<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AbsenceTool;
use App\Ai\Tools\EmployeeTool;
use App\Ai\Tools\PlanningTool;
use App\Ai\Tools\PdfTool;
use App\Ai\Tools\SalaryTool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantRH
{
    private EmployeeTool $employeeTool;
    private AbsenceTool  $absenceTool;
    private PlanningTool $planningTool;
    private PdfTool      $pdfTool;
    private SalaryTool   $salaryTool;

    public function __construct()
    {
        $this->employeeTool = new EmployeeTool();
        $this->absenceTool  = new AbsenceTool();
        $this->planningTool = new PlanningTool();
        $this->pdfTool      = new PdfTool();
        $this->salaryTool   = new SalaryTool();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Instructions système
    // ──────────────────────────────────────────────────────────────────────────

    private function instructions(): string
    {
        return <<<TXT
Tu es AssistantRH, un assistant RH intelligent pour HospitalRH.

RÈGLES STRICTES :
1. Réponds TOUJOURS en français.
2. Sois clair, professionnel et concis.
3. Pour TOUTE question sur employés, absences, planning ou salaires,
   utilise OBLIGATOIREMENT les tools disponibles — ne jamais inventer de données.
4. Si un tool retourne une liste vide, dis-le clairement.
5. Formate les réponses de manière lisible.
6. Si l'utilisateur demande un PDF, un rapport, un fichier ou un document à télécharger,
   utilise OBLIGATOIREMENT le tool generate_pdf.
7. Quand generate_pdf retourne PDF_DOWNLOAD::, **NE DIS RIEN d'autre**.
   Réponds UNIQUEMENT:
   - Info utile (ex: "PDF absences du {{today}} généré ({{count}} abs.).")
   - PDF_DOWNLOAD:: tag EXACT **sur ligne séparée**
   Exemple EXACT:
   ```
   PDF des employés actifs généré (8 employés).

   PDF_DOWNLOAD::http://...::fichier.pdf::Liste employés
   ```

TOOLS DISPONIBLES :
- employee_search : recherche d'employés par nom, matricule, département, poste, email, téléphone.
- absence_today   : liste les absences approuvées en cours aujourd'hui.
- planning_search : planning d'un employé pour une semaine donnée (nécessite matricule).
- salary_query    : recherche bulletins de salaire par nom/matricule/mois.
- generate_pdf    : PDF téléchargeable (absences, employees, planning, salaries).
TXT;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Lecture sécurisée de la clé API
    // ──────────────────────────────────────────────────────────────────────────

    private function getApiKey(): ?string
    {
        return config('ai.providers.openrouter.key')
            ?? config('services.openrouter.key')
            ?? env('OPENROUTER_API_KEY');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Définitions OpenAI-format des tools
    // ──────────────────────────────────────────────────────────────────────────

    private function buildToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'employee_search',
                    'description' => 'Recherche des employés actifs par nom, prénom, matricule, département, poste, email ou téléphone.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Terme de recherche.',
                            ],
                            'fields' => [
                                'type'        => 'array',
                                'description' => 'Champs à afficher (optionnel).',
                                'items'       => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'absence_today',
                    'description' => 'Liste toutes les absences approuvées en cours aujourd\'hui.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => new \stdClass(),
                        'required'   => [],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'planning_search',
                    'description' => 'Retourne le planning hebdomadaire d\'un employé identifié par son matricule.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'matricule' => [
                                'type'        => 'string',
                                'description' => 'Le matricule de l\'employé (ex: EMP0001).',
                            ],
                            'date' => [
                                'type'        => 'string',
                                'description' => 'Date de référence au format YYYY-MM-DD (optionnel).',
                            ],
                        ],
                        'required' => ['matricule'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'generate_pdf',
                    'description' => 'Génère un fichier PDF téléchargeable.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'type' => [
                                'type'        => 'string',
                                'enum'        => ['absences', 'employees', 'planning', 'salaries'],
                                'description' => 'Type de rapport.',
                            ],
                            'matricule' => ['type' => 'string'],
                            'month'     => ['type' => 'string'],
                            'date'      => ['type' => 'string'],
                        ],
                        'required' => ['type'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'salary_query',
                    'description' => 'Recherche bulletins de salaire par nom/matricule/mois.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'month' => ['type' => 'integer'],
                            'year'  => ['type' => 'integer'],
                            'pdf'   => ['type' => 'boolean'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Dispatcher des tools
    // ──────────────────────────────────────────────────────────────────────────

    private function executeTool(string $name, array $args): string
    {
        Log::info('[AssistantRH] Tool appelé', ['tool' => $name, 'args' => $args]);

        try {
            $result = match ($name) {
                'employee_search' => $this->employeeTool->execute($args),
                'absence_today'   => $this->absenceTool->execute($args),
                'planning_search' => $this->planningTool->execute($args),
                'salary_query'    => $this->salaryTool->execute($args),
                'generate_pdf'    => $this->pdfTool->execute($args),
                default           => "Tool inconnu : '{$name}'.",
            };

            return is_array($result)
                ? json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : (string) $result;

        } catch (\Throwable $e) {
            Log::error('[AssistantRH] Erreur tool', [
                'tool'  => $name,
                'error' => $e->getMessage(),
            ]);
            return "Erreur lors de l'exécution du tool '{$name}' : " . $e->getMessage();
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Extraction du tag PDF depuis l'historique
    // ──────────────────────────────────────────────────────────────────────────

    private function extractPdfTag(array $messages): ?string
    {
        foreach (array_reverse($messages) as $msg) {
            if (
                ($msg['role'] ?? '') === 'tool' &&
                ($msg['name'] ?? '') === 'generate_pdf'
            ) {
                $content = trim($msg['content'] ?? '');
                if (str_starts_with($content, 'PDF_DOWNLOAD::')) {
                    return $content;
                }
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Point d'entrée principal — retourne un tableau simple ['success', 'text']
    // Plus de dépendance à Laravel\Ai\Responses\AgentResponse
    // ──────────────────────────────────────────────────────────────────────────

    public function prompt(string $userMessage, string $model = 'openai/gpt-4o-mini'): array
    {
        $key = $this->getApiKey();

        if (empty($key)) {
            Log::error('[AssistantRH] Clé API OpenRouter manquante');
            return [
                'success' => false,
                'text'    => 'Clé API OpenRouter manquante. Vérifiez OPENROUTER_API_KEY dans votre .env.',
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => $this->instructions()],
            ['role' => 'user',   'content' => $userMessage],
        ];

        $maxIterations = 6;

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {

            Log::debug('[AssistantRH] Itération', ['i' => $iteration]);

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                    'HTTP-Referer'  => url('/'),
                    'X-Title'       => config('app.name', 'HospitalRH'),
                ])
                ->withoutVerifying()
                ->timeout(45)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => $model,
                    'messages'    => $messages,
                    'tools'       => $this->buildToolDefinitions(),
                    'tool_choice' => 'auto',
                ]);

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('[AssistantRH] Erreur réseau', ['error' => $e->getMessage()]);
                return [
                    'success' => false,
                    'text'    => 'Impossible de joindre OpenRouter. Vérifiez votre connexion internet.',
                ];
            }

            if ($response->failed()) {
                $status = $response->status();
                Log::error('[AssistantRH] API failed', ['status' => $status, 'body' => $response->body()]);

                $errorMsg = match (true) {
                    $status === 401 => 'Clé API invalide ou expirée (401).',
                    $status === 402 => 'Quota OpenRouter épuisé (402).',
                    $status === 429 => 'Trop de requêtes (429). Réessayez dans quelques secondes.',
                    $status >= 500  => 'Erreur serveur OpenRouter (' . $status . ').',
                    default         => 'Erreur API OpenRouter (' . $status . ').',
                };

                return ['success' => false, 'text' => $errorMsg];
            }

            $data = $response->json();

            if (!is_array($data) || !isset($data['choices'])) {
                return ['success' => false, 'text' => 'Réponse invalide reçue depuis OpenRouter.'];
            }

            $choice       = $data['choices'][0]     ?? null;
            $assistantMsg = $choice['message']       ?? null;
            $finishReason = $choice['finish_reason'] ?? 'stop';

            if (!$assistantMsg) {
                return ['success' => false, 'text' => 'Réponse API invalide : aucun message reçu.'];
            }

            // ── Tool calls ────────────────────────────────────────────────────
            if ($finishReason === 'tool_calls' || !empty($assistantMsg['tool_calls'])) {

                $messages[] = $assistantMsg;

                foreach ($assistantMsg['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name']      ?? '';
                    $toolArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];
                    $callId   = $toolCall['id']                    ?? uniqid('call_');

                    $toolResult = $this->executeTool($toolName, $toolArgs);

                    Log::info('[AssistantRH] Résultat tool', [
                        'tool'    => $toolName,
                        'preview' => mb_substr($toolResult, 0, 300),
                    ]);

                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $callId,
                        'name'         => $toolName,
                        'content'      => $toolResult,
                    ];
                }

                continue;
            }

            // ── Réponse texte finale ──────────────────────────────────────────
            $text = $assistantMsg['content'] ?? null;

            if ($text !== null) {
                $finalText = trim($text);

                if (!str_contains($finalText, 'PDF_DOWNLOAD::')) {
                    $pdfTag = $this->extractPdfTag($messages);
                    if ($pdfTag !== null) {
                        $finalText .= "\n" . $pdfTag;
                    }
                }

                return ['success' => true, 'text' => $finalText];
            }

            return ['success' => false, 'text' => "Réponse inattendue (finish_reason: {$finishReason})."];
        }

        return ['success' => false, 'text' => 'Nombre maximum d\'itérations atteint.'];
    }
}

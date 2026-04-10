<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AbsenceTool;
use App\Ai\Tools\EmployeeTool;
use App\Ai\Tools\PlanningTool;
use App\Ai\Tools\PdfTool;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Enums\Lab;
use Stringable;

class AssistantRH implements Agent, Conversational, HasTools
{
    use Promptable;

    private EmployeeTool $employeeTool;
    private AbsenceTool  $absenceTool;
    private PlanningTool $planningTool;
private PdfTool      $pdfTool;


    public function __construct()
    {
        $this->employeeTool = new EmployeeTool();
        $this->absenceTool  = new AbsenceTool();
        $this->planningTool = new PlanningTool();
        $this->pdfTool      = new PdfTool();

    }

    // ──────────────────────────────────────────────────────────────────────────
    // Instructions système
    // ──────────────────────────────────────────────────────────────────────────

    public function instructions(): Stringable|string
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
   NI "lien", NI "télécharger", NI texte explicatif. 
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

- generate_pdf    : PDF téléchargeable (absences, employees, planning, salaries).
TXT;
    }

    public function messages(): iterable
    {
        return [];
    }

    public function tools(): iterable
    {
        return [
            $this->employeeTool,
            $this->absenceTool,
            $this->planningTool,
            $this->pdfTool,
        ];

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
                                'description' => 'Terme de recherche : nom, prénom, matricule, département, poste, email ou téléphone.',
                            ],
                            'fields' => [
                                'type'        => 'array',
                                'description' => 'Champs à afficher (optionnel). Valeurs : matricule, nom, email, phone, position, department, base_salary, status.',
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
                                'description' => 'Date de référence au format YYYY-MM-DD (optionnel, défaut = aujourd\'hui).',
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
                    'description' => 'Génère un fichier PDF téléchargeable. À utiliser quand l\'utilisateur demande un PDF, rapport, fichier ou document à télécharger.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'type' => [
                                'type'        => 'string',
                                'enum'        => ['absences', 'employees', 'planning', 'salaries'],
                                'description' => 'Type de rapport : "absences" (absences du jour), "employees" (liste employés), "planning" (planning employé), "salaries" (bulletins mois/année).',
                            ],
                            'matricule' => [
                                'type'        => 'string',
                                'description' => 'Matricule employé (pour planning/salaries).',
                            ],
                            'month' => [
                                'type'        => 'string',
                                'description' => 'Mois pour salaries (1-12).',
                            ],
                            'date' => [
                                'type'        => 'string',
                                'description' => 'Date YYYY-MM-DD (planning).',
                            ],
                        ],
                        'required' => ['type'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'salary_query',
                    'description' => 'Recherche bulletins de salaire par nom/matricule/mois. Fournit totaux CNSS/paiements + PDF.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => [
                                'type'        => 'string',
                                'description' => 'Recherche: nom, matricule, "masse salariale", "CNSS", "payés", "cash" (ex: "EMP0001", "masse", "CNSS").',
                            ],
                            'month' => [
                                'type'        => 'integer',
                                'description' => 'Mois 1-12 (optionnel).',
                            ],
                            'year' => [
                                'type'        => 'integer',
                                'description' => 'Année (défaut actuel).',
                            ],
                            'pdf' => [
                                'type' => 'boolean',
                                'description' => 'Générer PDF (true).',
                            ],
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
            // CORRECTION : suppression du strtoupper() sur generate_pdf
            // qui cassait l'URL en la mettant en majuscules
            $result = match ($name) {
                'employee_search' => $this->employeeTool->execute($args),
                'absence_today'   => $this->absenceTool->execute($args),
                'planning_search' => $this->planningTool->execute($args),
'generate_pdf'    => $this->pdfTool->execute($args),

                default           => "Tool inconnu : '{$name}'.",
            };

            return is_array($result)
                ? json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : (string) $result;

        } catch (\Throwable $e) {
            Log::error('[AssistantRH] Erreur tool', [
                'tool'  => $name,
                'args'  => $args,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return "Erreur lors de l'exécution du tool '{$name}' : " . $e->getMessage();
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Extraction du tag PDF depuis l'historique des messages tool
    // Garantit que le tag brut est toujours présent dans la réponse finale,
    // même si le LLM l'a reformaté ou omis.
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
    // Point d'entrée principal avec boucle tool-calling
    // ──────────────────────────────────────────────────────────────────────────

    public function prompt(
        string $prompt,
        array $attachments = [],
        Lab|array|string|null $provider = null,
        ?string $model = null
    ): AgentResponse {

        $key = config('ai.providers.openrouter.key');

        if (!$key) {
            return $this->errorResponse('Clé API OpenRouter manquante. Vérifiez OPENROUTER_API_KEY dans votre .env.');
        }

        $model = $model ?? 'openai/gpt-4o-mini';

        $messages = [
            ['role' => 'system', 'content' => (string) $this->instructions()],
            ['role' => 'user',   'content' => $prompt],
        ];

        $toolDefinitions   = $this->buildToolDefinitions();
        $totalInputTokens  = 0;
        $totalOutputTokens = 0;
        $maxIterations     = 6;

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {

            Log::debug('[AssistantRH] Itération', ['i' => $iteration, 'messages' => count($messages)]);

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
                'tools'       => $toolDefinitions,
                'tool_choice' => 'auto',
            ]);

            if ($response->failed()) {
                Log::error('[AssistantRH] API failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->errorResponse(
                    'Erreur API OpenRouter (' . $response->status() . '). Vérifiez votre clé et votre quota.'
                );
            }

            $data = $response->json();

            $totalInputTokens  += $data['usage']['prompt_tokens']    ?? 0;
            $totalOutputTokens += $data['usage']['completion_tokens'] ?? 0;

            $choice       = $data['choices'][0]      ?? null;
            $assistantMsg = $choice['message']        ?? null;
            $finishReason = $choice['finish_reason']  ?? 'stop';

            if (!$assistantMsg) {
                return $this->errorResponse('Réponse API invalide : aucun message reçu.');
            }

            // ── Le modèle veut appeler des tools ──────────────────────────────
            if ($finishReason === 'tool_calls' || !empty($assistantMsg['tool_calls'])) {

                $messages[] = $assistantMsg;

                foreach ($assistantMsg['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name']      ?? '';
                    $toolArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];
                    $callId   = $toolCall['id']                    ?? uniqid('call_');

                    $toolResult = $this->executeTool($toolName, $toolArgs);

                    Log::info('[AssistantRH] Résultat tool', [
                        'tool'    => $toolName,
                        'call_id' => $callId,
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

                // CORRECTION : si le LLM n'a pas reproduit le tag PDF tel quel
                // (reformaté en Markdown ou omis), on l'injecte depuis
                // les résultats du tool pour garantir que le frontend peut
                // l'intercepter et afficher le bouton de téléchargement.
                if (!str_contains($finalText, 'PDF_DOWNLOAD::')) {
                    $pdfTag = $this->extractPdfTag($messages);
                    if ($pdfTag !== null) {
                        $finalText .= "\n" . $pdfTag;
                    }
                }

                return new AgentResponse(
                    uniqid('ai_'),
                    $finalText,
                    new Usage($totalInputTokens, $totalOutputTokens, $totalInputTokens + $totalOutputTokens),
                    new Meta(provider: 'openrouter', model: $model)
                );
            }

            return $this->errorResponse("Réponse inattendue (finish_reason: {$finishReason}).");
        }

        return $this->errorResponse('Nombre maximum d\'itérations tool-calling atteint.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────────────────────────────────

    private function errorResponse(string $message): AgentResponse
    {
        return new AgentResponse(
            uniqid('ai_'),
            $message,
            new Usage(0, 0, 0),
            new Meta(provider: 'openrouter', model: 'none')
        );
    }
}
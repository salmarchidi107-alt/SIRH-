<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AbsenceTool;
use App\Ai\Tools\EmployeeTool;
use App\Ai\Tools\PlanningTool;
use App\Ai\Tools\PdfTool;
use App\Ai\Tools\SalaryTool;
use App\Ai\Tools\PointageTool;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantRH
{
    private EmployeeTool $employeeTool;
    private AbsenceTool  $absenceTool;
    private PlanningTool $planningTool;
    private PdfTool      $pdfTool;
    private SalaryTool   $salaryTool;
    private PointageTool $pointageTool;

    public function __construct()
    {
        $this->employeeTool = new EmployeeTool();
        $this->absenceTool  = new AbsenceTool();
        $this->planningTool = new PlanningTool();
        $this->pdfTool      = new PdfTool();
        $this->salaryTool   = new SalaryTool();
        $this->pointageTool = new PointageTool();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Instructions système
    // ──────────────────────────────────────────────────────────────────────────

    private function instructions(): string
    {
        $today = now()->format('d/m/Y');

        return <<<TXT
Tu es AssistantRH, un assistant RH intelligent pour HospitalRH. Nous sommes le {$today}.

════════════════════════════════════════
RÈGLES ABSOLUES
════════════════════════════════════════
1. Réponds TOUJOURS en français.
2. Sois clair, professionnel et concis.
3. N'invente JAMAIS de données — utilise OBLIGATOIREMENT les tools pour toute
   question sur employés, absences, planning, salaires ou pointages.
4. Si un tool retourne une liste vide, annonce-le clairement.
5. Formate les réponses de façon lisible (tableaux Markdown si disponibles).
6. Pour tout PDF / rapport / document à télécharger → tool generate_pdf.

════════════════════════════════════════
RÈGLE PDF (CRITIQUE)
════════════════════════════════════════
Quand generate_pdf retourne un tag PDF_DOWNLOAD::, tu dois :
  • Écrire UNE courte phrase de confirmation (ex: "PDF généré (8 employés).").
  • Reproduire le tag EXACTEMENT tel quel sur une ligne séparée, sans modification.
  • Ne rien ajouter d'autre (pas de "cliquez ici", pas de Markdown autour du tag).

Exemple de réponse correcte :
---
PDF des absences du 14/01/2025 généré (3 absence(s)).

PDF_DOWNLOAD::http://example.com/pdf/fichier.pdf::fichier.pdf::Absences du 14/01/2025
---

════════════════════════════════════════
TOOLS DISPONIBLES ET LEUR USAGE
════════════════════════════════════════

employee_search
  → Recherche un employé par nom, prénom, matricule, département, poste, email, téléphone.

absence_today
  → Absences approuvées EN COURS aujourd'hui.

planning_search
  → Planning hebdomadaire d'un employé (matricule obligatoire).

salary_query
  → Bulletins de salaire, masse salariale, CNSS, statistiques de paiement, PDF salaires.

pointage_search
  → Pointages (entrées/sorties) d'un employé ou de tous les employés.

generate_pdf
  → Génère un fichier PDF téléchargeable.
  → Types : "absences" | "employees" | "planning" | "salaries"
TXT;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Définitions des tools OpenAI-format
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
                            'query'  => ['type' => 'string', 'description' => 'Terme de recherche.'],
                            'fields' => ['type' => 'array',  'description' => 'Champs à afficher (optionnel).', 'items' => ['type' => 'string']],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'absence_today',
                    'description' => "Liste toutes les absences approuvées en cours aujourd'hui.",
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass(), 'required' => []],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'planning_search',
                    'description' => "Retourne le planning hebdomadaire d'un employé identifié par son matricule.",
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'matricule' => ['type' => 'string', 'description' => "Matricule de l'employé (ex: EMP0001)."],
                            'date'      => ['type' => 'string', 'description' => 'Date de référence YYYY-MM-DD (optionnel).'],
                        ],
                        'required' => ['matricule'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'salary_query',
                    'description' => 'Interroge les bulletins de salaire, masse salariale, CNSS, statistiques ou génère un PDF.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query' => ['type' => 'string',  'description' => 'Terme de recherche.'],
                            'month' => ['type' => 'integer', 'description' => 'Mois (1-12, optionnel).'],
                            'year'  => ['type' => 'integer', 'description' => 'Année YYYY (optionnel).'],
                            'pdf'   => ['type' => 'boolean', 'description' => 'true pour générer un PDF.'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'pointage_search',
                    'description' => "Recherche les pointages d'un ou plusieurs employés sur une date ou période.",
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'matricule'  => ['type' => 'string', 'description' => 'Matricule (optionnel).'],
                            'date'       => ['type' => 'string', 'description' => 'Date exacte YYYY-MM-DD (optionnel).'],
                            'date_debut' => ['type' => 'string', 'description' => 'Début de plage YYYY-MM-DD (optionnel).'],
                            'date_fin'   => ['type' => 'string', 'description' => 'Fin de plage YYYY-MM-DD (optionnel).'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'generate_pdf',
                    'description' => "Génère un fichier PDF téléchargeable.",
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'type'      => ['type' => 'string', 'enum' => ['absences', 'employees', 'planning', 'salaries']],
                            'matricule' => ['type' => 'string',  'description' => 'Matricule employé (optionnel).'],
                            'month'     => ['type' => 'integer', 'description' => 'Mois (1-12, optionnel).'],
                            'year'      => ['type' => 'integer', 'description' => 'Année (optionnel).'],
                            'date'      => ['type' => 'string',  'description' => 'Date YYYY-MM-DD (optionnel).'],
                        ],
                        'required' => ['type'],
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
                'generate_pdf'    => $this->pdfTool->execute($args),
                'salary_query'    => $this->salaryTool->execute($args),
                'pointage_search' => $this->pointageTool->execute($args),
                default           => "Tool inconnu : '{$name}'.",
            };

            return is_array($result)
                ? json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : (string) $result;

        } catch (\Throwable $e) {
            Log::error('[AssistantRH] Erreur tool', [
                'tool'  => $name,
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return "Erreur lors de l'exécution du tool '{$name}' : " . $e->getMessage();
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Extraction du tag PDF depuis les messages tool
    // ──────────────────────────────────────────────────────────────────────────

    private function extractPdfTag(array $messages): ?string
    {
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') !== 'tool') {
                continue;
            }
            $content = trim($msg['content'] ?? '');
            if (preg_match('/(PDF_DOWNLOAD::[^\s]+::[^\s]+::[^\n]+)/', $content, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Point d'entrée principal
    // ──────────────────────────────────────────────────────────────────────────

    public function prompt(string $prompt): array
    {
        $key = config('ai.providers.openrouter.key')
            ?? config('services.openrouter.key')
            ?? env('OPENROUTER_API_KEY');

        if (!$key) {
            Log::error('[AssistantRH] Clé API OpenRouter manquante');
            return [
                'text'  => '⚠️ Clé API OpenRouter manquante. Vérifiez OPENROUTER_API_KEY dans votre .env.',
                'error' => true,
            ];
        }

        $model = 'openai/gpt-4o-mini';

        $messages = [
            ['role' => 'system', 'content' => $this->instructions()],
            ['role' => 'user',   'content' => $prompt],
        ];

        $toolDefinitions   = $this->buildToolDefinitions();
        $totalInputTokens  = 0;
        $totalOutputTokens = 0;
        $maxIterations     = 8;

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {

            Log::debug('[AssistantRH] Itération', [
                'i'        => $iteration,
                'messages' => count($messages),
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => url('/'),
                'X-Title'       => config('app.name', 'HospitalRH'),
            ])
            ->withoutVerifying()
            ->timeout(60)
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
                return [
                    'text'  => 'Erreur API OpenRouter (' . $response->status() . '). Vérifiez votre clé et votre quota.',
                    'error' => true,
                ];
            }

            $data = $response->json();

            $totalInputTokens  += $data['usage']['prompt_tokens']    ?? 0;
            $totalOutputTokens += $data['usage']['completion_tokens'] ?? 0;

            $choice       = $data['choices'][0]     ?? null;
            $assistantMsg = $choice['message']       ?? null;
            $finishReason = $choice['finish_reason'] ?? 'stop';

            if (!$assistantMsg) {
                return ['text' => 'Réponse API invalide : aucun message reçu.', 'error' => true];
            }

            // ── Le modèle veut appeler des tools ──────────────────────────
            if ($finishReason === 'tool_calls' || !empty($assistantMsg['tool_calls'])) {

                $messages[] = $assistantMsg;

                foreach ($assistantMsg['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name']             ?? '';
                    $toolArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];
                    $callId   = $toolCall['id']                           ?? uniqid('call_');

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

            // ── Réponse texte finale ──────────────────────────────────────
            $text = $assistantMsg['content'] ?? null;

            if ($text !== null) {
                $finalText = trim($text);

                // Injecter le tag PDF si le LLM l'a omis
                if (!str_contains($finalText, 'PDF_DOWNLOAD::')) {
                    $pdfTag = $this->extractPdfTag($messages);
                    if ($pdfTag !== null) {
                        $finalText .= "\n" . $pdfTag;
                    }
                }

                return [
                    'text'          => $finalText,
                    'input_tokens'  => $totalInputTokens,
                    'output_tokens' => $totalOutputTokens,
                    'error'         => false,
                ];
            }

            return ['text' => "Réponse inattendue (finish_reason: {$finishReason}).", 'error' => true];
        }

        return ['text' => "Nombre maximum d'itérations tool-calling atteint.", 'error' => true];
    }
}
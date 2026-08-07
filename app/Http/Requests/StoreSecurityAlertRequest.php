<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le payload de fuite envoyé par le frontend.
 *
 * Important : le frontend ne fournit QUE ce qu'il a réellement observé
 * (tenants, module, ressource, IDs, URL, route). Tout ce qui doit être
 * fiable et non falsifiable (utilisateur, IP, user agent) est résolu
 * côté serveur dans le contrôleur, jamais lu depuis ce payload.
 */
class StoreSecurityAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'expected_tenant_id' => ['nullable', 'integer'],
            'received_tenant_id' => ['nullable', 'integer'],
            'module' => ['required', 'string', 'max:150'],
            'model_name' => ['required', 'string', 'max:150'],
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['required'],
            'url' => ['required', 'string', 'max:2048'],
            'route_name' => ['nullable', 'string', 'max:150'],
        ];
    }
}

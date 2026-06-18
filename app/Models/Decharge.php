<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'affectation_id',
        'type',
        'statut',
        'date_generation',
        'date_signature',
        'chemin_pdf',
        'signature_salarie',
        'generee_par',
    ];

    protected $casts = [
        'date_generation' => 'date',
        'date_signature'  => 'date',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class);
    }

    public function genereePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generee_par');
    }

    // ─── Génération de numéro auto ───────────────────────────────────────────

    public static function genererNumero(): string
    {
        $annee = now()->year;
        $last = self::whereYear('created_at', $annee)
            ->orderByDesc('id')
            ->value('numero');
        $num = $last ? ((int) substr($last, -5)) + 1 : 1;
        return 'DCH-' . $annee . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function isSignee(): bool
    {
        return $this->statut === 'signee';
    }

    public function isEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }
}

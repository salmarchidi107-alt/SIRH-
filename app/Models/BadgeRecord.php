<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'latitude',
        'longitude',
        'accuracy',
        'location_address',
        'geolocation_denied',
    ];

    protected $casts = [
        'latitude'           => 'float',
        'longitude'          => 'float',
        'accuracy'           => 'float',
        'geolocation_denied' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Indique si le pointage a des coordonnées GPS valides. */
    public function hasLocation(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null
            && ! $this->geolocation_denied;
    }

    /** Retourne la précision arrondie en mètres, ou null. */
    public function accuracyMeters(): ?int
    {
        return $this->accuracy !== null ? (int) round($this->accuracy) : null;
    }

    /** Indique si la précision est considérée bonne (≤ 30 m). */
    public function isHighAccuracy(): bool
    {
        return $this->accuracy !== null && $this->accuracy <= 30;
    }

    /** Retourne les coords formatées pour Google Maps. */
    public function googleMapsUrl(): ?string
    {
        if (! $this->hasLocation()) return null;
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /** Scopes ─────────────────────────────────────────────────────────── */

    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')->where('geolocation_denied', false);
    }

    public function scopeWithoutLocation($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('latitude')->orWhere('geolocation_denied', true);
        });
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

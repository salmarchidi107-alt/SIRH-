<?php
// ============================================================
//  app/Models/BadgeRecord.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BadgeRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'shift_type',          // ← AJOUTÉ : 'normal' | 'garde'

        // Géolocalisation
        'latitude',
        'longitude',
        'accuracy',
        'location_address',
        'geolocation_denied',

        // Photo faciale
        'face_photo_path',
        'face_photo_disk',
        'face_photo_base64',
        'face_photo_size',
        'face_photo_mime',
    ];

    protected $hidden = [
        'face_photo_base64', // trop lourd pour les sérialisations JSON
    ];

    protected $casts = [
        'latitude'           => 'float',
        'longitude'          => 'float',
        'accuracy'           => 'float',
        'geolocation_denied' => 'boolean',
        'face_photo_size'    => 'integer',
        'shift_type'         => 'string',  // ← AJOUTÉ
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Accessors shift ───────────────────────────────────────────────────

    /** true si c'est une garde de nuit. */
    public function isGarde(): bool
    {
        return $this->shift_type === 'garde';
    }

    /** Libellé lisible du type de shift. */
    public function getShiftTypeLabelAttribute(): string
    {
        return $this->shift_type === 'garde' ? 'Garde' : 'Shift normal';
    }

    // ── Accessors photo ───────────────────────────────────────────────────

    /** URL publique de la photo (via fichier disque). */
    public function getFacePhotoUrlAttribute(): ?string
    {
        if (! $this->face_photo_path) return null;

        return Storage::disk($this->face_photo_disk ?? 'public')
                      ->url($this->face_photo_path);
    }

    /** Taille lisible : "142 Ko" ou "1.4 Mo". */
    public function getFacePhotoSizeHumanAttribute(): string
    {
        if (! $this->face_photo_size) return '—';
        $kb = round($this->face_photo_size / 1024, 1);
        return $kb >= 1024
            ? round($kb / 1024, 2) . ' Mo'
            : $kb . ' Ko';
    }

    /** true si une photo a été enregistrée. */
    public function hasFacePhoto(): bool
    {
        return ! empty($this->face_photo_path);
    }

    // ── Helpers géo ───────────────────────────────────────────────────────

    public function hasLocation(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null
            && ! $this->geolocation_denied;
    }

    public function accuracyMeters(): ?int
    {
        return $this->accuracy !== null ? (int) round($this->accuracy) : null;
    }

    public function isHighAccuracy(): bool
    {
        return $this->accuracy !== null && $this->accuracy <= 30;
    }

    public function googleMapsUrl(): ?string
    {
        if (! $this->hasLocation()) return null;
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    // ── Scopes ────────────────────────────────────────────────────────────

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

    public function scopeWithPhoto($query)
    {
        return $query->whereNotNull('face_photo_path');
    }

    /** Scope : filtrer par type de shift. */
    public function scopeOfShift($query, string $shiftType)
    {
        return $query->where('shift_type', $shiftType);
    }

    /** Scope : uniquement les gardes. */
    public function scopeGarde($query)
    {
        return $query->where('shift_type', 'garde');
    }

    /** Scope : uniquement les shifts normaux. */
    public function scopeNormal($query)
    {
        return $query->where('shift_type', 'normal');
    }
}

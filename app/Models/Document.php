<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'fichier_path',
        'fichier_nom_original',
        'taille',
        'description',
        'employe_id',
        'modele_id',
        'created_by',
        'date_document',
    ];

    protected $casts = [
        'date_document' => 'date',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function employe()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modele()
    {
        return $this->belongsTo(DocumentModele::class, 'modele_id');
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    public function getTailleFormatteeAttribute(): string
    {
        if (!$this->taille) return '—';
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = floor(log($this->taille, 1024));
        return round($this->taille / pow(1024, $i), 1) . ' ' . $units[$i];
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->fichier_path);
    }

    public function getIconeTypeAttribute(): string
    {
        if (!$this->fichier_nom_original) return 'fa-file';
        $ext = strtolower(pathinfo($this->fichier_nom_original, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'             => 'fa-file-pdf',
            'doc', 'docx'     => 'fa-file-word',
            'xls', 'xlsx'     => 'fa-file-excel',
            'jpg','jpeg','png','gif' => 'fa-file-image',
            default           => 'fa-file',
        };
    }

    public function getCouleurTypeAttribute(): string
    {
        if (!$this->fichier_nom_original) return '#7f8c8d';
        $ext = strtolower(pathinfo($this->fichier_nom_original, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'             => '#e74c3c',
            'doc', 'docx'     => '#2980b9',
            'xls', 'xlsx'     => '#27ae60',
            'jpg','jpeg','png','gif' => '#8e44ad',
            default           => '#7f8c8d',
        };
    }
}

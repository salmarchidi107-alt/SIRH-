<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PayrollSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'category', 'type'];

    protected $casts = ['value' => 'decimal:4'];

    /**
     * Récupérer un taux (mis en cache 1h)
     */
    public static function get(string $key, float $default = 0): float
    {
        return Cache::remember("payroll_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? (float) $setting->value : $default;
        });
    }

    /**
     * Mettre à jour et invalider le cache
     */
    public static function set(string $key, float $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("payroll_setting_{$key}");
    }

    /**
     * Tous les paramètres groupés par catégorie
     */
    public static function allGrouped(): array
    {
        return static::all()->groupBy('category')->toArray();
    }
}

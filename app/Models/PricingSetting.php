<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PricingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'description',
        'group',
        'is_editable',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
    ];

    // ========================================================================
    // MÉTODOS ESTÁTICOS
    // ========================================================================

    /**
     * Obtener valor de un setting (con caché)
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("pricing_setting_{$key}", 3600, function() use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Establecer valor de un setting
     */
    public static function set($key, $value)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("pricing_setting_{$key}");
        
        return $setting;
    }

    /**
     * Obtener todos los settings de un grupo
     */
    public static function getGroup($group)
    {
        return self::where('group', $group)
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
    }

    /**
     * Castear valor según tipo
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'decimal':
            case 'float':
                return (float) $value;
            case 'integer':
            case 'int':
                return (int) $value;
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Limpiar toda la caché de settings
     */
    public static function clearCache()
    {
        Cache::flush(); // O más específico si prefieres
    }
}
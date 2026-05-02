<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static $cache = [];

    protected static array $encryptedKeys = [
        'odoo_password',
    ];

    public static function get(string $key, $default = null): ?string
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        $value = $setting ? $setting->value : $default;

        if ($value !== null && in_array($key, static::$encryptedKeys, true)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                $value = null;
            }
        }

        static::$cache[$key] = $value;

        return $value;
    }

    public static function set(string $key, $value): void
    {
        if (in_array($key, static::$encryptedKeys, true) && $value !== null) {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
        unset(static::$cache[$key]);
    }

    public static function getOdooConfig(): array
    {
        return [
            'url' => static::get('odoo_url', ''),
            'db' => static::get('odoo_db', ''),
            'user' => static::get('odoo_user', ''),
            'password' => static::get('odoo_password', ''),
        ];
    }

    public static function getValue(string $key, $default = null): ?string
    {
        return static::get($key, $default);
    }

    public static function setValue(string $key, $value): void
    {
        static::set($key, $value);
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }
}

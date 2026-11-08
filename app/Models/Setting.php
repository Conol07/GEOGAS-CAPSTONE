<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/** Tiny key/value store for LGU-controlled switches (e.g. public heatmap). */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function bool(string $key, bool $default = true): bool
    {
        // Safe before the migration has been run: fall back to the default.
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }
            $row = static::find($key);
        } catch (\Throwable $e) {
            return $default;
        }

        return $row ? $row->value === '1' : $default;
    }

    public static function setBool(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
    }
}

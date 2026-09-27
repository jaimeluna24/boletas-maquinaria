<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Operador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operadores';

    protected $fillable = [
        'codigo',
        'nombre_operador',
        'telefono_operador',
        'estado_operador',
    ];

    protected $casts = [
        'estado_operador' => 'boolean',
    ];

    public function distribucionMaquinaria(): HasMany
    {
        return $this->hasMany(DistribucionMaquinaria::class, 'operador_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($operador) {
            if (empty($operador->codigo_operador)) {
                $operador->codigo_operador = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo(): string
    {
        return DB::transaction(function () {
            $ultimo = self::withTrashed()
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $siguienteNumero = 1;

            if ($ultimo && preg_match('/(\d+)$/', $ultimo->codigo_operador, $matches)) {
                $siguienteNumero = (int) $matches[1] + 1;
            }

            return 'OPR-'.str_pad((string) $siguienteNumero, 4, '0', STR_PAD_LEFT);
        });
    }
}

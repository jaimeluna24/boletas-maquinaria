<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Solicitud extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes';

    protected $fillable = [
        'codigo_solicitud',
        'descripcion',
        'sector',
        'fecha_solicitud',
        'tipo_equipo',
        'estado',
        'actividad_id',
        'solicitante',
        'observacion',
        'contestada_por',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function usuarioSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante');
    }

    public function distribucionMaquinaria(): HasMany
    {
        return $this->hasMany(DistribucionMaquinaria::class, 'solicitud_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($solicitud) {
            if (empty($solicitud->codigo_solicitud)) {
                $solicitud->codigo_solicitud = self::generarCodigo();
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

            if ($ultimo && preg_match('/(\d+)$/', $ultimo->codigo_solicitud, $matches)) {
                $siguienteNumero = (int) $matches[1] + 1;
            }

            return 'SOL-'.str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);
        });
    }
}

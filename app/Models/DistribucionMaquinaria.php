<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistribucionMaquinaria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'distribucion_maquinarias';

    protected $fillable = [
        'solicitud_id',
        'operador_id',
        'equipo_id',
        'implemento_id',
        'hora_inicio',
        'hora_fin',
        'fecha',
        'horometro_inicial',
        'horometro_final',
        'observacion',
        'estado',
        'lugar',
        'creada_por',
        'descripcion',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function implemento(): BelongsTo
    {
        return $this->belongsTo(Implemento::class, 'implemento_id');
    }

    public function usuario_creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por');
    }

    public function tiempoPerdidoDetalle(): HasMany
    {
        return $this->hasMany(TiempoPerdidoDetalle::class, 'distribucion_maquinaria_id');
    }
}

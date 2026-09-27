<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiempoPerdidoDetalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tiempo_perdido_detalles';

    protected $fillable = [
        // 'tiempo_perdido_id',
        'nombre_tiempo_perdido',
        'distribucion_maquinaria_id',
        'hora_inicio',
        'hora_fin',
        'fecha',
        'observacion',
    ];

    // public function tiempoPerdido(): BelongsTo
    // {
    //     return $this->belongsTo(TiempoPerdido::class, 'tiempo_perdido_id');
    // }

    public function distribucionMaquinaria(): BelongsTo
    {
        return $this->belongsTo(DistribucionMaquinaria::class, 'distribucion_maquinaria_id');
    }
}

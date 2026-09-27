<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiempoPerdido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tiempo_perdidos';

    protected $fillable = [
        'codigo_tiempo_perdido',
        'nombre_tiempo_perdido'
    ];

    public function tiempoPerdidoDetalles(): HasMany
    {
        return $this->hasMany(TiempoPerdidoDetalle::class, 'tiempo_perdido_id');
    }
}

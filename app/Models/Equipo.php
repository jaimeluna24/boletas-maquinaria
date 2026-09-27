<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipos';

    protected $fillable = [
        'inventario',
        'nombre_equipo',
        'tipo_equipo_id',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tipoEquipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }
}

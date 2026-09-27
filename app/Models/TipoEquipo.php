<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_equipos';

    protected $fillable = [
        'nombre_tipo_equipo',
    ];

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class, 'tipo_equipo_id');
    }
}

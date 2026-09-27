<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoImplemento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_implementos';

    protected $fillable = [
        'nombre_tipo_implemento'
    ];

    public function implementos(): HasMany
    {
        return $this->hasMany(Implemento::class, 'tipo_implemento_id');
    }
}

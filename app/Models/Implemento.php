<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Implemento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'implementos';

    protected $fillable = [
        'inventario',
        'nombre_implemento',
        'tipo_implemento_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tipoImplemento(): BelongsTo
    {
        return $this->belongsTo(TipoImplemento::class, 'tipo_implemento_id');
    }
}

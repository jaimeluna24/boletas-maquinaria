<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo_destinatario',
        'rol_destino',
        'notificable_type',
        'notificable_id',
        'url',
        'creado_por',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'notificacion_usuarios')
            ->withPivot('leido_at')
            ->withTimestamps();
    }

    public function notificable()
    {
        return $this->morphTo();
    }

    public function notificacionesUsuarios(): HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'notificacion_id');
    }
}

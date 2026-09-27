<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionUsuario extends Model
{
    protected $table = 'notificacion_usuarios';

    protected $fillable = [
        'notificacion_id',
        'user_id',
        'leido_at',
    ];

    public function notificacion(): BelongsTo
    {
        return $this->belongsTo(Notificacion::class, 'notificacion_id');
    }

    /**
     * Obtiene el usuario asociado a esta notificación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

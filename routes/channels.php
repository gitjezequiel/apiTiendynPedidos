<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Canal privado por usuario: solo el propio usuario puede suscribirse.
| Usado para notificar al dueño del restaurante cuando llega un pedido nuevo.
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\Partido;

class ComunidadService
{
    public function unirse($codigo, $user_id){

        $comunidad = Comunidad::where('codigo', $codigo)->firstOrFail();

        if ($comunidad->creador_id === $user_id){

            return ['error' => true, 'message' => 'No puedes unirte a tu propia comunidad', 'code' => 422];
        }

        if ($comunidad->users()->where('user_id', $user_id)->exists()) {

            return ['error' => true, 'message' => 'Ya eres miembro o tienes una solicitud pendiente', 'code' => 422];
        }

        $comunidad->users()->attach($user_id, ['estado_solicitud' => 'pendiente']);
        
        return ['error' => false, 'data' => $comunidad];


    }
}
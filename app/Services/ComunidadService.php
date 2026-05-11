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

    public function aceptarSolicitud($comunidad_id, $creador_id, $user_id){

        $comunidad = Comunidad::findOrFail($comunidad_id);

        if ($comunidad->creador_id !== $creador_id){

            return ['error' => true, 'message' => 'No eres el creador de esta comunidad', 'code' => 422];
        }

        $solicitud = $comunidad->users()->where('user_id', $user_id)
                        ->wherePivot('estado_solicitud', 'pendiente')
                        ->exists();

        if (!$solicitud){
            return ['error' => true, 'message' => 'No existe solicitud pendiente para este usuario', 'code' => 404];
        }

        $comunidad->users()->updateExistingPivot($user_id, ['estado_solicitud' => 'aceptado']);

        return ['error' => false, 'data' => $comunidad];

    }

    public function eliminarMiembro($comunidad_id, $creador_id, $user_id){

        $comunidad = Comunidad::findOrFail($comunidad_id);

        if ($comunidad->creador_id !== $creador_id) {
            return ['error' => true, 'message' => 'No eres el creador de esta comunidad', 'code' => 403];
        }

        $esmiembro = $comunidad->users()
                        ->where('user_id', $user_id)
                        ->wherePivot('estado_solicitud', 'aceptado')
                        ->exists();

        if (!$esmiembro) {
            return ['error' => true, 'message' => 'Este usuario no es miembro de la comunidad', 'code' => 404];
        }

        $comunidad->users()->detach($user_id);

        return ['error' => false, 'data' => null];
    }
}
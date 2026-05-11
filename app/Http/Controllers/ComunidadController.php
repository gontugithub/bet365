<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Prediccion;
use App\Services\ComunidadService;
use App\Traits\TraitApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComunidadController extends Controller
{
    use TraitApiResponse;

    public function store(Request $request){

        $request->validate([
            'nombre' => ['required', 'string'],
        ]);

        $comunidad = Comunidad::where('nombre', $request->nombre)
                            ->where('creador_id', $request->user()->id)
                            ->exists();
        if ($comunidad){
            
            return $this->errorResponse('Tienes una comunidad ya creada a ese nombre', 400);

        } else {

            do{
                $codigo = strtoupper(Str::random(6));

            } while (Comunidad::where('codigo', $codigo)->exists());

            $comunidad = Comunidad::create([
                'nombre' => $request->nombre,
                'codigo' => $codigo,
                'creador_id' => $request->user()->id
            ]);

            return $this->successResponse($comunidad, "Comunidad creada con exito", 201);

        }
    }

    public function solicitar(Request $request){

        $request->validate([
                'codigo' => ['required', 'string', 'size:6'],
            ]);

        $service = new ComunidadService;

        $response = $service->unirse($request->codigo, $request->user()->id);
         
        if ($response['error']){
            return $this->errorResponse($response['message'], $response['code']);

        } else{
            return $this->successResponse($response['data'], 'Solicitud enviada con exito', 201);
        }
    }

    public function aceptar(Request $request, $comunidad_id, $user_id){

        $service = new ComunidadService;

        $response = $service->aceptarSolicitud($comunidad_id, $request->user()->id, $user_id);

        if ($response['error']){
            return $this->errorResponse($response['message'], $response['code']);

        } else{
            return $this->successResponse($response['data'], 'Usuario aceptado', 201);
        }


    }

    public function eliminar(Request $request, $comunidad_id, $user_id){

        $service = new ComunidadService;

        $response = $service->eliminarMiembro($comunidad_id, $request->user()->id, $user_id);

        if ($response['error']){
            return $this->errorResponse($response['message'], $response['code']);

        } else{
            return $this->successResponse($response['data'], 'Usuario eliminado', 200);
        }


    }

    public function show(Request $request, $comunidad_id){

        $comunidad = Comunidad::with('users')->findOrFail($comunidad_id);

        $esMiembro = $comunidad->users()
                ->where('user_id', $request->user()->id)
                ->wherePivot('estado_solicitud', 'aceptado')
                ->exists();

        $esCreador = $comunidad->creador_id === $request->user()->id;

        if (!$esMiembro && !$esCreador) {
            return $this->errorResponse('No tienes acceso a esta comunidad', 403);
        }

        return $this->successResponse($comunidad, 'Comunidad encontrada', 200);
    }

    public function ranking(Request $request, $comunidad_id){


        $comunidad = Comunidad::findOrFail($comunidad_id);

        $esMiembro = $comunidad->users()
                ->where('user_id', $request->user()->id)
                ->wherePivot('estado_solicitud', 'aceptado')
                ->exists();

        $esCreador = $comunidad->creador_id === $request->user()->id;

        if (!$esMiembro && !$esCreador) {
            return $this->errorResponse('No tienes acceso a esta comunidad', 403);
        }

        $miembros = $comunidad->users()
                ->wherePivot('estado_solicitud', 'aceptado')
                ->get();

        $ranking = $miembros->map(function($miembro) {

        return [
            'nombre' => $miembro->name,
            'puntos' => (int) Prediccion::where('user_id', $miembro->id)->sum('puntos_ganados')
        ];
        })->sortByDesc('puntos')->values();

        return $this->successResponse($ranking, 'Ranking de la comunidad', 200);

    }
        


}

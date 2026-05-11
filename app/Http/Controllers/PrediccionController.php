<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Services\PrediccionService;
use App\Traits\TraitApiResponse;
use Illuminate\Http\Request;

class PrediccionController extends Controller
{
    use TraitApiResponse;

    public function store(Request $request){

        $request->validate([
            'partido_id' => ['required', 'integer'],
            'goles_equipo_A' => ['required', 'integer'],
            'goles_equipo_B' => ['required', 'integer'],
        ]);

        $service = new PrediccionService; 

        $response = $service->crearPrediccion(
            $request->partido_id,
            $request->user()->id, // devuelve el modelo user de la peticion, por el token de sanctum
            $request->goles_equipo_A,
            $request->goles_equipo_B
        );

        if ($response['error']){
            return $this->errorResponse($response['message'], $response['code']);

        } else{
            return $this->successResponse($response['data'], 'Predicción creada', 201);
        }
    }
    
}

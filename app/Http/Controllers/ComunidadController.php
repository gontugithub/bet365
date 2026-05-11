<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
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
}

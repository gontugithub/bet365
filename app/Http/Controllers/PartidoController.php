<?php

namespace App\Http\Controllers;

use App\Services\PartidoService;
use App\Traits\TraitApiResponse;
use Illuminate\Http\Request;

class PartidoController extends Controller
{
   use TraitApiResponse;

    public function importar(Request $request){

        $request->validate([
            'fichero' => ['required', 'file', 'mimes:csv,txt']
        ]);

        $service = new PartidoService();
        
        $partidos = $service->importarCSV($request->file('fichero'));

        return $this->successResponse($partidos, 'Partidos importados', 201);
    }
}

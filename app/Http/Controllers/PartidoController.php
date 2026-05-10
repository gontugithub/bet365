<?php

namespace App\Http\Controllers;

use App\Models\Partido;
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

    public function index(Request $request){

        $query = Partido::query();

        if ($request->has('fase')) {
            $query->where('fase', $request->fase);
        }

        if ($request->has('equipo')) {
            $query->where('equipo_A', $request->equipo)
            ->orWhere('equipo_B', $request->equipo);
        }

        $partidos = $query->get();

        return $this->successResponse($partidos, 'Soliciutd partidos', 200);

    }
}

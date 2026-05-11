<?php

namespace App\Services;

use App\Models\Partido;
use App\Models\Prediccion;
use Carbon\Carbon;

class PrediccionService
{

    public function comprobarFaseActual(){

        $faseActual = Partido::whereNull('goles_equipo_A')
                     ->orderBy('fecha_hora_partido', 'asc')
                     ->value('fase');

        return $faseActual;

    }

    public function crearPrediccion($partido_id, $user_id, $goles_equipo_A, $goles_equipo_B){

        $partido = Partido::findOrFail($partido_id);

        if ($partido->fase !== $this->comprobarFaseActual()) {
            return ['error' => true, 'message' => 'El partido no pertenece a la fase actual', 'code' => 422];
        }


        $hoy = Carbon::now();
        $fechaPartido = Carbon::parse($partido->fecha_hora_partido);

        if ($hoy->greaterThanOrEqualTo($fechaPartido->subDay())) {
            return ['error' => true, 'message' => 'Ya no puedes predecir este partido', 'code' => 422];
        }

        $prediccionExistente = Prediccion::where('user_id', $user_id)
                                 ->where('partido_id', $partido_id)
                                 ->first();

        if ($prediccionExistente) {
            return ['error' => true, 'message' => 'Ya tienes una predicción para este partido', 'code' => 422];
        }

        $prediccion = Prediccion::create([
            'user_id' => $user_id,
            'partido_id' => $partido_id,
            'goles_equipo_A' => $goles_equipo_A,
            'goles_equipo_B' => $goles_equipo_B
        ]);

        return ['error' => false, 'data' => $prediccion];

    }

    
}
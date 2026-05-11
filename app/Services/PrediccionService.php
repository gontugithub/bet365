<?php

namespace App\Services;

use App\Models\Partido;

class PrediccionService
{

    public function comprobarFaseActual(){

        $faseActual = Partido::whereNull('goles_eqipo_A')
                     ->orderBy('fecha_hora_partido', 'asc')
                     ->value('fase');

        return $faseActual;

    }
}
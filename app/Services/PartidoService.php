<?php

namespace App\Services;

use App\Models\Partido;

class PartidoService
{
    public function importarCSV($fichero){

        // r es solo para leer el archivo

        $handle = fopen($fichero->getRealPath(), 'r');

         // entro en la cabecera del csv, en la primera fila, nos la saltamos porque aqui estan los titulos

        fgetcsv($handle); // saltar cabecera

        $partidos = [];

        // ahora entramos en un bucle qeu va ir leyendo cada fila 
        while (($fila = fgetcsv($handle)) !== false) {
           $partidos[] = Partido::create([
                'id_event' => $fila[0],
                'fecha_hora_partido' => $fila[1],
                'fase' => $fila[2],
                'equipo_A' => $fila[3],
                // para evitar que nos de error la cadena vacia ponemos un null
                'goles_eqipo_A' => $fila[4]!== '' ? $fila[4] : null,
                'equipo_B' => $fila[5],
                'goles_eqipo_B' => $fila[6]!== '' ? $fila[6] : null,
            ]
            );
        }

        fclose($handle);
        return $partidos;

        
    }
}
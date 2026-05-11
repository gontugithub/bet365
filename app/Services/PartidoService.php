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

        // ahora entramos en un bucle qeu va ir leyendo cada fila, tambien hacemos del tiron el update y el crear partidos
        // con updateOrCreate busco primero si existe si existe actualiza y sino los crea
        while (($fila = fgetcsv($handle)) !== false) {
           $partido = Partido::updateOrCreate(['id_event' => $fila[0]],[
                'id_event' => $fila[0],
                'fecha_hora_partido' => $fila[1],
                'fase' => $fila[2],
                'equipo_A' => $fila[3],
                // para evitar que nos de error la cadena vacia ponemos un null
                'goles_equipo_A' => $fila[4]!== '' ? $fila[4] : null,
                'equipo_B' => $fila[5],
                'goles_equipo_B' => $fila[6]!== '' ? $fila[6] : null,
            ]
            );

            // calculo los puntos, y actualizo el campo puntos_ganados
            $this->calcularPuntos($partido);
            $partidos[] = $partido; // voy guardando partidos idnividualmente despues de guardarlos de uno en uno.
        }

        fclose($handle);
        return $partidos;

    }

    public function calcularPuntos($partido){

        // si no hay resultado
        if (is_null($partido->goles_equipo_A)) return;

        $predicciones = $partido->predicciones;

        
        if ($partido->goles_equipo_A > $partido->goles_equipo_B) {
            $ganadorReal = 'A';
        } elseif ($partido->goles_equipo_B > $partido->goles_equipo_A) {
            $ganadorReal = 'B';
        } else {
            $ganadorReal = 'empate';
        }

        foreach ($predicciones as $prediccion) {
            // Determinar ganador predicho
            if ($prediccion->goles_equipo_A > $prediccion->goles_equipo_B) {
                $ganadorPredicho = 'A';
            } elseif ($prediccion->goles_equipo_B > $prediccion->goles_equipo_A) {
                $ganadorPredicho = 'B';
            } else {
                $ganadorPredicho = 'empate';
            }

            // Calcular puntos
            if ($ganadorPredicho === $ganadorReal && 
            $prediccion->goles_equipo_A == $partido->goles_equipo_A && 
            $prediccion->goles_equipo_B == $partido->goles_equipo_B ) {
                $puntos = 3;
            } elseif ($ganadorPredicho === $ganadorReal) {
                $puntos = 1;
            } else {
                $puntos = 0;
            }

            $prediccion->update(['puntos_ganados' => $puntos]);

        }
    }
}
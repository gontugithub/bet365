<?php

namespace Database\Seeders;

use App\Models\Comunidad;
use App\Models\Prediccion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear 5 usuarios normales
        $usuarios = [
            User::create([
                'name'     => 'Carlos García',
                'email'    => 'carlos@test.com',
                'password' => Hash::make('password123'),
                'rol'      => 'usuario'
            ]),
            User::create([
                'name'     => 'María López',
                'email'    => 'maria@test.com',
                'password' => Hash::make('password123'),
                'rol'      => 'usuario'
            ]),
            User::create([
                'name'     => 'Pedro Martínez',
                'email'    => 'pedro@test.com',
                'password' => Hash::make('password123'),
                'rol'      => 'usuario'
            ]),
            User::create([
                'name'     => 'Ana Sánchez',
                'email'    => 'ana@test.com',
                'password' => Hash::make('password123'),
                'rol'      => 'usuario'
            ]),
            User::create([
                'name'     => 'Luis Fernández',
                'email'    => 'luis@test.com',
                'password' => Hash::make('password123'),
                'rol'      => 'usuario'
            ]),
        ];

        // 2. Crear una comunidad (creada por el admin, id=1)
        $comunidad = Comunidad::create([
            'nombre'     => 'Liga de Campeones',
            'codigo'     => 'WLD026',
            'creador_id' => 1
        ]);

        // 3. Añadir todos los usuarios a la comunidad como aceptados
        foreach ($usuarios as $usuario) {
            $comunidad->users()->attach($usuario->id, [
                'estado_solicitud' => 'aceptado'
            ]);
        }

        // 4. Predicciones variadas para los primeros 3 partidos
        // Partido 1: Mexico vs South Africa (id=1)
        // Partido 2: South Korea vs Czech Republic (id=2)
        // Partido 3: Canada vs Bosnia-Herzegovina (id=3)

        $predicciones = [
            // Carlos — acierta resultado exacto partido 1 (3pts), acierta ganador partido 2 (1pt), falla partido 3 (0pts)
            ['user_id' => $usuarios[0]->id, 'partido_id' => 1, 'goles_equipo_A' => 2, 'goles_equipo_B' => 1],
            ['user_id' => $usuarios[0]->id, 'partido_id' => 2, 'goles_equipo_A' => 3, 'goles_equipo_B' => 1],
            ['user_id' => $usuarios[0]->id, 'partido_id' => 3, 'goles_equipo_A' => 0, 'goles_equipo_B' => 2],

            // María — acierta ganador partido 1 (1pt), acierta resultado exacto partido 2 (3pts), acierta ganador partido 3 (1pt)
            ['user_id' => $usuarios[1]->id, 'partido_id' => 1, 'goles_equipo_A' => 3, 'goles_equipo_B' => 0],
            ['user_id' => $usuarios[1]->id, 'partido_id' => 2, 'goles_equipo_A' => 2, 'goles_equipo_B' => 0],
            ['user_id' => $usuarios[1]->id, 'partido_id' => 3, 'goles_equipo_A' => 1, 'goles_equipo_B' => 0],

            // Pedro — falla todo (0pts)
            ['user_id' => $usuarios[2]->id, 'partido_id' => 1, 'goles_equipo_A' => 0, 'goles_equipo_B' => 2],
            ['user_id' => $usuarios[2]->id, 'partido_id' => 2, 'goles_equipo_A' => 0, 'goles_equipo_B' => 1],
            ['user_id' => $usuarios[2]->id, 'partido_id' => 3, 'goles_equipo_A' => 0, 'goles_equipo_B' => 3],

            // Ana — acierta resultado exacto partido 1 (3pts), falla partido 2 (0pts), acierta resultado exacto partido 3 (3pts)
            ['user_id' => $usuarios[3]->id, 'partido_id' => 1, 'goles_equipo_A' => 2, 'goles_equipo_B' => 1],
            ['user_id' => $usuarios[3]->id, 'partido_id' => 2, 'goles_equipo_A' => 0, 'goles_equipo_B' => 2],
            ['user_id' => $usuarios[3]->id, 'partido_id' => 3, 'goles_equipo_A' => 2, 'goles_equipo_B' => 0],

            // Luis — acierta ganador partido 1 (1pt), acierta ganador partido 2 (1pt), falla partido 3 (0pts)
            ['user_id' => $usuarios[4]->id, 'partido_id' => 1, 'goles_equipo_A' => 1, 'goles_equipo_B' => 0],
            ['user_id' => $usuarios[4]->id, 'partido_id' => 2, 'goles_equipo_A' => 1, 'goles_equipo_B' => 0],
            ['user_id' => $usuarios[4]->id, 'partido_id' => 3, 'goles_equipo_A' => 0, 'goles_equipo_B' => 4],
        ];

        foreach ($predicciones as $prediccion) {
            Prediccion::create($prediccion);
        }
    }
}
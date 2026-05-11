<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partido extends Model
{

    protected $fillable = ['id_event','equipo_A', 'equipo_B', 'fase', 'fecha_hora_partido', 'goles_equipo_A', 'goles_equipo_B'];

    public function predicciones(): HasMany
    {
        return $this->hasMany(Prediccion::class);
    }
    

}

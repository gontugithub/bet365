<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediccion extends Model
{
    protected $fillable = ['partido_id','user_id','goles_eqipo_A', 'goles_eqipo_B', 'puntos_ganados'];
    protected $table = 'predicciones';

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comunidad extends Model
{

    // N:M una comunidad tiene varios usuarios y varios usuarios pertenecen a una comunidad
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
        // aqui tambien tendira que añadir algun campo extra de la base de datos pivote
    }

    // aqui hay que indicar a que columna en concreto
    public function user_creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }


}

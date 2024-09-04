<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genera extends Model
{
    use HasFactory;

    public function notas()
    {

        return $this->belongsToMany(User::class, 'generas', 'idnotas', 'idusuario');

    }

    public function users()
    {

        return $this->belongsToMany(Nota::class, 'generas', 'idusuario', 'idnotas');

    }
}

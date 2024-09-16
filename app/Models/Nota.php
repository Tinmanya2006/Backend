<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'descripcion',
        'categoria',
        'prioridad',
        'asignacion',
        'idusuario',
        'idgrupo',
        'id',
        'estado',
        'finalizacion',
    ];


    public function users()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }
}

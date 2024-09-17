<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'admin',
        'descripcion',
        'idusuario',
    ];

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }

}

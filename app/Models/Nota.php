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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

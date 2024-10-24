<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $primaryKey = "id";

    protected $fillable = [
        'titulo',
        'mensaje',
        'estado',
        'idusuario',
        'idgrupo',
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}

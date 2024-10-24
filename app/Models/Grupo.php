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
        'logo',
        'miembros'
    ];

    protected $casts = [
        'miembros' => 'array', // Asegúrate de que el campo 'miembros' sea tratado como un array
    ];

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }

    public function agregarMiembro($idusuario)
    {
        // Obtener los miembros actuales
        $miembros = $this->miembros ?? [];

        if (!is_array($miembros)) {
            $miembros = json_decode($miembros, true); // Asumimos que está almacenado en formato JSON
            // Si $miembros sigue sin ser un array, inicializarlo como array vacío
            if (!is_array($miembros)) {
                $miembros = [];
            }
        }

        if (!in_array($idusuario, $miembros)) {
            $miembros[] = $idusuario;
            $this->miembros = json_encode($miembros); // Guardar como JSON si el campo es de este tipo
            $this->save(); // Guardar el grupo actualizado
        }
    }

    // Método para eliminar un miembro
    public function eliminarMiembro($idusuario)
    {
        // Obtener los miembros actuales
        $miembros = $this->miembros ?? [];

        // Eliminar el miembro si existe
        if (in_array($idusuario, $miembros)) {
            $miembros = array_diff($miembros, [$idusuario]); // Elimina el usuario
            $this->miembros = $miembros;
            $this->save(); // Guardar el grupo actualizado
        }
    }
}

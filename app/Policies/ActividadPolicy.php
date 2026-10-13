<?php

namespace App\Policies;

use App\Models\Actividad;
use App\Models\Usuario;

class ActividadPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'actividades';

    public function view(Usuario $usuario, Actividad $actividad): bool
    {
        return $this->puede($usuario, 'ver');
    }

    public function update(Usuario $usuario, Actividad $actividad): bool
    {
        return $this->puede($usuario, 'editar');
    }

    public function delete(Usuario $usuario, Actividad $actividad): bool
    {
        return $this->puede($usuario, 'eliminar');
    }
}

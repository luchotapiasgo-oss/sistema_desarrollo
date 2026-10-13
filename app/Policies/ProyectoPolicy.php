<?php

namespace App\Policies;

use App\Models\Proyecto;
use App\Models\Usuario;

class ProyectoPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'proyectos';

    public function view(Usuario $usuario, Proyecto $proyecto): bool
    {
        return $this->puede($usuario, 'ver');
    }

    public function update(Usuario $usuario, Proyecto $proyecto): bool
    {
        return $this->puede($usuario, 'editar');
    }

    public function delete(Usuario $usuario, Proyecto $proyecto): bool
    {
        return $this->puede($usuario, 'eliminar');
    }

    /**
     * Alta, edicion y baja de integrantes del proyecto.
     */
    public function gestionarIntegrantes(Usuario $usuario, Proyecto $proyecto): bool
    {
        return $this->puede($usuario, 'editar');
    }
}

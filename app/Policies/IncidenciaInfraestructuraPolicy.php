<?php

namespace App\Policies;

use App\Models\IncidenciaInfraestructura;
use App\Models\Usuario;

class IncidenciaInfraestructuraPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'incidencias';

    public function view(Usuario $usuario, IncidenciaInfraestructura $incidencia): bool
    {
        return $this->puede($usuario, 'ver');
    }

    public function update(Usuario $usuario, IncidenciaInfraestructura $incidencia): bool
    {
        return $this->puede($usuario, 'editar');
    }

    public function delete(Usuario $usuario, IncidenciaInfraestructura $incidencia): bool
    {
        return $this->puede($usuario, 'eliminar');
    }

    /**
     * Cerrar la incidencia como resuelta (fecha de resolucion y costo).
     */
    public function resolver(Usuario $usuario, IncidenciaInfraestructura $incidencia): bool
    {
        return $incidencia->estado !== 'resuelta' && $this->puede($usuario, 'resolver');
    }
}

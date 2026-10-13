<?php

namespace App\Policies;

use App\Models\MovimientoEconomico;
use App\Models\Usuario;

class MovimientoEconomicoPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'movimientos_economicos';

    public function view(Usuario $usuario, MovimientoEconomico $movimiento): bool
    {
        return $this->puede($usuario, 'ver');
    }

    /**
     * Un movimiento anulado queda cerrado: solo se puede consultar o eliminar.
     */
    public function update(Usuario $usuario, MovimientoEconomico $movimiento): bool
    {
        return $movimiento->estado !== 'anulado' && $this->puede($usuario, 'editar');
    }

    public function delete(Usuario $usuario, MovimientoEconomico $movimiento): bool
    {
        return $this->puede($usuario, 'eliminar');
    }

    public function export(Usuario $usuario): bool
    {
        return $this->puede($usuario, 'exportar');
    }
}

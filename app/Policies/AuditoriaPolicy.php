<?php

namespace App\Policies;

use App\Models\Auditoria;
use App\Models\Usuario;

/**
 * La auditoria es de solo lectura: los registros los genera el trait
 * RegistraAuditoria y nadie, ni el administrador, puede crearlos ni editarlos.
 */
class AuditoriaPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'auditorias';

    public function view(Usuario $usuario, Auditoria $auditoria): bool
    {
        return $this->puede($usuario, 'ver');
    }

    public function create(Usuario $usuario): bool
    {
        return false;
    }

    public function update(Usuario $usuario, Auditoria $auditoria): bool
    {
        return false;
    }

    public function delete(Usuario $usuario, Auditoria $auditoria): bool
    {
        return false;
    }

    public function export(Usuario $usuario): bool
    {
        return $this->puede($usuario, 'exportar');
    }
}

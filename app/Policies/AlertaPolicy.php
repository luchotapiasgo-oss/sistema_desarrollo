<?php

namespace App\Policies;

use App\Models\Alerta;
use App\Models\Usuario;

class AlertaPolicy extends PolicyPorPermiso
{
    protected string $modulo = 'alertas';

    /**
     * El destinatario siempre puede ver sus propias alertas.
     */
    public function view(Usuario $usuario, Alerta $alerta): bool
    {
        return $this->esDestinatario($usuario, $alerta) || $this->puede($usuario, 'ver');
    }

    public function update(Usuario $usuario, Alerta $alerta): bool
    {
        return $this->puede($usuario, 'editar');
    }

    public function delete(Usuario $usuario, Alerta $alerta): bool
    {
        return $this->puede($usuario, 'eliminar');
    }

    /**
     * Marcar la alerta como atendida: el destinatario o quien tenga el permiso.
     */
    public function atender(Usuario $usuario, Alerta $alerta): bool
    {
        if ($alerta->estado === 'atendida') {
            return false;
        }

        return $this->esDestinatario($usuario, $alerta) || $this->puede($usuario, 'atender');
    }

    private function esDestinatario(Usuario $usuario, Alerta $alerta): bool
    {
        return $usuario->estaActivo() && $alerta->usuario_id === $usuario->id;
    }
}

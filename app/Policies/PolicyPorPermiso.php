<?php

namespace App\Policies;

use App\Models\Usuario;
use WeakMap;

/**
 * Base de las policies que se resuelven contra el catalogo de permisos
 * (`modulo.accion`, ver PermisoSeeder). Cada policy hija solo define el
 * modulo y, si hace falta, acciones extra.
 */
abstract class PolicyPorPermiso
{
    /**
     * Modulo de la tabla permisos (por ejemplo "actividades").
     */
    protected string $modulo;

    /**
     * Permisos ya consultados por usuario durante la peticion, para no
     * repetir la consulta en cada @can del menu y de los listados.
     *
     * @var WeakMap<Usuario, array<int, string>>|null
     */
    private static ?WeakMap $cache = null;

    public function viewAny(Usuario $usuario): bool
    {
        return $this->puede($usuario, 'ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $this->puede($usuario, 'crear');
    }

    protected function puede(Usuario $usuario, string $accion): bool
    {
        if (! $usuario->estaActivo()) {
            return false;
        }

        self::$cache ??= new WeakMap;
        self::$cache[$usuario] ??= $usuario->permisos()->pluck('nombre')->all();

        return in_array($this->modulo.'.'.$accion, self::$cache[$usuario], true);
    }
}

<?php

namespace Tests\Feature\Concerns;

use App\Models\Oficina;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;

/**
 * Catalogo de permisos y roles reales (PermisoSeeder + RolSeeder) y helpers
 * para crear usuarios, oficinas y policias en los tests.
 */
trait PreparaEscenario
{
    protected function sembrarPermisos(): void
    {
        $this->seed([PermisoSeeder::class, RolSeeder::class]);
    }

    protected function usuarioConRol(string $rol): Usuario
    {
        return Usuario::factory()->create([
            'rol_id' => Rol::where('nombre', $rol)->value('id'),
        ]);
    }

    protected function crearOficina(string $nombre = 'Oficina Central'): Oficina
    {
        return Oficina::create(['nombre' => $nombre]);
    }

    protected function crearPolicia(string $apellido = 'Perez'): Policia
    {
        static $secuencia = 0;
        $secuencia++;

        $persona = Persona::create([
            'dni' => (string) (30000000 + $secuencia),
            'nombre' => 'Juan',
            'apellido' => $apellido,
        ]);

        return Policia::create([
            'persona_id' => $persona->id,
            'numero_legajo' => 'L-'.$secuencia,
        ]);
    }
}

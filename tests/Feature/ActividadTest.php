<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Auditoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\PreparaEscenario;
use Tests\TestCase;

class ActividadTest extends TestCase
{
    use PreparaEscenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sembrarPermisos();
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'oficina_id' => $this->crearOficina()->id,
            'titulo' => 'Operativo de control',
            'fecha_inicio' => '2026-10-01 08:00',
            'fecha_fin' => '2026-10-01 12:00',
            'prioridad' => 'alta',
            'estado' => 'pendiente',
        ], $cambios);
    }

    public function test_sin_sesion_no_accede(): void
    {
        // getJson: con HTML el middleware redirige a la ruta login, que aun no existe.
        $this->getJson('/actividades')->assertUnauthorized();
    }

    public function test_crud_completo_con_encargado(): void
    {
        $usuario = $this->usuarioConRol('encargado_oficina');

        $this->actingAs($usuario)->get(route('actividades.index'))->assertOk();
        $this->actingAs($usuario)->get(route('actividades.create'))->assertOk();

        $this->actingAs($usuario)->post(route('actividades.store'), $this->datosValidos())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $actividad = Actividad::firstOrFail();
        $this->assertSame('Operativo de control', $actividad->titulo);
        $this->assertTrue(Auditoria::where('tabla_afectada', 'actividades')->where('accion', 'crear')->exists());

        $this->actingAs($usuario)->get(route('actividades.show', $actividad))->assertOk()->assertSee('Operativo de control');
        $this->actingAs($usuario)->get(route('actividades.edit', $actividad))->assertOk();

        $this->actingAs($usuario)->put(route('actividades.update', $actividad), $this->datosValidos(['estado' => 'finalizada']))
            ->assertSessionHasNoErrors();
        $this->assertSame('finalizada', $actividad->fresh()->estado);

        $this->actingAs($usuario)->delete(route('actividades.destroy', $actividad))->assertRedirect(route('actividades.index'));
        $this->assertModelMissing($actividad);
    }

    public function test_validaciones(): void
    {
        $usuario = $this->usuarioConRol('administrador');

        $this->actingAs($usuario)->post(route('actividades.store'), $this->datosValidos([
            'fecha_fin' => '2026-09-01 08:00',
            'prioridad' => 'urgentisima',
            'oficina_id' => 999,
        ]))->assertSessionHasErrors(['fecha_fin', 'prioridad', 'oficina_id']);

        $this->actingAs($usuario)->post(route('actividades.store'), $this->datosValidos([
            'estado' => 'finalizada',
            'fecha_fin' => null,
        ]))->assertSessionHasErrors('fecha_fin');

        $this->assertSame(0, Actividad::count());
    }

    public function test_invitado_solo_puede_ver(): void
    {
        $usuario = $this->usuarioConRol('invitado');
        $actividad = Actividad::create($this->datosValidos());

        $this->actingAs($usuario)->get(route('actividades.index'))->assertOk();
        $this->actingAs($usuario)->get(route('actividades.create'))->assertForbidden();
        $this->actingAs($usuario)->post(route('actividades.store'), $this->datosValidos())->assertForbidden();
        $this->actingAs($usuario)->delete(route('actividades.destroy', $actividad))->assertForbidden();
    }

    public function test_rol_sin_modulo_no_accede(): void
    {
        $this->actingAs($this->usuarioConRol('soporte'))->get(route('actividades.index'))->assertForbidden();
    }

    public function test_usuario_inactivo_no_tiene_permisos(): void
    {
        $usuario = $this->usuarioConRol('administrador');
        $usuario->update(['activo' => false]);

        $this->actingAs($usuario)->get(route('actividades.index'))->assertForbidden();
    }
}

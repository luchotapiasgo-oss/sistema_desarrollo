<?php

namespace Tests\Feature;

use App\Models\IncidenciaInfraestructura;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\PreparaEscenario;
use Tests\TestCase;

class IncidenciaInfraestructuraTest extends TestCase
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
            'tipo' => 'electrica',
            'titulo' => 'Corte de luz en guardia',
            'fecha_reporte' => '2026-10-01 09:00',
            'prioridad' => 'alta',
            'estado' => 'pendiente',
            'costo' => '1500.50',
        ], $cambios);
    }

    public function test_crud(): void
    {
        $usuario = $this->usuarioConRol('soporte');

        $this->actingAs($usuario)->get(route('incidencias.create'))->assertOk();
        $this->actingAs($usuario)->post(route('incidencias.store'), $this->datosValidos())->assertSessionHasNoErrors();

        $incidencia = IncidenciaInfraestructura::firstOrFail();
        $this->actingAs($usuario)->get(route('incidencias.index'))->assertOk()->assertSee('Corte de luz');
        $this->actingAs($usuario)->get(route('incidencias.show', $incidencia))->assertOk()->assertSee('Resolver incidencia');

        $this->actingAs($usuario)->put(route('incidencias.update', $incidencia), $this->datosValidos(['estado' => 'en_proceso']))
            ->assertSessionHasNoErrors();
        $this->assertSame('en_proceso', $incidencia->fresh()->estado);

        $this->actingAs($usuario)->delete(route('incidencias.destroy', $incidencia))->assertRedirect();
        $this->assertModelMissing($incidencia);
    }

    public function test_validaciones(): void
    {
        $usuario = $this->usuarioConRol('administrador');

        $this->actingAs($usuario)->post(route('incidencias.store'), $this->datosValidos([
            'tipo' => 'inventado',
            'costo' => -5,
            'fecha_reporte' => now()->addDay()->format('Y-m-d H:i'),
        ]))->assertSessionHasErrors(['tipo', 'costo', 'fecha_reporte']);

        $this->actingAs($usuario)->post(route('incidencias.store'), $this->datosValidos(['estado' => 'resuelta']))
            ->assertSessionHasErrors('fecha_resolucion');

        $this->actingAs($usuario)->post(route('incidencias.store'), $this->datosValidos([
            'estado' => 'resuelta',
            'fecha_resolucion' => '2026-09-01 10:00',
        ]))->assertSessionHasErrors('fecha_resolucion');
    }

    public function test_resolver(): void
    {
        $usuario = $this->usuarioConRol('soporte');
        $incidencia = IncidenciaInfraestructura::create($this->datosValidos(['observaciones' => 'Reportado por guardia']));

        $this->actingAs($usuario)->patch(route('incidencias.resolver', $incidencia), [
            'fecha_resolucion' => '2026-10-02 10:00',
            'costo' => '2000',
            'observaciones' => 'Se cambio el disyuntor',
        ])->assertSessionHasNoErrors();

        $incidencia->refresh();
        $this->assertSame('resuelta', $incidencia->estado);
        $this->assertEquals(2000, $incidencia->costo);
        $this->assertStringContainsString('Reportado por guardia', $incidencia->observaciones);
        $this->assertStringContainsString('Se cambio el disyuntor', $incidencia->observaciones);

        // Ya resuelta: no se vuelve a resolver.
        $this->actingAs($usuario)->patch(route('incidencias.resolver', $incidencia), ['fecha_resolucion' => '2026-10-03 10:00'])
            ->assertForbidden();

        // Al reabrirla se limpia la fecha de resolucion.
        $this->actingAs($usuario)->put(route('incidencias.update', $incidencia), $this->datosValidos(['estado' => 'en_proceso', 'fecha_resolucion' => '2026-10-02 10:00']))
            ->assertSessionHasNoErrors();
        $this->assertNull($incidencia->fresh()->fecha_resolucion);
    }

    public function test_sin_permiso_resolver_no_puede_cerrar_por_edicion(): void
    {
        $rol = Rol::create(['nombre' => 'editor_incidencias', 'activo' => true]);
        $rol->permisos()->sync(Permiso::where('modulo', 'incidencias')->where('accion', '!=', 'resolver')->pluck('id'));
        $usuario = $this->usuarioConRol('editor_incidencias');
        $incidencia = IncidenciaInfraestructura::create($this->datosValidos());

        $this->actingAs($usuario)->put(route('incidencias.update', $incidencia), $this->datosValidos([
            'estado' => 'resuelta',
            'fecha_resolucion' => '2026-10-02 10:00',
        ]))->assertSessionHasErrors('estado');

        $this->actingAs($usuario)->patch(route('incidencias.resolver', $incidencia), ['fecha_resolucion' => '2026-10-02 10:00'])
            ->assertForbidden();

        $this->assertSame('pendiente', $incidencia->fresh()->estado);
    }
}

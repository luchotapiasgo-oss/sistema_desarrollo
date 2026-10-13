<?php

namespace Tests\Feature;

use App\Models\MovimientoEconomico;
use App\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\PreparaEscenario;
use Tests\TestCase;

class ProyectoTest extends TestCase
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
            'nombre' => 'Modernizacion de comisarias',
            'codigo' => 'pry-001',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'prioridad' => 'media',
            'estado' => 'en_curso',
            'porcentaje_avance' => 40,
        ], $cambios);
    }

    public function test_crud_y_ficha(): void
    {
        $usuario = $this->usuarioConRol('encargado_oficina');
        $responsable = $this->crearPolicia('Gomez');

        $this->actingAs($usuario)->post(route('proyectos.store'), $this->datosValidos([
            'responsable_id' => $responsable->id,
            'oficina_id' => $this->crearOficina()->id,
        ]))->assertSessionHasNoErrors();

        $proyecto = Proyecto::firstOrFail();
        $this->assertSame('PRY-001', $proyecto->codigo, 'El codigo se normaliza a mayusculas');

        MovimientoEconomico::create(['tipo' => 'ingreso', 'categoria' => 'Fondos', 'fecha' => '2026-02-01', 'concepto' => 'Partida', 'monto' => 1000, 'proyecto_id' => $proyecto->id]);
        MovimientoEconomico::create(['tipo' => 'egreso', 'categoria' => 'Compras', 'fecha' => '2026-02-02', 'concepto' => 'Equipos', 'monto' => 300, 'proyecto_id' => $proyecto->id]);
        MovimientoEconomico::create(['tipo' => 'egreso', 'categoria' => 'Compras', 'fecha' => '2026-02-03', 'concepto' => 'Anulado', 'monto' => 999, 'proyecto_id' => $proyecto->id, 'estado' => 'anulado']);

        $this->actingAs($usuario)->get(route('proyectos.index'))->assertOk()->assertSee('PRY-001');
        $this->actingAs($usuario)->get(route('proyectos.show', $proyecto))
            ->assertOk()
            ->assertSee('Gomez')
            ->assertSee('700,00'); // balance sin el anulado

        $this->actingAs($usuario)->put(route('proyectos.update', $proyecto), $this->datosValidos(['porcentaje_avance' => 60]))
            ->assertSessionHasNoErrors();
        $this->assertEquals(60, $proyecto->fresh()->porcentaje_avance);

        $this->actingAs($usuario)->delete(route('proyectos.destroy', $proyecto))->assertRedirect();
        $this->assertModelMissing($proyecto);
        $this->assertSame(3, MovimientoEconomico::whereNull('proyecto_id')->count());
    }

    public function test_reglas_de_negocio(): void
    {
        $usuario = $this->usuarioConRol('administrador');
        Proyecto::create($this->datosValidos(['codigo' => 'PRY-001']));

        $this->actingAs($usuario)->post(route('proyectos.store'), $this->datosValidos())
            ->assertSessionHasErrors('codigo');

        $this->actingAs($usuario)->post(route('proyectos.store'), $this->datosValidos(['codigo' => 'X1', 'estado' => 'finalizado', 'porcentaje_avance' => 90]))
            ->assertSessionHasErrors('porcentaje_avance');

        $this->actingAs($usuario)->post(route('proyectos.store'), $this->datosValidos(['codigo' => 'X2', 'estado' => 'planificado', 'porcentaje_avance' => 10]))
            ->assertSessionHasErrors('porcentaje_avance');

        $this->actingAs($usuario)->post(route('proyectos.store'), $this->datosValidos(['codigo' => 'X3', 'porcentaje_avance' => 150, 'fecha_fin' => '2025-01-01']))
            ->assertSessionHasErrors(['porcentaje_avance', 'fecha_fin']);

        $this->assertSame(1, Proyecto::count());
    }

    public function test_actualizar_conserva_su_propio_codigo(): void
    {
        $usuario = $this->usuarioConRol('administrador');
        $proyecto = Proyecto::create($this->datosValidos(['codigo' => 'PRY-001']));

        $this->actingAs($usuario)->put(route('proyectos.update', $proyecto), $this->datosValidos(['codigo' => 'PRY-001']))
            ->assertSessionHasNoErrors();
    }

    public function test_integrantes(): void
    {
        $usuario = $this->usuarioConRol('encargado_oficina');
        $proyecto = Proyecto::create($this->datosValidos(['codigo' => 'PRY-001']));
        $policia = $this->crearPolicia();

        $this->actingAs($usuario)->post(route('proyectos.integrantes.store', $proyecto), [
            'policia_id' => $policia->id,
            'fecha_ingreso' => '2026-02-01',
        ])->assertSessionHasNoErrors();

        $integrante = $proyecto->integrantes()->firstOrFail();

        // Duplicado y fecha anterior al inicio del proyecto.
        $this->actingAs($usuario)->post(route('proyectos.integrantes.store', $proyecto), [
            'policia_id' => $policia->id,
            'fecha_ingreso' => '2025-01-01',
        ])->assertSessionHasErrors(['policia_id', 'fecha_ingreso']);

        $this->actingAs($usuario)->get(route('proyectos.integrantes.edit', [$proyecto, $integrante]))->assertOk();
        $this->actingAs($usuario)->put(route('proyectos.integrantes.update', [$proyecto, $integrante]), [
            'fecha_ingreso' => '2026-02-01',
            'fecha_salida' => '2026-01-15',
        ])->assertSessionHasErrors('fecha_salida');

        $this->actingAs($usuario)->put(route('proyectos.integrantes.update', [$proyecto, $integrante]), [
            'fecha_ingreso' => '2026-02-01',
            'fecha_salida' => '2026-06-30',
        ])->assertSessionHasNoErrors();
        $this->assertSame('2026-06-30', $integrante->fresh()->fecha_salida->toDateString());

        // Un integrante de otro proyecto no se puede tocar desde esta URL.
        $otro = Proyecto::create($this->datosValidos(['codigo' => 'PRY-002']));
        $this->actingAs($usuario)->delete(route('proyectos.integrantes.destroy', [$otro, $integrante]))->assertNotFound();

        $this->actingAs($usuario)->delete(route('proyectos.integrantes.destroy', [$proyecto, $integrante]))->assertRedirect();
        $this->assertSame(0, $proyecto->integrantes()->count());
    }

    public function test_invitado_no_gestiona_integrantes(): void
    {
        $proyecto = Proyecto::create($this->datosValidos(['codigo' => 'PRY-001']));

        $this->actingAs($this->usuarioConRol('invitado'))
            ->post(route('proyectos.integrantes.store', $proyecto), ['policia_id' => $this->crearPolicia()->id])
            ->assertForbidden();
    }
}

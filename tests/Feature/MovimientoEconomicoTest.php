<?php

namespace Tests\Feature;

use App\Models\MovimientoEconomico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\PreparaEscenario;
use Tests\TestCase;

class MovimientoEconomicoTest extends TestCase
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
            'tipo' => 'egreso',
            'categoria' => 'Insumos',
            'fecha' => '2026-10-01',
            'concepto' => 'Compra de resmas',
            'monto' => '12500.75',
            'estado' => 'registrado',
        ], $cambios);
    }

    public function test_crud_totales_y_exportacion(): void
    {
        $usuario = $this->usuarioConRol('administrativo');

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosValidos())->assertSessionHasNoErrors();
        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosValidos(['tipo' => 'ingreso', 'monto' => '50000', 'concepto' => 'Partida']))->assertSessionHasNoErrors();

        $this->actingAs($usuario)->get(route('movimientos.index'))
            ->assertOk()
            ->assertSee('50.000,00')
            ->assertSee('37.499,25');

        $movimiento = MovimientoEconomico::where('concepto', 'Compra de resmas')->firstOrFail();
        $this->actingAs($usuario)->get(route('movimientos.show', $movimiento))->assertOk();

        $respuesta = $this->actingAs($usuario)->get(route('movimientos.exportar', ['tipo' => 'egreso']));
        $respuesta->assertOk();
        $csv = $respuesta->streamedContent();
        $this->assertStringContainsString('Compra de resmas', $csv);
        $this->assertStringNotContainsString('Partida', $csv);

        $this->actingAs($usuario)->delete(route('movimientos.destroy', $movimiento))->assertRedirect();
        $this->assertModelMissing($movimiento);
    }

    public function test_validaciones(): void
    {
        $usuario = $this->usuarioConRol('administrador');

        $this->actingAs($usuario)->post(route('movimientos.store'), $this->datosValidos([
            'tipo' => 'transferencia',
            'monto' => 0,
            'fecha' => now()->addDays(2)->toDateString(),
            'proyecto_id' => 999,
        ]))->assertSessionHasErrors(['tipo', 'monto', 'fecha', 'proyecto_id']);
    }

    public function test_anulado_no_se_edita(): void
    {
        $usuario = $this->usuarioConRol('administrador');
        $movimiento = MovimientoEconomico::create($this->datosValidos(['estado' => 'anulado']));

        $this->actingAs($usuario)->get(route('movimientos.edit', $movimiento))->assertForbidden();
        $this->actingAs($usuario)->put(route('movimientos.update', $movimiento), $this->datosValidos())->assertForbidden();
    }

    public function test_invitado_no_ve_movimientos(): void
    {
        $this->actingAs($this->usuarioConRol('invitado'))->get(route('movimientos.index'))->assertForbidden();
    }
}

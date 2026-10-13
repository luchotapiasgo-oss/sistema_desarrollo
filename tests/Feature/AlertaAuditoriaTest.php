<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Alerta;
use App\Models\Auditoria;
use App\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\PreparaEscenario;
use Tests\TestCase;

class AlertaAuditoriaTest extends TestCase
{
    use PreparaEscenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sembrarPermisos();
    }

    private function proyecto(): Proyecto
    {
        return Proyecto::create(['nombre' => 'Proyecto', 'codigo' => 'P-1']);
    }

    public function test_crud_de_alertas(): void
    {
        $usuario = $this->usuarioConRol('encargado_oficina');
        $proyecto = $this->proyecto();

        $this->actingAs($usuario)
            ->get(route('alertas.create', ['alertable_tipo' => 'proyecto', 'alertable_id' => $proyecto->id]))
            ->assertOk();

        $this->actingAs($usuario)->post(route('alertas.store'), [
            'tipo' => 'Seguimiento',
            'titulo' => 'Revisar avance',
            'alertable_tipo' => 'proyecto',
            'alertable_id' => $proyecto->id,
            'usuario_id' => $usuario->id,
            'prioridad' => 'alta',
            'fecha_generacion' => '2026-10-01 08:00',
            'fecha_vencimiento' => '2026-10-10 08:00',
            'estado' => 'pendiente',
        ])->assertSessionHasNoErrors();

        $alerta = Alerta::firstOrFail();
        $this->assertSame(Proyecto::class, $alerta->alertable_tipo);
        $this->assertTrue($alerta->alertable->is($proyecto));

        $this->actingAs($usuario)->get(route('alertas.index'))->assertOk()->assertSee('Revisar avance');
        $this->actingAs($usuario)->get(route('proyectos.show', $proyecto))->assertSee('Revisar avance');

        // Al abrirla su destinatario queda leida.
        $this->actingAs($usuario)->get(route('alertas.show', $alerta))->assertOk();
        $this->assertSame('leida', $alerta->fresh()->estado);
        $this->assertNotNull($alerta->fresh()->fecha_lectura);

        $this->actingAs($usuario)->patch(route('alertas.atender', $alerta))->assertRedirect();
        $this->assertSame('atendida', $alerta->fresh()->estado);

        $this->actingAs($usuario)->delete(route('alertas.destroy', $alerta))->assertRedirect();
        $this->assertModelMissing($alerta);
    }

    public function test_validacion_del_registro_asociado(): void
    {
        $usuario = $this->usuarioConRol('administrador');

        $this->actingAs($usuario)->post(route('alertas.store'), [
            'tipo' => 'X',
            'titulo' => 'X',
            'alertable_tipo' => 'proyecto',
            'alertable_id' => 999,
            'prioridad' => 'media',
            'fecha_generacion' => '2026-10-05 08:00',
            'fecha_vencimiento' => '2026-10-01 08:00',
            'estado' => 'pendiente',
        ])->assertSessionHasErrors(['alertable_id', 'fecha_vencimiento']);

        $this->actingAs($usuario)->post(route('alertas.store'), [
            'tipo' => 'X',
            'titulo' => 'X',
            'alertable_tipo' => 'App\\Models\\Usuario',
            'alertable_id' => 1,
            'prioridad' => 'media',
            'fecha_generacion' => '2026-10-05 08:00',
            'estado' => 'pendiente',
        ])->assertSessionHasErrors('alertable_tipo');
    }

    public function test_destinatario_sin_permiso_ve_y_atiende_solo_las_suyas(): void
    {
        $soporte = $this->usuarioConRol('soporte'); // sin modulo alertas
        $proyecto = $this->proyecto();
        $base = [
            'tipo' => 'Aviso', 'alertable_tipo' => Proyecto::class, 'alertable_id' => $proyecto->id,
            'prioridad' => 'media', 'fecha_generacion' => now(), 'estado' => 'pendiente',
        ];
        $propia = Alerta::create($base + ['titulo' => 'Para soporte', 'usuario_id' => $soporte->id]);
        $ajena = Alerta::create($base + ['titulo' => 'Para otro']);

        $this->actingAs($soporte)->get(route('alertas.index'))
            ->assertOk()
            ->assertSee('Para soporte')
            ->assertDontSee('Para otro');

        $this->actingAs($soporte)->get(route('alertas.show', $ajena))->assertForbidden();
        $this->actingAs($soporte)->patch(route('alertas.atender', $ajena))->assertForbidden();
        $this->actingAs($soporte)->patch(route('alertas.atender', $propia))->assertRedirect();
        $this->actingAs($soporte)->get(route('alertas.create'))->assertForbidden();
    }

    public function test_auditoria_es_de_solo_lectura_y_exportable(): void
    {
        $usuario = $this->usuarioConRol('encargado_oficina');
        $this->actingAs($usuario);

        $actividad = Actividad::create([
            'oficina_id' => $this->crearOficina()->id,
            'titulo' => 'Auditada',
            'fecha_inicio' => '2026-10-01 08:00',
        ]);
        $actividad->update(['titulo' => 'Auditada editada']);

        $registro = Auditoria::where('tabla_afectada', 'actividades')->where('accion', 'editar')->firstOrFail();
        $this->assertSame($usuario->id, $registro->usuario_id);

        $this->get(route('auditorias.index', ['tabla' => 'actividades']))->assertOk()->assertSee('actividades');
        $this->get(route('auditorias.show', $registro))->assertOk()->assertSee('Auditada editada');

        $csv = $this->get(route('auditorias.exportar', ['accion' => 'editar']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Auditada editada', $csv);

        // No existen rutas de escritura.
        $this->post('/auditorias', [])->assertMethodNotAllowed();
        $this->delete('/auditorias/'.$registro->id)->assertMethodNotAllowed();

        $this->actingAs($this->usuarioConRol('invitado'))->get(route('auditorias.index'))->assertForbidden();
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Alerta;
use Closure;
use Illuminate\Validation\Rule;

class AlertaRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        $alerta = $this->route('alerta');

        return $alerta instanceof Alerta
            ? $this->user()->can('update', $alerta)
            : $this->user()->can('create', Alerta::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'max:100'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'alertable_tipo' => ['required', Rule::in(array_keys(Alerta::ALERTABLES))],
            'alertable_id' => ['required', 'integer', $this->existeAlertable()],
            'usuario_id' => ['nullable', 'integer', Rule::exists('usuarios', 'id')],
            'prioridad' => ['required', Rule::in(array_keys(Alerta::PRIORIDADES))],
            'fecha_generacion' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_generacion'],
            'estado' => ['required', Rule::in(array_keys(Alerta::ESTADOS))],
        ];
    }

    /**
     * Datos listos para guardar: alertable_tipo pasa de la clave del
     * formulario a la clase del modelo y fecha_lectura acompaña al estado.
     *
     * @return array<string, mixed>
     */
    public function datosAlerta(?Alerta $alerta = null): array
    {
        $datos = $this->validated();
        $datos['alertable_tipo'] = Alerta::ALERTABLES[$datos['alertable_tipo']];
        $datos['fecha_lectura'] = $datos['estado'] === 'pendiente'
            ? null
            : ($alerta?->fecha_lectura ?? now());

        return $datos;
    }

    /**
     * La relacion es polimorfica y no tiene FK: se comprueba a mano que el
     * registro exista en la tabla del tipo elegido.
     */
    private function existeAlertable(): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar) {
            $clase = Alerta::ALERTABLES[$this->input('alertable_tipo')] ?? null;

            if ($clase !== null && ! $clase::whereKey($valor)->exists()) {
                $fallar('El registro asociado no existe.');
            }
        };
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'alertable_tipo' => 'tipo de registro asociado',
            'alertable_id' => 'registro asociado',
            'usuario_id' => 'destinatario',
            'fecha_generacion' => 'fecha de generacion',
            'fecha_vencimiento' => 'fecha de vencimiento',
        ];
    }
}

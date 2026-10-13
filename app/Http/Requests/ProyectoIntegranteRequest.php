<?php

namespace App\Http\Requests;

use App\Models\Proyecto;
use App\Models\ProyectoIntegrante;
use Illuminate\Validation\Rule;

/**
 * Alta y edicion de integrantes. El policia solo se elige al dar de alta:
 * para cambiarlo hay que quitar el integrante y agregar otro.
 */
class ProyectoIntegranteRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        return $this->user()->can('gestionarIntegrantes', $this->proyecto());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $proyecto = $this->proyecto();

        $reglas = [
            'fecha_ingreso' => array_filter([
                'nullable', 'date',
                $proyecto->fecha_inicio ? 'after_or_equal:'.$proyecto->fecha_inicio->toDateString() : null,
            ]),
            'fecha_salida' => ['nullable', 'date', 'after_or_equal:fecha_ingreso'],
            'observaciones' => ['nullable', 'string'],
        ];

        if (! $this->route('integrante') instanceof ProyectoIntegrante) {
            $reglas['policia_id'] = [
                'required', 'integer',
                Rule::exists('policias', 'id'),
                Rule::unique('proyecto_integrantes', 'policia_id')->where('proyecto_id', $proyecto->id),
            ];
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'policia_id.unique' => 'El policia ya integra este proyecto.',
            'fecha_ingreso.after_or_equal' => 'La fecha de ingreso no puede ser anterior al inicio del proyecto.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'policia_id' => 'policia',
            'fecha_ingreso' => 'fecha de ingreso',
            'fecha_salida' => 'fecha de salida',
        ];
    }

    private function proyecto(): Proyecto
    {
        return $this->route('proyecto');
    }
}

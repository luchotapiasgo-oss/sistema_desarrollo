<?php

namespace App\Http\Requests;

use App\Models\IncidenciaInfraestructura;

class ResolverIncidenciaRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        return $this->user()->can('resolver', $this->route('incidencia'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var IncidenciaInfraestructura $incidencia */
        $incidencia = $this->route('incidencia');

        return [
            'fecha_resolucion' => [
                'required', 'date', 'before_or_equal:now',
                'after_or_equal:'.$incidencia->fecha_reporte->toDateTimeString(),
            ],
            'costo' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'fecha_resolucion.after_or_equal' => 'La fecha de resolucion no puede ser anterior a la fecha de reporte.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fecha_resolucion' => 'fecha de resolucion',
        ];
    }
}

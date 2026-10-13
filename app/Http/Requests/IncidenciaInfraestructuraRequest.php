<?php

namespace App\Http\Requests;

use App\Models\IncidenciaInfraestructura;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class IncidenciaInfraestructuraRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        $incidencia = $this->route('incidencia');

        return $incidencia instanceof IncidenciaInfraestructura
            ? $this->user()->can('update', $incidencia)
            : $this->user()->can('create', IncidenciaInfraestructura::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'oficina_id' => ['nullable', 'integer', Rule::exists('oficinas', 'id')],
            'tipo' => ['required', Rule::in(array_keys(IncidenciaInfraestructura::TIPOS))],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'fecha_reporte' => ['required', 'date', 'before_or_equal:now'],
            'prioridad' => ['required', Rule::in(array_keys(IncidenciaInfraestructura::PRIORIDADES))],
            'responsable_id' => ['nullable', 'integer', Rule::exists('policias', 'id')],
            'estado' => ['required', Rule::in(array_keys(IncidenciaInfraestructura::ESTADOS))],
            'costo' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'fecha_resolucion' => ['nullable', 'required_if:estado,resuelta', 'date', 'after_or_equal:fecha_reporte', 'before_or_equal:now'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * Pasar una incidencia a "resuelta" requiere el permiso incidencias.resolver,
     * no alcanza con incidencias.editar.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('estado') !== 'resuelta') {
                    return;
                }

                $incidencia = $this->route('incidencia') ?? new IncidenciaInfraestructura;

                if ($incidencia->estado !== 'resuelta' && ! $this->user()->can('resolver', $incidencia)) {
                    $validator->errors()->add('estado', 'No tiene permiso para marcar incidencias como resueltas.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'oficina_id' => 'oficina',
            'responsable_id' => 'responsable',
            'fecha_reporte' => 'fecha de reporte',
            'fecha_resolucion' => 'fecha de resolucion',
        ];
    }
}

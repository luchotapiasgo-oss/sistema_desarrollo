<?php

namespace App\Http\Requests;

use App\Models\Actividad;
use Illuminate\Validation\Rule;

class ActividadRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        $actividad = $this->route('actividad');

        return $actividad instanceof Actividad
            ? $this->user()->can('update', $actividad)
            : $this->user()->can('create', Actividad::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'oficina_id' => ['required', 'integer', Rule::exists('oficinas', 'id')],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            // Una actividad finalizada tiene que tener fecha de cierre.
            'fecha_fin' => ['nullable', 'required_if:estado,finalizada', 'date', 'after_or_equal:fecha_inicio'],
            'prioridad' => ['required', Rule::in(array_keys(Actividad::PRIORIDADES))],
            'estado' => ['required', Rule::in(array_keys(Actividad::ESTADOS))],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'oficina_id' => 'oficina',
            'titulo' => 'titulo',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
        ];
    }
}

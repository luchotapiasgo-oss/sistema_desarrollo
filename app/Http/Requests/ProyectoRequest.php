<?php

namespace App\Http\Requests;

use App\Models\Proyecto;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProyectoRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        $proyecto = $this->route('proyecto');

        return $proyecto instanceof Proyecto
            ? $this->user()->can('update', $proyecto)
            : $this->user()->can('create', Proyecto::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('codigo')) {
            $this->merge(['codigo' => mb_strtoupper(trim($this->input('codigo')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:200'],
            'codigo' => [
                'required', 'string', 'max:50',
                Rule::unique('proyectos', 'codigo')->ignore($this->route('proyecto')),
            ],
            'descripcion' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'oficina_id' => ['nullable', 'integer', Rule::exists('oficinas', 'id')],
            'responsable_id' => ['nullable', 'integer', Rule::exists('policias', 'id')],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'prioridad' => ['required', Rule::in(array_keys(Proyecto::PRIORIDADES))],
            'estado' => ['required', Rule::in(array_keys(Proyecto::ESTADOS))],
            'porcentaje_avance' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * Reglas que cruzan estado y avance.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $avance = (float) $this->input('porcentaje_avance');

                if ($this->input('estado') === 'finalizado' && $avance < 100) {
                    $validator->errors()->add('porcentaje_avance', 'Un proyecto finalizado debe tener el 100% de avance.');
                }

                if ($this->input('estado') === 'planificado' && $avance > 0) {
                    $validator->errors()->add('porcentaje_avance', 'Un proyecto planificado todavia no puede registrar avance.');
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
            'codigo' => 'codigo',
            'oficina_id' => 'oficina',
            'responsable_id' => 'responsable',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'porcentaje_avance' => 'porcentaje de avance',
        ];
    }
}

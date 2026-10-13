<?php

namespace App\Http\Requests;

use App\Models\MovimientoEconomico;
use Illuminate\Validation\Rule;

class MovimientoEconomicoRequest extends SolicitudFormulario
{
    public function authorize(): bool
    {
        $movimiento = $this->route('movimiento');

        return $movimiento instanceof MovimientoEconomico
            ? $this->user()->can('update', $movimiento)
            : $this->user()->can('create', MovimientoEconomico::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(MovimientoEconomico::TIPOS))],
            'categoria' => ['required', 'string', 'max:100'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'concepto' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'proveedor' => ['nullable', 'string', 'max:200'],
            'monto' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'responsable_id' => ['nullable', 'integer', Rule::exists('policias', 'id')],
            'proyecto_id' => ['nullable', 'integer', Rule::exists('proyectos', 'id')],
            'comprobante' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', Rule::in(array_keys(MovimientoEconomico::ESTADOS))],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'responsable_id' => 'responsable',
            'proyecto_id' => 'proyecto',
        ];
    }
}

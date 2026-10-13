<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de los FormRequest del sistema: mensajes de validacion en castellano
 * sin depender del locale de la aplicacion.
 */
abstract class SolicitudFormulario extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'numeric' => 'El campo :attribute debe ser numerico.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'date' => 'El campo :attribute no es una fecha valida.',
            'after_or_equal' => 'El campo :attribute debe ser una fecha igual o posterior a :date.',
            'before_or_equal' => 'El campo :attribute debe ser una fecha igual o anterior a :date.',
            'in' => 'El valor seleccionado en :attribute no es valido.',
            'exists' => 'El valor seleccionado en :attribute no existe.',
            'unique' => 'El valor de :attribute ya esta registrado.',
            'max.string' => 'El campo :attribute no puede superar los :max caracteres.',
            'max.numeric' => 'El campo :attribute no puede ser mayor que :max.',
            'min.numeric' => 'El campo :attribute debe ser como minimo :min.',
            'gt.numeric' => 'El campo :attribute debe ser mayor que :value.',
            'decimal' => 'El campo :attribute debe tener como maximo :decimal decimales.',
        ];
    }
}

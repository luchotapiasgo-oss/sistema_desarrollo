<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Oficina;
use App\Models\Policia;
use Illuminate\Support\Collection;

/**
 * Listas para los <select> de oficinas y policias que comparten los
 * formularios y filtros de varios modulos.
 */
trait OpcionesFormulario
{
    /**
     * @return Collection<int, string>
     */
    protected function opcionesOficinas(): Collection
    {
        return Oficina::orderBy('nombre')->pluck('nombre', 'id');
    }

    /**
     * Policias como "Apellido Nombre (legajo)", ordenados por nombre.
     *
     * @return Collection<int, string>
     */
    protected function opcionesPolicias(): Collection
    {
        return Policia::with('persona')
            ->get()
            ->mapWithKeys(fn (Policia $policia) => [
                $policia->id => trim($policia->nombre_completo.' ('.$policia->numero_legajo.')'),
            ])
            ->sort();
    }
}

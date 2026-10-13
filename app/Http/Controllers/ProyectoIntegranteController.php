<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProyectoIntegranteRequest;
use App\Models\Proyecto;
use App\Models\ProyectoIntegrante;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Integrantes de un proyecto. El listado y el alta viven en la ficha del
 * proyecto (proyectos.show); aqui solo hay edicion de fechas y baja.
 */
class ProyectoIntegranteController extends Controller
{
    use AuthorizesRequests;

    public function store(ProyectoIntegranteRequest $request, Proyecto $proyecto): RedirectResponse
    {
        $proyecto->integrantes()->create($request->validated());

        return redirect()->route('proyectos.show', $proyecto)
            ->with('exito', 'Integrante agregado al proyecto.');
    }

    public function edit(Proyecto $proyecto, ProyectoIntegrante $integrante): View
    {
        $this->authorize('gestionarIntegrantes', $proyecto);

        $integrante->load('policia.persona');

        return view('Proyecto.integrantes.edit', compact('proyecto', 'integrante'));
    }

    public function update(ProyectoIntegranteRequest $request, Proyecto $proyecto, ProyectoIntegrante $integrante): RedirectResponse
    {
        $integrante->update($request->validated());

        return redirect()->route('proyectos.show', $proyecto)
            ->with('exito', 'Integrante actualizado.');
    }

    public function destroy(Proyecto $proyecto, ProyectoIntegrante $integrante): RedirectResponse
    {
        $this->authorize('gestionarIntegrantes', $proyecto);

        $integrante->delete();

        return redirect()->route('proyectos.show', $proyecto)
            ->with('exito', 'Integrante quitado del proyecto.');
    }
}

@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="titulo" label="Título" :value="$incidencia->titulo" required maxlength="200" class="md:col-span-2" />
    <x-form.select name="tipo" label="Tipo" :options="App\Models\IncidenciaInfraestructura::TIPOS" :value="$incidencia->tipo" required />
    <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="$incidencia->oficina_id" placeholder="Sin oficina" />
    <x-form.input type="datetime-local" name="fecha_reporte" label="Fecha de reporte" :value="$incidencia->fecha_reporte?->format('Y-m-d\TH:i')" required />
    <x-form.select name="responsable_id" label="Responsable" :options="$policias" :value="$incidencia->responsable_id" placeholder="Sin asignar" />
    <x-form.select name="prioridad" label="Prioridad" :options="App\Models\IncidenciaInfraestructura::PRIORIDADES" :value="$incidencia->prioridad" required :placeholder="false" />
    <x-form.select name="estado" label="Estado" :options="App\Models\IncidenciaInfraestructura::ESTADOS" :value="$incidencia->estado" required :placeholder="false" />
    <x-form.input type="number" name="costo" label="Costo ($)" :value="$incidencia->costo" min="0" step="0.01" />
    <x-form.input type="datetime-local" name="fecha_resolucion" label="Fecha de resolución" :value="$incidencia->fecha_resolucion?->format('Y-m-d\TH:i')" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$incidencia->descripcion" rows="4" class="md:col-span-2" />
    <x-form.textarea name="observaciones" label="Observaciones" :value="$incidencia->observaciones" class="md:col-span-2" />
</div>

<p class="mt-3 text-xs text-slate-500">
    La fecha de resolución es obligatoria si el estado es «Resuelta» y se borra si la incidencia se reabre.
    Marcarla como resuelta requiere el permiso <code>incidencias.resolver</code>.
</p>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
    <a href="{{ $incidencia->exists ? route('incidencias.show', $incidencia) : route('incidencias.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>

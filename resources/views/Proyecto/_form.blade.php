@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="nombre" label="Nombre" :value="$proyecto->nombre" required maxlength="200" />
    <x-form.input name="codigo" label="Código" :value="$proyecto->codigo" required maxlength="50" placeholder="PRY-2026-001" />
    <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="$proyecto->oficina_id" placeholder="Sin oficina" />
    <x-form.select name="responsable_id" label="Responsable" :options="$policias" :value="$proyecto->responsable_id" placeholder="Sin responsable" />
    <x-form.input type="date" name="fecha_inicio" label="Fecha de inicio" :value="$proyecto->fecha_inicio?->format('Y-m-d')" />
    <x-form.input type="date" name="fecha_fin" label="Fecha de fin" :value="$proyecto->fecha_fin?->format('Y-m-d')" />
    <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Proyecto::PRIORIDADES" :value="$proyecto->prioridad" required :placeholder="false" />
    <x-form.select name="estado" label="Estado" :options="App\Models\Proyecto::ESTADOS" :value="$proyecto->estado" required :placeholder="false" />
    <x-form.input type="number" name="porcentaje_avance" label="Porcentaje de avance" :value="$proyecto->porcentaje_avance" required min="0" max="100" step="0.01" />
    <div class="hidden md:block"></div>
    <x-form.textarea name="descripcion" label="Descripción" :value="$proyecto->descripcion" class="md:col-span-2" />
    <x-form.textarea name="objetivo" label="Objetivo" :value="$proyecto->objetivo" class="md:col-span-2" />
    <x-form.textarea name="observaciones" label="Observaciones" :value="$proyecto->observaciones" class="md:col-span-2" />
</div>

<p class="mt-3 text-xs text-slate-500">Un proyecto planificado no registra avance; uno finalizado debe estar al 100%.</p>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
    <a href="{{ $proyecto->exists ? route('proyectos.show', $proyecto) : route('proyectos.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>

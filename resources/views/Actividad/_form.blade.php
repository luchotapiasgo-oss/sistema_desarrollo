@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="titulo" label="Título" :value="$actividad->titulo" required maxlength="200" class="md:col-span-2" />
    <x-form.select name="oficina_id" label="Oficina" :options="$oficinas" :value="$actividad->oficina_id" required />
    <x-form.input name="tipo" label="Tipo" :value="$actividad->tipo" maxlength="50" placeholder="Operativo, capacitación, reunión..." />
    <x-form.input type="datetime-local" name="fecha_inicio" label="Fecha de inicio" :value="$actividad->fecha_inicio?->format('Y-m-d\TH:i')" required />
    <x-form.input type="datetime-local" name="fecha_fin" label="Fecha de fin" :value="$actividad->fecha_fin?->format('Y-m-d\TH:i')" />
    <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Actividad::PRIORIDADES" :value="$actividad->prioridad" required :placeholder="false" />
    <x-form.select name="estado" label="Estado" :options="App\Models\Actividad::ESTADOS" :value="$actividad->estado" required :placeholder="false" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$actividad->descripcion" rows="4" class="md:col-span-2" />
    <x-form.textarea name="observaciones" label="Observaciones" :value="$actividad->observaciones" class="md:col-span-2" />
</div>

<p class="mt-3 text-xs text-slate-500">Una actividad finalizada debe tener fecha de fin.</p>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
    <a href="{{ $actividad->exists ? route('actividades.show', $actividad) : route('actividades.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>

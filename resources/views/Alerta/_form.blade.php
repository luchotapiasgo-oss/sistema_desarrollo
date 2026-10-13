@csrf

@php($tiposAlertable = collect(App\Models\Alerta::ALERTABLES)->keys()->mapWithKeys(fn ($clave) => [$clave => \Illuminate\Support\Str::headline($clave)]))

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="titulo" label="Título" :value="$alerta->titulo" required maxlength="200" class="md:col-span-2" />
    <x-form.input name="tipo" label="Tipo" :value="$alerta->tipo" required maxlength="100" placeholder="Vencimiento, mantenimiento, seguimiento..." />
    <x-form.select name="usuario_id" label="Destinatario" :options="$usuarios" :value="$alerta->usuario_id" placeholder="Sin destinatario" />
    <x-form.select name="alertable_tipo" label="Registro asociado" :options="$tiposAlertable" :value="$alerta->alertable_clave" required />
    <x-form.input type="number" name="alertable_id" label="ID del registro" :value="$alerta->alertable_id" required min="1" />
    <x-form.input type="datetime-local" name="fecha_generacion" label="Fecha de generación" :value="$alerta->fecha_generacion?->format('Y-m-d\TH:i')" required />
    <x-form.input type="datetime-local" name="fecha_vencimiento" label="Fecha de vencimiento" :value="$alerta->fecha_vencimiento?->format('Y-m-d\TH:i')" />
    <x-form.select name="prioridad" label="Prioridad" :options="App\Models\Alerta::PRIORIDADES" :value="$alerta->prioridad" required :placeholder="false" />
    <x-form.select name="estado" label="Estado" :options="App\Models\Alerta::ESTADOS" :value="$alerta->estado" required :placeholder="false" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$alerta->descripcion" rows="4" class="md:col-span-2" />
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
    <a href="{{ $alerta->exists ? route('alertas.show', $alerta) : route('alertas.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>

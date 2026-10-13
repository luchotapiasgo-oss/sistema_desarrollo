@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.select name="tipo" label="Tipo" :options="App\Models\MovimientoEconomico::TIPOS" :value="$movimiento->tipo" required :placeholder="false" />
    <x-form.input type="date" name="fecha" label="Fecha" :value="$movimiento->fecha?->format('Y-m-d')" required :max="today()->format('Y-m-d')" />
    <x-form.input name="concepto" label="Concepto" :value="$movimiento->concepto" required maxlength="255" class="md:col-span-2" />
    <x-form.input name="categoria" label="Categoría" :value="$movimiento->categoria" required maxlength="100" placeholder="Mantenimiento, insumos, viáticos..." />
    <x-form.input type="number" name="monto" label="Monto ($)" :value="$movimiento->monto" required min="0.01" step="0.01" />
    <x-form.input name="proveedor" label="Proveedor" :value="$movimiento->proveedor" maxlength="200" />
    <x-form.input name="comprobante" label="N° de comprobante" :value="$movimiento->comprobante" maxlength="150" />
    <x-form.select name="proyecto_id" label="Proyecto" :options="$proyectos" :value="$movimiento->proyecto_id" placeholder="Sin proyecto" />
    <x-form.select name="responsable_id" label="Responsable" :options="$policias" :value="$movimiento->responsable_id" placeholder="Sin responsable" />
    <x-form.select name="estado" label="Estado" :options="App\Models\MovimientoEconomico::ESTADOS" :value="$movimiento->estado" required :placeholder="false" />
    <div class="hidden md:block"></div>
    <x-form.textarea name="descripcion" label="Descripción" :value="$movimiento->descripcion" class="md:col-span-2" />
    <x-form.textarea name="observaciones" label="Observaciones" :value="$movimiento->observaciones" class="md:col-span-2" />
</div>

<p class="mt-3 text-xs text-slate-500">Un movimiento anulado ya no se puede editar.</p>

<div class="mt-6 flex gap-3">
    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
    <a href="{{ $movimiento->exists ? route('movimientos.show', $movimiento) : route('movimientos.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
</div>

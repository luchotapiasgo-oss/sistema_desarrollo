@props(['name', 'label', 'options' => [], 'value' => null, 'required' => false, 'placeholder' => 'Seleccione...'])

@php($seleccionado = (string) old($name, $value))

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $label }} @if ($required)<span class="text-red-600">*</span>@endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
            {{ $attributes->except('class')->class([
                'w-full rounded-md border bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-400',
                'border-red-400' => $errors->has($name),
                'border-slate-300' => ! $errors->has($name),
            ]) }}>
        @if ($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $clave => $texto)
            <option value="{{ $clave }}" @selected($seleccionado === (string) $clave)>{{ $texto }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

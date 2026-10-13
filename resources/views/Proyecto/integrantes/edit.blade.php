@extends('layouts.app')

@section('titulo', 'Editar integrante')

@section('contenido')
    <a href="{{ route('proyectos.show', $proyecto) }}" class="mb-4 inline-block text-sm text-slate-500 hover:text-slate-800">&larr; {{ $proyecto->nombre }}</a>

    <form method="POST" action="{{ route('proyectos.integrantes.update', [$proyecto, $integrante]) }}" class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <p class="mb-4 text-sm">
            <span class="text-slate-500">Policía:</span>
            <span class="font-medium">{{ $integrante->policia?->nombre_completo }}</span>
        </p>

        <div class="grid gap-4 md:grid-cols-2">
            <x-form.input type="date" name="fecha_ingreso" label="Fecha de ingreso" :value="$integrante->fecha_ingreso?->format('Y-m-d')" />
            <x-form.input type="date" name="fecha_salida" label="Fecha de salida" :value="$integrante->fecha_salida?->format('Y-m-d')" />
            <x-form.textarea name="observaciones" label="Observaciones" :value="$integrante->observaciones" class="md:col-span-2" />
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
            <a href="{{ route('proyectos.show', $proyecto) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
@endsection

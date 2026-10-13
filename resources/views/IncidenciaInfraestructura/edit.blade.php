@extends('layouts.app')

@section('titulo', 'Editar incidencia')

@section('contenido')
    <form method="POST" action="{{ route('incidencias.update', $incidencia) }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @method('PUT')
        @include('IncidenciaInfraestructura._form')
    </form>
@endsection

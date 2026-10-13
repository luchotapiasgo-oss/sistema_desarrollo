@extends('layouts.app')

@section('titulo', 'Reportar incidencia')

@section('contenido')
    <form method="POST" action="{{ route('incidencias.store') }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @include('IncidenciaInfraestructura._form')
    </form>
@endsection

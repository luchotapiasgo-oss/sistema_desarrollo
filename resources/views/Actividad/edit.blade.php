@extends('layouts.app')

@section('titulo', 'Editar actividad')

@section('contenido')
    <form method="POST" action="{{ route('actividades.update', $actividad) }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @method('PUT')
        @include('Actividad._form')
    </form>
@endsection

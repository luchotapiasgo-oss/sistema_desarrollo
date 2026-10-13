@extends('layouts.app')

@section('titulo', 'Editar proyecto')

@section('contenido')
    <form method="POST" action="{{ route('proyectos.update', $proyecto) }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @method('PUT')
        @include('Proyecto._form')
    </form>
@endsection

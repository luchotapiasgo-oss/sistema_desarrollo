@extends('layouts.app')

@section('titulo', 'Nuevo proyecto')

@section('contenido')
    <form method="POST" action="{{ route('proyectos.store') }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @include('Proyecto._form')
    </form>
@endsection

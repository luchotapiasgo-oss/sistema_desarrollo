@extends('layouts.app')

@section('titulo', 'Nueva actividad')

@section('contenido')
    <form method="POST" action="{{ route('actividades.store') }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @include('Actividad._form')
    </form>
@endsection

@extends('layouts.app')

@section('titulo', 'Editar alerta')

@section('contenido')
    <form method="POST" action="{{ route('alertas.update', $alerta) }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @method('PUT')
        @include('Alerta._form')
    </form>
@endsection

@extends('layouts.app')

@section('titulo', 'Editar movimiento económico')

@section('contenido')
    <form method="POST" action="{{ route('movimientos.update', $movimiento) }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @method('PUT')
        @include('MovimientoEconomico._form')
    </form>
@endsection

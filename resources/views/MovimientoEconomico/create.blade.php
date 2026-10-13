@extends('layouts.app')

@section('titulo', 'Nuevo movimiento económico')

@section('contenido')
    <form method="POST" action="{{ route('movimientos.store') }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @include('MovimientoEconomico._form')
    </form>
@endsection

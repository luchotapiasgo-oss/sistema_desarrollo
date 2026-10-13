@extends('layouts.app')

@section('titulo', 'Nueva alerta')

@section('contenido')
    <form method="POST" action="{{ route('alertas.store') }}" class="max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        @include('Alerta._form')
    </form>
@endsection

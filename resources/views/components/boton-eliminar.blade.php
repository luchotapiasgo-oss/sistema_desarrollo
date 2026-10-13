@props(['accion', 'mensaje' => '¿Confirma la eliminación? Esta acción no se puede deshacer.', 'texto' => 'Eliminar'])

<form method="POST" action="{{ $accion }}" class="inline" onsubmit="return confirm(@js($mensaje))">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class('text-sm font-medium text-red-600 hover:text-red-800') }}>{{ $texto }}</button>
</form>

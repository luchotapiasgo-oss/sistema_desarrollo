<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Prioridades compartidas por actividades, proyectos, incidencias y alertas.
 * La columna `prioridad` es un varchar libre: los valores validos viven aqui.
 */
trait TienePrioridad
{
    /**
     * @var array<string, string>
     */
    const PRIORIDADES = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
        'critica' => 'Critica',
    ];

    public function scopeDePrioridad(Builder $query, string $prioridad): Builder
    {
        return $query->where('prioridad', $prioridad);
    }

    public function getPrioridadEtiquetaAttribute(): string
    {
        return self::PRIORIDADES[$this->prioridad] ?? (string) $this->prioridad;
    }
}

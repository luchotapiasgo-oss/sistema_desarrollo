<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TienePrioridad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IncidenciaInfraestructura extends Model
{
    use RegistraAuditoria, TienePrioridad;

    protected $table = 'incidencias_infraestructura';

    /**
     * @var array<string, string>
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En proceso',
        'resuelta' => 'Resuelta',
        'cancelada' => 'Cancelada',
    ];

    /**
     * @var array<string, string>
     */
    const TIPOS = [
        'electrica' => 'Electrica',
        'sanitaria' => 'Sanitaria',
        'edilicia' => 'Edilicia',
        'climatizacion' => 'Climatizacion',
        'redes' => 'Redes y comunicaciones',
        'seguridad' => 'Seguridad',
        'otra' => 'Otra',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'oficina_id',
        'tipo',
        'titulo',
        'descripcion',
        'fecha_reporte',
        'prioridad',
        'responsable_id',
        'estado',
        'costo',
        'fecha_resolucion',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_reporte' => 'datetime',
            'fecha_resolucion' => 'datetime',
            'costo' => 'decimal:2',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeResueltas(Builder $query): Builder
    {
        return $query->where('estado', 'resuelta');
    }
}

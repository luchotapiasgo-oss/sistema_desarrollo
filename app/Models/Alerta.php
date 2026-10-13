<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TienePrioridad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alerta extends Model
{
    use RegistraAuditoria, TienePrioridad;

    protected $table = 'alertas';

    /**
     * @var array<string, string>
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'leida' => 'Leida',
        'atendida' => 'Atendida',
    ];

    /**
     * Registros a los que se puede asociar una alerta. La clave es la que
     * viaja en el formulario; el valor es lo que se guarda en alertable_tipo.
     *
     * @var array<string, class-string<Model>>
     */
    const ALERTABLES = [
        'actividad' => Actividad::class,
        'proyecto' => Proyecto::class,
        'incidencia' => IncidenciaInfraestructura::class,
        'policia' => Policia::class,
        'vehiculo' => Vehiculo::class,
        'drone_robot' => DroneRobot::class,
        'equipamiento' => EquipamientoTecnologico::class,
        'inventario' => Inventario::class,
        'oficina' => Oficina::class,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'alertable_id',
        'alertable_tipo',
        'usuario_id',
        'prioridad',
        'fecha_generacion',
        'fecha_vencimiento',
        'estado',
        'fecha_lectura',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_generacion' => 'datetime',
            'fecha_vencimiento' => 'datetime',
            'fecha_lectura' => 'datetime',
        ];
    }

    /**
     * Registro que origina la alerta (relacion polimorfica):
     * Vehiculo, DroneRobot, EquipamientoTecnologico, IncidenciaInfraestructura...
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo('alertable', 'alertable_tipo', 'alertable_id');
    }

    /**
     * Usuario destinatario de la alerta.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now())
            ->where('estado', '!=', 'atendida');
    }

    public function scopeDeUsuario(Builder $query, int $usuarioId): Builder
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Clave de ALERTABLES correspondiente a alertable_tipo.
     */
    public function getAlertableClaveAttribute(): ?string
    {
        $clave = array_search($this->alertable_tipo, self::ALERTABLES, true);

        return $clave === false ? null : $clave;
    }

    /**
     * Texto legible del registro que origina la alerta.
     */
    public function getAlertableEtiquetaAttribute(): string
    {
        $registro = $this->alertable;
        $tipo = $this->alertable_clave ?? class_basename($this->alertable_tipo);

        if ($registro === null) {
            return $tipo.' #'.$this->alertable_id.' (eliminado)';
        }

        foreach (['titulo', 'nombre', 'dominio', 'codigo', 'nombre_completo'] as $campo) {
            if (filled($registro->{$campo})) {
                return $tipo.': '.$registro->{$campo};
            }
        }

        return $tipo.' #'.$registro->getKey();
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->isPast()
            && $this->estado !== 'atendida';
    }

    public function marcarLeida(): void
    {
        if ($this->estado === 'pendiente') {
            $this->update(['estado' => 'leida', 'fecha_lectura' => now()]);
        }
    }

    public function marcarAtendida(): void
    {
        $this->update([
            'estado' => 'atendida',
            'fecha_lectura' => $this->fecha_lectura ?? now(),
        ]);
    }
}

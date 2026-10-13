<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoEconomico extends Model
{
    use RegistraAuditoria;

    protected $table = 'movimientos_economicos';

    /**
     * @var array<string, string>
     */
    const TIPOS = [
        'ingreso' => 'Ingreso',
        'egreso' => 'Egreso',
    ];

    /**
     * @var array<string, string>
     */
    const ESTADOS = [
        'registrado' => 'Registrado',
        'aprobado' => 'Aprobado',
        'anulado' => 'Anulado',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo',
        'categoria',
        'fecha',
        'concepto',
        'descripcion',
        'proveedor',
        'monto',
        'responsable_id',
        'proyecto_id',
        'comprobante',
        'estado',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }
}

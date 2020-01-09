<?php

namespace Raffles\Modules\Poga\Models;

use Raffles\Models\Document;
use Raffles\Modules\Poga\Filters\RentaFilters;
use Raffles\Modules\Poga\Models\Traits\RentaTrait;
use Raffles\Modules\Poga\Sorters\RentaSorters;

use Illuminate\Database\Eloquent\Model;
use RafflesArgentina\FilterableSortable\FilterableSortableTrait;

class Renta extends Model
{
    use FilterableSortableTrait, RentaTrait;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'moneda',    
        'nombre_y_apellidos_inquilino_referente',
        'persona_id_inquilino_referente',
    ];

    protected $casts = [
        'fecha_fin' => 'date',
        'fecha_inicio' => 'date',
        'multa' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'fecha_fin', 'fecha_inicio', 'fecha_finalizacion_contrato',
    ];

    /**
     * The associated query filters.
     *
     * @var RentaFilters
     */
    protected $filters = RentaFilters::class;

    /**
     * The associated query sorters.
     *
     * @var RentaSorters
     */
    protected $sorters = RentaSorters::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comision_administrador',
        'comision_inmobiliaria',
        'dia_mes_pago',
        'dias_multa',
        'dias_notificacion_previa_renovacion',
        'enum_estado',
        'expensas',
        'fecha_fin',
        'fecha_finalizacion_contrato',
        'fecha_inicio',
        'garantia',
        'id_inmueble',
        'id_inquilino',
        'id_moneda',
        'id_renta_padre',
        'monto',
        'monto_descontado_garantia_finalizacion_contrato',
        'monto_multa_dia',
        'motivo_descuento_garantia',
        'multa',
        'observacion',
        'prim_comision_administrador',
        'renovacion',
        'vigente',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rentas';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['idInmueble', 'idInquilino'];

    /**
     * Get the documentos for the inmueble.
     */
    public function documentos()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the administradores for the renta.
     */
    public function administradores()
    {
        return $this->hasManyThrough(Inmueble::class, InmueblePersona::class, 'id_inmueble')->where('inmueble_persona.enum_estado', 'ACTIVO')->where('inmueble_persona.enum_rol', 'ADMINISTRADOR');
    }

    /**
     * The estados inmueble that belong to the renta.
     */
    public function estadosInmueble()
    {
        return $this->belongsToMany(EstadoInmueble::class, 'estado_inmueble_renta', 'id_renta', 'id_estado_inmueble')
            ->withPivot(['cantidad', 'enum_estado', 'reparar']);
    }

    /**
     * Get the inmueble that owns the renta.
     */
    public function idInmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    /**
     * Get the inquilino that owns the renta.
     */
    public function idInquilino()
    {
        return $this->belongsTo(Persona::class, 'id_inquilino');
    }

    /**
     * Get the moneda that owns the renta.
     */
    public function idMoneda()
    {
        return $this->belongsTo(Moneda::class, 'id_moneda');
    }

    /**
     * Get the renta padre that owns the renta.
     */
    public function idRentaPadre()
    {
        return $this->belongsTo(Renta::class, 'id_renta_padre');
    }

    /**
     * Get the unidad that owns the renta.
     */
    public function idUnidad()
    {
        return $this->belongsTo(Unidad::class, 'id_inmueble', 'id_inmueble');
    }

    /**
     * Get the multas for the renta.
     */
    public function multas()
    {
        return $this->hasMany(MultaRenta::class, 'id_renta');
    }
}

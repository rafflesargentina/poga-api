<?php

namespace Raffles\Modules\Poga\Models;

use Raffles\Modules\Poga\Filters\InmueblePadreFilters;
use Raffles\Modules\Poga\Sorters\InmueblePadreSorters;

use Illuminate\Database\Eloquent\Model;
use RafflesArgentina\FilterableSortable\FilterableSortableTrait;

class InmueblePadre extends Model
{
    use FilterableSortableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cant_pisos',
        'comision_administrador',
        'divisible_en_unidades',
        'id_inmueble',
        'id_direccion',
        'modalidad_propiedad',
        'monto_fondo_reserva',
        'nombre',
    ];

    protected $filters = InmueblePadreFilters::class;

    protected $sorters = InmueblePadreSorters::class;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inmuebles_padre';

    protected $with = ['idDireccion'];

    /**
     * Get the direccion that owns the inmueble padre.
     */
    public function idDireccion()
    {
        return $this->belongsTo(Direccion::class, 'id_direccion');
    }

    /**
     * Get the inmueble that owns the inmueble padre.
     */
    public function idInmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    /**
     * Get the eventos for the inmueble padre.
     */
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'id_inmueble_padre');
    }

    /**
     * Get the unidades for the inmueble padre.
     */
    public function unidades()
    {
        return $this->hasMany(Unidad::class, 'id_inmueble_padre');
    }

    public function rentas()
    {
        return $this->idInmueble->rentas();
    }

    /**
     * Get the nombre.
     *
     * @return string
     */
    public function getNombreAttribute($value)
    {
        if (!$value) {
		return $this->idDireccion->calle_principal.' '.($this->idDireccion->calle_secundaria ? ('c/'.$this->idDireccion->calle_secundaria.' '.$this->idDireccion->numeracion) : $this->idDireccion->numeracion);
        }

        return $value;
    }
}

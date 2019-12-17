<?php

namespace Raffles\Modules\Poga\Models;

use Raffles\Modules\Poga\Filters\CaracteristicaTipoInmuebleFilters;
use Raffles\Modules\Poga\Sorters\CaracteristicaTipoInmuebleSorters;

use Illuminate\Database\Eloquent\Relations\Pivot;
use RafflesArgentina\FilterableSortable\FilterableSortableTrait;

class CaracteristicaTipoInmueble extends Pivot
{
    use FilterableSortableTrait;

    /**
     * The table associated with the pivot.
     *
     * @var string
     */
    protected $table = 'caracteristica_tipo_inmueble';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = 'idCaracteristica';

    protected $filters = CaracteristicaTipoInmuebleFilters::class;

    protected $sorters = CaracteristicaTipoInmuebleSorters::class;

    /**
     * Get the caracteristica than owns the CaracteristicaTipoInmueble.
     */
    public function idCaracteristica()
    {
        return $this->belongsTo(Caracteristica::class, 'id_caracteristica');
    }


    /**
     * Get the grupo caracteristica than owns the CaracteristicaTipoInmueble.
     */
    public function idGrupoCaracteristica()
    {
        return $this->belongsTo(Caracteristica::class, 'id_caracteristica');
    }
}

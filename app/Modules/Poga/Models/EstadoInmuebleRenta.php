<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EstadoInmuebleRenta extends Pivot
{
    /**
     * The table associated with the pivot.
     *
     * @var string
     */
    protected $table = 'estado_inmueble_renta';
}

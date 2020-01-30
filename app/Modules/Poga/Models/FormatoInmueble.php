<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Model;
use RafflesArgentina\FilterableSortable\FilterableSortableTrait;

class FormatoInmueble extends Model
{
    /**
     * The table associated with the pivot.
     *
     * @var string
     */
    protected $table = 'formato_inmueble';

    protected $with = 'idFormato';

    /**
     * Get the formato that owns the formato inmueble.
     */
    public function idFormato()
    {
        return $this->belongsTo(Formato::class, 'id_formato');
    }
}

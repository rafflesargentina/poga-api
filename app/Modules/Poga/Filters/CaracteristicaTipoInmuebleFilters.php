<?php

namespace Raffles\Modules\Poga\Filters;

use RafflesArgentina\FilterableSortable\BaseFilters;

class CaracteristicaTipoInmuebleFilters extends BaseFilters
{
    /**
     * enum_estado.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function enum_estado($query)
    {
        return $this->builder->where('caracteristica_tipo_inmueble.enum_estado', $query);
    }

    /**
     * id_tipo_inmueble.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function id_tipo_inmueble($query)
    {
        return $this->builder->where('id_tipo_inmueble', $query);
    }
}

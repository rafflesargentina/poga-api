<?php

namespace Raffles\Modules\Poga\Filters;

use Carbon\Carbon;
use RafflesArgentina\FilterableSortable\BaseFilters;

class PagareFilters extends BaseFilters
{
    /**
     * enum_estado
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function enum_estado($query)
    {
        return $this->builder->where('pagares.enum_estado', $query);
    }

    /**
     * enum_clasificacion_pagare.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function enum_clasificacion_pagare($query)
    {
        return $this->builder->where('enum_clasificacion_pagare', $query);
    }

    /**
     * fecha_pagare_desde
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function fecha_pagare_desde($query)
    {
        return $this->builder->where('fecha_pagare', '>=', $query);
    }

    /**
     * fecha_pagare_hasta
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function fecha_pagare_hasta($query)
    {
        return $this->builder->where('fecha_pagare', '<=', $query);
    }

    /**
     * id_inmueble.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function id_inmueble($query)
    {
        return $this->builder->where('id_inmueble', $query);
    }
}

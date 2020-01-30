<?php

namespace Raffles\Modules\Poga\Filters;

use RafflesArgentina\FilterableSortable\BaseFilters;

class PersonaFilters extends BaseFilters
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
        return $this->builder->where('personas.enum_estado', $query);
    }

    /**
     * enum_rol
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function enum_rol($query)
    {
        return $this->builder->whereHas(
            'inmuebles', function ($q) use($query) {
                $q->where('enum_rol', $query);
            }
        );
    }

    /**
     * excluir.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function excluir($query)
    {
        switch ($query) {
        case 'con_renta':
            return $this->builder
                ->whereDoesntHave(
                    'inmuebles.rentas', function ($query) {
                        return $query->where('enum_estado', 'ACTIVO');
                    }
                );
        break;
        case 'sin_renta':
            return $this->builder
                ->whereHas(
                    'inmuebles.rentas', function ($query) {
                        return $query->where('enum_estado', 'ACTIVO');
                    }
                );
        break;
        }
    }


    /**
     * id_inmueble
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function id_inmueble($query) {
        return $this->builder->whereHas('inmuebles', function($q) use($query) {
            $q->where('inmuebles.enum_estado', 'ACTIVO');
            $q->where('id_inmueble', $query);
        });
    }
}

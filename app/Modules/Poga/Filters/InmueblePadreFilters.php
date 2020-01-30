<?php

namespace Raffles\Modules\Poga\Filters;

use RafflesArgentina\FilterableSortable\BaseFilters;

class InmueblePadreFilters extends BaseFilters
{
    /**
     * direccion.
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function direccion($query)
    {
        $q = explode(',', $query ?: ',,,');
        return $this->builder->whereHas(
            'idDireccion', function ($direccion) use ($q) {
                $direccion->where('calle_principal', $q[0]);
        
                if ($q[1]) {
                    $direccion->whereBetween('numeracion', [(intval($q[1]) - 100), (intval($q[1]) + 100)]);
                } elseif($q[2]) {
                    $direccion->where('calle_secundaria', $q[2]);
                } 
            }
        );
    }

    /**
     * enum_estado
     *
     * @param mixed $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function enum_estado($query)
    {
        return $this->builder->where('inmuebles_padre.enum_estado', $query);
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
                ->with(
                    ['unidades' => function ($query) {
                            return $query->whereDoesntHave(
                                'idInmueble.rentas', function ($query) {
                                               return $query->where('enum_estado', 'ACTIVO'); 
                                }
                            ); 
                    }]
                )
                ->whereDoesntHave(
                    'idInmueble.rentas', function ($query) {
                        return $query->where('enum_estado', 'ACTIVO'); 
                    }
                );
        break;
        case 'sin_renta':
            return $this->builder
                ->with(
                    ['unidades' => function ($query) {
                            return $query->whereHas(
                                'idInmueble.rentas', function ($query) {
                                               return $query->where('enum_estado', 'ACTIVO'); 
                                }
                            ); 
                    }]
                )
                ->whereHas(
                    'idInmueble.rentas', function ($query) {
                        return $query->where('enum_estado', 'ACTIVO'); 
                    }
                );
        break;
        }
    }
}

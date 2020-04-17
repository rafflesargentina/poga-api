<?php

namespace Raffles\Modules\Poga\Models\Traits;

use Carbon\Carbon;

trait RentaTrait
{
    /**
     * Get the moneda.
     *
     * @return string
     */
    public function getMonedaAttribute()
    {
        $moneda = $this->idMoneda;

        if (!$moneda) {
            return null;
        }

        return $moneda->moneda;
    }

    /**
     * Get the nombre y apellidos for the inquilino referente.
     *
     * @return string
     */
    public function getNombreYApellidosInquilinoReferenteAttribute()
    {
        $inquilino = $this->idInquilino;

        if (!$inquilino) {
            return null;
        }

        return $inquilino->nombre.' '.$inquilino->apellidos;
    }

    /**
     * Get the inquilino referente's id.
     *
     * @return string
     */
    public function getPersonaIdInquilinoReferenteAttribute()
    {
        return $this->id_inquilino;
    }

    public function setFechaFinAttribute($value)
    {
        $this->attributes['fecha_fin'] = Carbon::parse($value);
    }

    public function setFechaInicioAttribute($value)
    {
        $this->attributes['fecha_inicio'] = Carbon::parse($value);
    }
}

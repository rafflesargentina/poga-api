<?php

namespace Raffles\Modules\Poga\Models\Traits;

trait PagareTrait
{
    /**
     * Get the formatted option for enum_clasificacion_pagare.
     *
     * @return string
     */
    public function getClasificacionAttribute()
    {
        switch ($this->enum_clasificacion_pagare) {
        case 'COMISION_INMOBILIARIA':
            $clasificacion = 'Comisión Inmobiliaria';
        break;
        case 'COMISION_POGA':
            $clasificacion = 'Comisión Poga (5.5%)';
        break;
        case 'COMISION_RENTA_ADMIN':
            $clasificacion = 'Comisión Renta Administrador';
        break;
        case 'COMISION_RENTA_PRIM_ADMIN':
            $clasificacion = 'Comisión. primer mes Renta Administrador';
        break;
        case 'DEPOSITO_GARANTIA':
            $clasificacion = 'Depósito de Garantía';
        break;
        case 'EXPENSA':
            $clasificacion = 'Expensa';
        break;
        case 'MULTA_RENTA':
            $clasificacion = 'Multa por renta atrasada';
        break;
        case 'OTRO':
            $clasificacion = 'Solicitud de Pago';
        break;
        case 'RENTA':
            $clasificacion = 'Renta';
        break;
        case 'SALARIO_ADMINISTRADOR':
            $clasificacion = 'Salario del Administrador';
        break;
        case 'SALARIO_CONSERJE':
            $clasificacion = 'Salario del Conserje';
        default:
            $clasificacion = $this->enum_clasificacion_pagare;
        }

        return $this->attributes['clasificacion'] = $clasificacion;
    }

    /**
     * Get the formatted option for enum_estado.
     *
     * @return string
     */
    public function getEstadoAttribute()
    {
        switch ($this->enum_estado) {
        case 'A_CONFIRMAR_POR_ADMIN':
            $estado = 'A confirmar';
	break;
	case 'ANULADO':
            $estado = 'Anulado';
        break;
        case 'PAGADO':
            $estado = 'Pagado';
        break;
        case 'PENDIENTE':
            $estado = 'Pendiente';
	default:
	    $estado = $this->enum_estado;
	}

	return $this->attributes['estado'] = $estado;
    }
}

<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Models\CaracteristicaTipoInmueble;

use Caffeinated\Repository\Repositories\EloquentRepository;

class CaracteristicaTipoInmuebleRepository extends EloquentRepository
{
    /**
     * @var Model
     */
    public $model = CaracteristicaTipoInmueble::class;

    /**
     * @var array
     */
    public $tag = ['CaracteristicaTipoInmueble'];
}

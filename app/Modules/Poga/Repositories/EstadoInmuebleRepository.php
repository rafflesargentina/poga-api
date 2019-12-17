<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Models\EstadoInmueble;

use Caffeinated\Repository\Repositories\EloquentRepository;

class EstadoInmuebleRepository extends EloquentRepository
{
    /**
     * @var Model
     */
    public $model = EstadoInmueble::class;

    /**
     * @var array
     */
    public $tag = ['EstadoInmueble'];
}

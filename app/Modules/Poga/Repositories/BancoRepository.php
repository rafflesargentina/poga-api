<?php

namespace Raffles\Modules\Poga\Repositories;

use Raffles\Modules\Poga\Models\Banco;

use Caffeinated\Repository\Repositories\EloquentRepository;

class BancoRepository extends EloquentRepository
{
    /**
     * @var Model
     */
    public $model = Banco::class;

    /**
     * @var array
     */
    public $tag = ['Banco'];
}

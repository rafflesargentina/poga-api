<?php

namespace Raffles\Modules\Poga\Http\Controllers\Cuenta;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\InmueblePadreRepository;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class MisInmueblesController extends Controller
{
    use FormatsValidJsonResponses;

    protected $repository;

    public function __construct(InmueblePadreRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the account for the authenticated user.
     *
     * @param Request $request The request object.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $user = $request->user('api');

        abort_if(!$user, 401);

        $items = $this->repository->with('unidades')->where('id_usuario_creador', $user->id)->where('enum_estado', 'ACTIVO')->get();

        return $this->validSuccessJsonResponse('Success', $items);
    }
}

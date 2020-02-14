<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Raffles\Modules\Poga\UseCases\AnularPagare;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class AnularPagareController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * The PagareRepository object.
     *
     * @var PagareRepository
     */
    protected $repository;

    /**
     * Create a new AnularPagareRentaController instance.
     *
     * @param  PagareRepository $repository
     *
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        $this->middleware('auth:api');
    
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request The request object.
     * @param int     $id      The Pagare id.
     *
     * @return \Illuminate\Http\JsonResponse
     */    
    public function __invoke(Request $request, $id)
    {
        $model = $this->repository->find($id);

        if (!$model) {
            return $this->validNotFoundJsonResponse();
	}

	if ($model->enum_estado !== 'PENDIENTE') {
            $errors = new MessageBag;
	    $errors->add('enum_estado', 'Sólo puede anularse pagares con estado pendiente.');
            return $this->validUnprocessableEntityJsonResponse($errors, $errors->first());
	}

        $response = $this->dispatch(new AnularPagare($model));

        return $this->validSuccessJsonResponse('Success', $response);
    }
}

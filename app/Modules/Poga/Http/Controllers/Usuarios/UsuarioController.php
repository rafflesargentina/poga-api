<?php

namespace Raffles\Modules\Poga\Http\Controllers\Usuarios;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\UserRepository;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class UsuarioController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * The UserRepository object.
     *
     * @var UserRepository $persona
     */
    protected $repository;

    /**
     * Create a new UsuarioController instance.
     *
     * @param UserRepository $repository The UserRepository object.
     *
     * @return void
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate(
            [
            'enum_rol' => 'required',
            'apellido' => 'required_if:enum_tipo_persona,FISICA',
            'ci' => 'required_if:enum_tipo_persona,FISICA',
            'cuenta_bancaria' => 'required_if:enum_rol,4',
            'direccion_facturacion' => 'required_if:enum_rol,4',
            'enum_tipo_persona' => 'required',
            'fecha_nacimiento' => 'nullable|date',
            'id_banco' => 'required_if:enum_rol,4',
            'id_pais' => 'required',
            'mail' => 'email|unique:users,email',
	    'nombre' => 'required',
	    'razon_social' => 'required_if:enum_rol,4',
            'ruc' => 'required_if:enum_tipo_persona,JURIDICA',
	    'ruc_facturacion' => 'required_if:enum_rol,4',
	    'telefono' => 'nullable|phone:PY',
            'telefono_celular' => 'required|phone:PY',
            'titular_cuenta' => 'required_if:enum_rol,4',
            ]
        );

        $data = $request->all();
        $user = $request->user('api');
        $inmuebleUsuario = $this->dispatchNow(new CrearUsuario($data, $user));

        return $this->validSuccessJsonResponse('Success', $inmuebleUsuario);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $model = $this->repository->find($id);

        $model->loadMissing('idPersona');

	if (!$model) {
            abort(404);
	}

	return $this->validSuccessJsonResponse('Success', $model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        //
    }
}

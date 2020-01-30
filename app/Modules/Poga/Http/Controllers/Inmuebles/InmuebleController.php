<?php

namespace Raffles\Modules\Poga\Http\Controllers\Inmuebles;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\Http\Requests\InmuebleRequest;
use Raffles\Modules\Poga\Models\Inmueble;
use Raffles\Modules\Poga\Repositories\{ InmueblePadreRepository, InmueblePersonaRepository, NominacionRepository };
use Raffles\Modules\Poga\UseCases\{ ActualizarInmueble, BorrarInmueble, CrearInmueble };

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RafflesArgentina\ResourceController\Traits\{ FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations };

class InmuebleController extends Controller
{
    use FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations;

    /**
     * The InmueblePadreRepository object.
     *
     * @var InmueblePadreRepository
     */
    protected $repository;

    /**
     * Create a new InmuebleController instance.
     *
     * @param InmueblePadreRepository $repository The InmueblePadreRepository object.
     *
     * @return void
     */
    public function __construct(InmueblePadreRepository $repository)
    {
        $this->middleware('auth:api')->except('show');

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
        $this->authorize('view', new Inmueble);

        $request->validate(
            [
            'tipoListado' => 'required|in:DisponiblesAdministrar,MisInmuebles,Nominaciones,TodosInmuebles',
            'rol' => 'required_if:tipo_listado,Nominaciones'
            ]
        );

        $user = $request->user('api');

        switch ($request->tipoListado) {
        case 'DisponiblesAdministrar':
            $map = $this->repository->findDisponiblesAdministrar();

            break;

        case 'MisInmuebles':
            $map = $this->repository->misInmuebles($request);
                
            break;

        case 'Nominaciones':
            $repository = new NominacionRepository;

            $map = $repository->dondeFuiNominado($user->id_persona, $user->role_id);

            break;

        case 'TodosInmuebles':
            $map = $this->repository->findTodos();

            break;
        }

        return $this->validSuccessJsonResponse('Success', $map);
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
        $user = $request->user('api');

        //$this->authorize('create', new Inmueble);

        if ($request->administrador === 'yo') {
            $request->merge(['id_administrador_referente' => $user->id_persona]);
        }

        if ($request->id_inmueble['id_tipo_inmueble'] !== '1') {
            $request->merge(['divisible_en_unidades' => 0]);
            $request->request->remove('unidades');
        }

        $request->validate(
            [
            'administrador' => 'sometimes|required|in:yo,otra_persona',
            'cant_pisos' => 'required|numeric',
            'comision_administrador' => Rule::requiredIf(
                function () use ($request, $user) {
                    // Requerido cuando la modalidad de la propiedad es UNICO_PROPIETARIO,
                    // y administrador es "yo" o id_administrador_referente es igual a id_persona del usuario.
                    return $request->modalidad_propiedad === 'UNICO_PROPIETARIO'
                    && ($request->administrador === 'yo' || $request->id_administrador_referente == $user->id_persona);
                }
            ),        
            'dia_cobro_mensual' => Rule::requiredIf(
                function () use ($request, $user) {
                    // Requerido cuando la modalidad de la propiedad es EN_CONDOMINIO,
                    // y administrador es "yo" o id_administrador_referente es igual a id_persona del usuario.
                    return $request->modalidad_propiedad === 'EN_CONDOMINIO'
                    && ($request->administrador === 'yo' || $request->id_administrador_referente == $user->id_persona);
                }
            ),        
            'divisible_en_unidades' => 'required_if:id_inmueble.id_tipo_inmueble,1',            
            'formatos' => 'required|array|min:1',
            'id_direccion.calle_principal' => 'required',
            'id_direccion.latitud' => 'sometimes|required',
            'id_direccion.longitud' => 'sometimes|required',
            'id_inmueble.id_tipo_inmueble' => 'required',
            'id_inmueble.solicitud_directa_inquilinos' => 'required',
            'modalidad_propiedad' => 'required_if:id_inmueble.id_tipo_inmueble,1',
            //'nombre' => 'required',
            'id_propietario_referente' => Rule::requiredIf(
                function () use ($request, $user) {
                    // Requerido cuando la modalidad de la propiedad es UNICO_PROPIETARIO
                    // y el tipo de inmueble es distinto de Edificio.
                    return $request->modalidad_propiedad === 'UNICO_PROPIETARIO'
                    && $request->id_inmueble['id_tipo_inmueble'] != '1';
                }
            ),
            'salario' => Rule::requiredIf(
                function () use ($request, $user) {
                    // Requerido cuando la modalidad de la propiedad es EN_CONDOMINIO,
                    // y administrador es "yo" o id_administrador_referente es igual a id_persona del usuario.
                    return $request->modalidad_propiedad === 'EN_CONDOMINIO'
                    && ($request->administrador === 'yo' || $request->id_administrador_referente == $user->id_persona);
                }
            )
            ]
        );

        $data = $request->all();
        $inmueblePadre = $this->dispatchNow(new CrearInmueble($data, $user));

        return $this->validSuccessJsonResponse('Success', $inmueblePadre);
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
        $model = $this->repository->findOrFail($id);
        $model->loadMissing('idInmueble.caracteristicas', 'idInmueble.documentos', 'idInmueble.featured_photo', 'idInmueble.formatos', 'idInmueble.idUsuarioCreador', 'idInmueble.unfeatured_photos');

        return $this->validSuccessJsonResponse('Success', $model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param InmuebleRequest $request
     * @param int             $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $model = $this->repository->findOrFail($id);

        if ($request->id_inmueble['id_tipo_inmueble'] !== '1') {
            $request->merge(['divisible_en_unidades' => 0]);
            $request->request->remove('unidades');
        }

        $inmueble = $model->idInmueble;
        $user = $request->user('api');
        //$this->authorize('update', $model->idInmueble);

        // Handle documentos.
        if ($request->documentos) {
            $request->validate(
                [
                'documentos[]' => 'file',
                ]
            );

            $mergedRequest = $this->uploadFiles($request, $inmueble);
            $this->updateOrCreateRelations($mergedRequest, $inmueble);

            return $this->validSuccessJsonResponse('Success');
	}


        // Handle featured photos.
        if ($request->featured_photo) {
            $request->validate(
                [
                'featured_photo[]' => 'image'
                ]
            );

            $mergedRequest = $this->uploadFiles($request, $inmueble);
            $this->updateOrCreateRelations($mergedRequest, $inmueble);

            return $this->validSuccessJsonResponse('Success');
        }

        // Handle photos.
        if ($request->unfeatured_photos) {
            $request->validate(
                [
                'unfeatured_photos[]' => 'image'
                ]
            );

            $mergedRequest = $this->uploadFiles($request, $inmueble);
            $this->updateOrCreateRelations($mergedRequest, $inmueble);

            return $this->validSuccessJsonResponse('Success');
        }


        $data = $request->all();

        $inmueblePadre = $this->dispatchNow(new ActualizarInmueble($model, $data, $user));

        return $this->validSuccessJsonResponse('Success', $inmueblePadre);
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
        $inmueblePadre = $this->repository->findOrFail($id);

        $model = $inmueblePadre->idInmueble;

        $this->authorize('delete', $model);

        $user = $request->user('api');
        $inmueble = $this->dispatchNow(new BorrarInmueble($model, $user));

        return $this->validSuccessJsonResponse('Success', $inmueble);
    }
}

<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Direccion, Inmueble, InmueblePadre, User };
use Raffles\Modules\Poga\Repositories\{ DireccionRepository, InmuebleRepository, InmueblePadreRepository, PersonaRepository };
use Raffles\Modules\Poga\Notifications\InmuebleCreado;

use Illuminate\Foundation\Bus\DispatchesJobs;

class CrearInmueble
{
    use DispatchesJobs;

    /**
     * The form data and the User model.
     *
     * @var array
     * @var User
     */
    protected $data, $user;

    /**
     * Create a new job instance.
     *
     * @param array $data The form data.
     * @param User  $user The User model.
     *
     * @return void
     */
    public function __construct($data, User $user)
    {
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param DireccionRepository     $rDireccion     The DireccionRepository object.
     * @param InmuebleRepository      $rInmueble      The InmuebleRepository object.
     * @param InmueblePadreRepository $rInmueblePadre The InmueblePadreRepository object.
     * @param PersonaRepository       $rPersona       The PersonaRepository object.
     *
     * @return void
     */
    public function handle(DireccionRepository $rDireccion, InmuebleRepository $rInmueble, InmueblePadreRepository $rInmueblePadre, PersonaRepository $rPersona)
    {
        $direccion = $this->crearDireccion($rDireccion);
        $inmueble = $this->crearInmueble($rInmueble);
        $this->adjuntarCaracteristicas($inmueble);
        $this->adjuntarFormatos($inmueble);

        $inmueblePadre = $this->crearOAsociarInmueblePadre($rInmueblePadre, $direccion, $inmueble);

        $this->crearUnidades($inmueblePadre);

        $this->nominarOAsignarAdministrador($rPersona, $inmueble);
        $this->nominarOAsignarPropietario($rPersona, $inmueble);

	// Notifica solo si es casa.
	if ($inmueble->id_tipo_inmueble == 2) {
	    $this->user->notify(new InmuebleCreado($inmueble));
	}

        return $inmueblePadre;
    }

    /**
     * Adjuntar Características.
     *
     * @param Inmueble $inmueble The Inmueble model.
     *
     * @return void
     */
    protected function adjuntarCaracteristicas(Inmueble $inmueble)
    {
        $data = $this->data;

        if (isset($data['caracteristicas'])) {
            foreach ($data['caracteristicas'] as $caracteristicaTipoInmueble) {
                $inmueble->caracteristicas()->attach($caracteristicaTipoInmueble['id_caracteristica']['id'], ['cantidad' => $caracteristicaTipoInmueble['id_caracteristica']['cantidad'], 'enum_estado' => 'ACTIVO', 'id_caracteristica_tipo_inmueble' => $caracteristicaTipoInmueble['id']]);
            }
        }
    }

    /**
     * Adjuntar Formatos.
     *
     * @param Inmueble $inmueble The Inmueble model.
     *
     * @return void
     */
    protected function adjuntarFormatos(Inmueble $inmueble)
    {
        $formatos = $this->data['formatos'];
        $inmueble->formatos()->attach($formatos);
    }

    /**
     * Crear Unidades.
     *
     * @param InmueblePadre $inmueblePadre The InmueblePadre model.
     *
     * @return void
     */
    protected function crearUnidades(InmueblePadre $inmueblePadre)
    {
        $data = $this->data;

        if (isset($data['unidades'])) {
            foreach ($data['unidades'] as $unidad) {
                 $this->dispatchNow(new CrearUnidad(array_merge($unidad, ['id_inmueble_padre' => $inmueblePadre->id, 'id_medida' => '1']), $this->user));
            }
        }
    }

    /**
     * Crear Dirección.
     *
     * @param DireccionRepository $repository The DireccionRepository object.
     *
     * @return Direccion
     */
    protected function crearDireccion(DireccionRepository $repository)
    {
        return $repository->create($this->data['id_direccion'])[1];
    }

    /**
     * Crear Inmueble.
     *
     * @param InmuebleRepository $repository The InmuebleRepository object.
     *
     * @return Inmueble
     */
    protected function crearInmueble(InmuebleRepository $repository)
    {
        return $repository->create(
            array_merge(
                $this->data['id_inmueble'],
                [
                'enum_estado' => 'ACTIVO',
                'id_usuario_creador' => $this->user->id
                ]
            )
        )[1];
    }

    /**
     * Crear o Asociar InmueblePadre.
     *
     * @param InmueblePadreRepository $repository The InmueblePadreRepository object.
     * @param Direccion               $direccion  The Direccion model.
     * @param Inmueble                $inmueble   The Inmueble model.
     *
     * @return InmueblePadre
     */
    protected function crearOAsociarInmueblePadre(InmueblePadreRepository $repository, Direccion $direccion, Inmueble $inmueble)
    {
        $data = $this->data;

        if (!isset($data['id_inmueble_padre'])) {
            $inmueblePadre = $repository->create(
                [
                'cant_pisos' => $data['cant_pisos'],
                'comision_administrador' => $data['comision_administrador'],
                'divisible_en_unidades' => $data['divisible_en_unidades'],
                'id_direccion' => $direccion->id,
                'id_inmueble' => $inmueble->id,
                'modalidad_propiedad' => $data['modalidad_propiedad'],
                'nombre' => $data['nombre'],
                ]
            )[1];
        } else {
            $inmueblePadre = $repository->findOrFail($data['id_inmueble_padre']);
	    $inmueblePadre = $this->dispatchNow(new ActualizarInmueble($inmueblePadre, $data, $this->user));
	}
    
        $inmueble->id_tabla_hija = $inmueblePadre->id;
	$inmueble->save();

        return $inmueblePadre;
    }

    /**
     * Nominar o Asignar Administrador Referente.
     *
     * @param PersonaRepository $repository The PersonaRepository object.
     * @param Inmueble          $inmueble   The Inmueble model.
     *
     * @return void
     */
    protected function nominarOAsignarAdministrador(PersonaRepository $repository, Inmueble $inmueble)
    {
        $data = $this->data;
        $user = $this->user;

        // id_administrador_referente presente en el array?
        if (isset($data['id_administrador_referente'])) {
            $id = $data['id_administrador_referente'];

            // id_administrador_referente no está vacío?
            if ($id) {
                // id_administrador_referente es distinto al id de la persona del usuario?
                if ($id != $user->id_persona) {
                    $persona = $repository->findOrFail($id);

                    $this->dispatch(new NominarAdministradorReferenteParaInmueble($persona, $inmueble));
                } else {
                    $persona = $user->idPersona;
                    $data = [];
                    if ($this->data['modalidad_propiedad'] === 'EN_CONDOMINIO') {
                        $data = array_only($this->data, ['dia_cobro_mensual', 'salario']);
                    }

                    $this->dispatch(new RelacionarAdministradorReferente($persona, $inmueble, $data));
                }
            }
        }
    }

    /**
     * Nominar o Asingar Propietario Referente.
     *
     * @param PersonaRepository $repository The PersonaRepository object.
     * @param Inmueble          $inmueble   The Inmueble model.
     *
     * @return void
     */
    protected function nominarOAsignarPropietario(PersonaRepository $repository, Inmueble $inmueble)
    {
        $data = $this->data;
        $user = $this->user;

        // id_propietario_referente no está presente en el array?
        if (isset($data['id_propietario_referente'])) {
            $id = $data['id_propietario_referente'];

            // id_propietario_referente no está vacío?
            if ($id) {
                // id_propietario_referente es distinto al id de la persona del usuario?
                if ($id != $user->id_persona) {
                    $persona = $repository->findOrFail($id);

                    $this->dispatch(new NominarPropietarioReferenteParaInmueble($persona, $inmueble, $user));
                } else {
                    $persona = $user->idPersona;
                    $this->dispatch(new RelacionarPropietarioReferente($persona, $inmueble));
                }
            }
        }
    }
}

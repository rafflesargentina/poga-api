<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Inmueble, Unidad, User };
use Raffles\Modules\Poga\Repositories\{ InmuebleRepository, UnidadRepository, PersonaRepository };
use Raffles\Modules\Poga\Notifications\UnidadActualizada;

use Illuminate\Foundation\Bus\DispatchesJobs;

class ActualizarUnidad
{
    use DispatchesJobs;

    /**
     * The Unidad model.
     *
     * @var Unidad
     */
    protected $unidad;

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
    public function __construct(Unidad $unidad, $data, User $user)
    {
        $this->unidad = $unidad;
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param InmuebleRepository      $rInmueble      The InmuebleRepository object.
     * @param UnidadRepository        $rUnidad        The UnidadRepository object.
     * @param PersonaRepository       $rPersona       The PersonaRepository object.
     *
     * @return void
     */
    public function handle(InmuebleRepository $rInmueble, UnidadRepository $rUnidad, PersonaRepository $rPersona)
    {
        $unidad = $this->actualizarUnidad($rUnidad);

        $inmueble = $this->actualizarInmueble($rInmueble);

        $this->sincronizarCaracteristicas($inmueble);
        $this->sincronizarFormatos($inmueble);

        $this->nominarOAsignarPropietario($rPersona, $inmueble);

        $this->user->notify(new UnidadActualizada($unidad, $this->user));

        return $unidad;
    }

    /**
     * Actualizar Inmueble.
     *
     * @param InmuebleRepository $repository The InmuebleRepository object.
     *
     * @return Inmueble
     */
    protected function actualizarInmueble(InmuebleRepository $repository)
    {
        if (isset($this->data['id_inmueble'])) {
            return $repository->update($this->unidad->idInmueble, $this->data['id_inmueble'])[1];
    
        }
    }

    /**
     * Actualizar Unidad.
     *
     * @param UnidadRepository $repository The UnidadRepository object.
     *
     * @return Unidad
     */
    protected function actualizarUnidad(UnidadRepository $repository)
    {
	$data = $this->data;

        return $repository->update($this->unidad, [
            'area' => $data['area'],
	    'area_estacionamiento' => isset($data['area_estacionamieno']) ? $data['area_estacionamiento'] : 0,
	    'divisible_en_unidades' => isset($data['divisible_en_unidades']) ? $data['divisible_en_unidades'] : 0,
	    'id_formato_inmueble' => $data['id_formato_inmueble'],
	    'id_medida' => $data['id_medida'],
	    'numero' => $data['numero'],
	    'piso' => $data['piso'],
            ]
        )[1];
    }

    /**
     * Sincronizar Características.
     *
     * @param Inmueble $inmueble The Inmueble model.
     *
     * @return void
     */
    protected function sincronizarCaracteristicas(Inmueble $inmueble)
    {
        if (isset($this->data['caracteristicas'])) {
            $caracteristicasTipoInmueble = $this->data['caracteristicas'];
	    $inmueble->caracteristicas()->sync([]);
	    foreach ($caracteristicasTipoInmueble as $caracteristicaTipoInmueble) {
                $inmueble->caracteristicas()->syncWithoutDetaching([$caracteristicaTipoInmueble['id_caracteristica']['id'] => ['cantidad' => $caracteristicaTipoInmueble['id_caracteristica']['cantidad'], 'enum_estado' => 'ACTIVO', 'id_caracteristica_tipo_inmueble' => $caracteristicaTipoInmueble['id']]]);
            }
        }
    }

    /**
     * Sincronizar Formatos.
     *
     * @param Inmueble $inmueble The Inmueble model.
     *
     * @return void
     */
    protected function sincronizarFormatos(Inmueble $inmueble)
    {
        if (isset($this->data['formatos'])) { 
            $formatos = $this->data['formatos'];
            if ($formatos) {
                $inmueble->formatos()->sync($formatos);
            }
        }
    }

    /**
     * Nominar o Asignar Propietario Referente.
     *
     * @param PersonaRepository $repository The PersonaRepository object.
     * @param Inmueble          $inmueble   The Inmueble model.
     *
     * @return void
     */
    protected function nominarOAsignarPropietario(PersonaRepository $repository, Inmueble $inmueble)
    {
        // idPropietarioReferente no está presente en el array?
        if (isset($this->data['id_propietario_referente'])) {
            $propietarioReferente = $this->data['id_propietario_referente'];
        
            $id = is_string($propietarioReferente) ?? $propetarioReferente['id'];

            // idPropietarioReferente no está vacío?
            if ($id) {
                $user = $this->user;

                // idPropietarioReferente es distinto al id de la persona del usuario?
                if ($id != $user->id_persona) {
                    $persona = $repository->findOrFail($id);

                    $this->dispatch(new NominarPropietarioReferenteParaInmueble($persona, $inmueble, $this->user));
                } else {
                    $persona = $user->idPersona;
                    $this->dispatch(new RelacionarPropietarioReferente($persona, $inmueble));
                }
            }
        }
    }
}

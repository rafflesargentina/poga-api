<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Inmueble, InmueblePadre, User };
use Raffles\Modules\Poga\Repositories\{ DireccionRepository, InmuebleRepository, InmueblePadreRepository, PersonaRepository };
use Raffles\Modules\Poga\Notifications\InmuebleActualizado;

use Illuminate\Foundation\Bus\DispatchesJobs;

class ActualizarInmueble
{
    use DispatchesJobs;

    /**
     * The InmueblePadre model.
     *
     * @var InmueblePadre
     */
    protected $inmueblePadre;

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
    public function __construct(InmueblePadre $inmueblePadre, $data, User $user)
    {
        $this->inmueblePadre = $inmueblePadre;
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
        $inmueblePadre = $this->actualizarInmueblePadre($rInmueblePadre);

        $direccion = $this->actualizarDireccion($rDireccion);
        $inmueble = $this->actualizarInmueble($rInmueble);

        $this->sincronizarCaracteristicas($inmueble);
        $this->sincronizarFormatos($inmueble);

        $this->nominarOAsignarPropietario($rPersona, $inmueble);

        $this->user->notify(new InmuebleActualizado($inmueble, $this->user));

        return $inmueblePadre;
    }

    /**
     * Actualizar Dirección.
     *
     * @param DireccionRepository $repository The DireccionRepository object.
     *
     * @return \Raffles\Modules\Poga\Models\Direccion
     */
    protected function actualizarDireccion(DireccionRepository $repository)
    {
        if (isset($this->data['id_direccion'])) {
            return $repository->update($this->inmueblePadre->idDirecccion, $this->data['id_direccion'])[1];
        }
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
            return $repository->update($this->inmueblePadre->idInmueble, $this->data['id_inmueble'])[1];
    
        }
    }

    /**
     * Actualizar InmueblePadre.
     *
     * @param InmueblePadreRepository $repository The InmueblePadreRepository object.
     *
     * @return InmueblePadre
     */
    protected function actualizarInmueblePadre(InmueblePadreRepository $repository)
    {
	$data = $this->data;

        return $repository->update($this->inmueblePadre, [
            'cant_pisos' => $data['cant_pisos'],
            'comision_administrador' => $data['comision_administrador'],
            'divisible_en_unidades' => $data['divisible_en_unidades'],
            'nombre' => $data['nombre'],
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

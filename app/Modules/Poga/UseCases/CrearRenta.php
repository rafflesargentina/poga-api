<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\User;
use Raffles\Modules\Poga\Repositories\{ InmuebleRepository, PersonaRepository, RentaRepository, UnidadRepository };
use Raffles\Modules\Poga\Notifications\{ RentaCreadaPropietarioReferente, RentaCreadaInquilinoReferente };

use Illuminate\Foundation\Bus\DispatchesJobs;
use RafflesArgentina\ResourceController\Traits\WorksWithFileUploads;
use Storage;

class CrearRenta
{
    use DispatchesJobs, WorksWithFileUploads;

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
     * @param RentaRepository   $rRenta   The RentaRepository object.
     * @param PersonaRepository $rPersona The PersonaRepository object.
     *
     * @return void
     */
    public function handle(RentaRepository $rRenta, PersonaRepository $rPersona, UnidadRepository $rUnidad, InmuebleRepository $rInmueble)
    {
        $data = $this->data;

        $inquilino = $rPersona->findOrFail($data['id_inquilino']);
        //if (!$inquilino->user) {
            $estado = 'ACTIVO';
        //} else {
            //$estado = 'PENDIENTE';
        //}

        $inmueble = $rInmueble->findOrFail($data['id_inmueble']);

        // Es una unidad?
        if ($inmueble->enum_tabla_hija === 'UNIDADES') {
            // Nomina al inquilino referente para la unidad.
            $this->dispatchNow(new NominarInquilinoReferenteParaUnidad($inquilino, $inmueble->idUnidad, $this->user));
        } else {
            // Nomina al inquilino referente para el condominio.
            $this->dispatchNow(new NominarInquilinoReferenteParaInmueble($inquilino, $inmueble, $this->user));
        }

        $renta = $rRenta->create(
            array_merge(
                $data,
                [
                // Agrega campos que no se piden en el formulario.
                'enum_estado' => $estado,
                ]
            )
        )[1];

        $this->adjuntarEstadosInmueble($renta);

        if (!$renta->vigente) {
            $boletaComisionAdministrador = $this->dispatchNow(new GenerarComisionPrimerMesAdministrador($renta));
            $boletaRenta = $this->dispatchNow(new GenerarPagareRentaPrimerMes($renta));
	} else {
	    $boletaComisionAdministrador = null;
            $boletaRenta = null;
	}

        $inquilino->user->notify(new RentaCreadaInquilinoReferente($renta, $boletaRenta));

        //$personaAdministradorReferente = $renta->idInmueble->idAdministradorReferente;
        // Notifica al administrador referente.
        //if ($personaAdministradorReferente) {
            //$userAdministradorReferente = $personaAdministradorReferente->idPersona->user;
            //if ($userAdministradorReferente) {
                //$userAdministradorReferente->notify(new RentaCreadaAdministradorReferente($renta, $boletaRenta));
            //}
        //}

        $personaPropietarioReferente = $renta->idInmueble->idPropietarioReferente;
        // Notifica al propietario referente.
        if ($personaPropietarioReferente) {
            $userPropietarioReferente = $personaPropietarioReferente->idPersona->user;
            if ($userPropietarioReferente) {
                $userPropietarioReferente->notify(new RentaCreadaPropietarioReferente($renta, $boletaRenta));
            }
        }

        return ['renta' => $renta, 'boletas' => ['renta' => $boletaRenta, 'comisionAdministrador' => $boletaComisionAdministrador]];
    }

    /**
     * Adjuntar Estados de Inmueble.
     *
     * @param Renta $renta The Renta model.
     *
     * @return void
     */
    protected function adjuntarEstadosInmueble($renta)
    {
        $estadosInmueble = $this->data['estados_inmueble'];
	foreach ($estadosInmueble as $estadoInmueble) {
	    if ($estadoInmueble['foto']) {
                $image = $estadoInmueble['foto'];
                $imageInfo = explode(";base64,", $image);
                $imageExt = str_replace('data:image/', '', $imageInfo[0]);
		$image = str_replace(' ', '+', $imageInfo[1]);
		$imageName = "estado-inmueble-".$estadoInmueble['id'].'-'.time().".".$imageExt;

		$relativePath = $this->getDefaultRelativePath();
		$storagePath = $this->getStoragePath($relativePath);
		$uploadedFile = $relativePath.$imageName;
                Storage::put($uploadedFile, base64_decode($image));
            } else {
                $uploadedFile = null;
	    }

	    $renta->estados_inmueble()->attach($estadoInmueble['id'], ['cantidad' => $estadoInmueble['cantidad'], 'enum_estado' => 'ACTIVO', 'reparar' => $estadoInmueble['reparar'], 'foto' => $uploadedFile]);
        }
    }
}

<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Repositories\RentaRepository;

use Illuminate\Foundation\Bus\DispatchesJobs;
use RafflesArgentina\ResourceController\Traits\WorksWithFileUploads;
use Storage;

class ActualizarRenta
{
    use DispatchesJobs, WorksWithFileUploads;

    /**
     * The form data and the Renta model.
     *
     * @var array
     */
    protected $data, $renta;

    /**
     * Create a new job instance.
     *
     * @param Renta $renta The Renta model.
     * @param array $data  The form data.
     *
     * @return void
     */
    public function __construct(Renta $renta, $data)
    {
        $this->renta = $renta;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param RentaRepository $rRenta The RentaRepository object.
     *
     * @return void
     */
    public function handle(RentaRepository $rRenta)
    {
        $this->sincronizarEstadosInmueble($this->renta);

        $renta = $rRenta->update($this->renta, $this->data)[1];

        return $renta;
    }

    /**
     * Sincronizar Estados de Inmueble.
     *
     * @param Renta $renta The Renta model.
     *
     * @return void
     */
    protected function sincronizarEstadosInmueble($renta)
    {
        if (isset($this->data['estados_inmueble'])) {
            $estadosInmueble = $this->data['estados_inmueble'];
            $renta->estados_inmueble()->sync([]);
    
            foreach ($estadosInmueble as $estadoInmueble) {
                $foto = $estadoInmueble['foto'];
                $uploadedFile = $foto;
                if ($foto) {
                    $imageInfo = explode(";base64,", $foto);
                    if (isset($imageInfo[1])) {
                        $imageExt = str_replace('data:image/', '', $imageInfo[0]);
                        $image = str_replace(' ', '+', $imageInfo[1]);
                        $imageName = "estado-inmueble-".$estadoInmueble['id'].'-'.time().".".$imageExt;

                        $relativePath = $this->getDefaultRelativePath();
                        $storagePath = $this->getStoragePath($relativePath);
                        $uploadedFile = $relativePath.$imageName;
                        Storage::put($uploadedFile, base64_decode($image));
                    }
                }

                $renta->estados_inmueble()->syncWithoutDetaching([$estadoInmueble['id'] => ['cantidad' => $estadoInmueble['cantidad'], 'enum_estado' => 'ACTIVO', 'reparar' => $estadoInmueble['reparar'], 'foto' => $uploadedFile]]);
            }
        }
    }
}

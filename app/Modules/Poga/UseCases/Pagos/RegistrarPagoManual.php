<?php

namespace Raffles\Modules\Poga\UseCases\Pagos;

use Raffles\Modules\Poga\Models\{ Pagare, User };
use Raffles\Modules\Poga\Notifications\{ PagoConfirmadoAcreedor, PagoConfirmadoDeudor, PagoConfirmadoParaAdminPoga };
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\{ AnularBoletaPago, TraerBoletaPago };

use Carbon\Carbon;
use DB;

class RegistrarPagoManual
{
    /**
     * La descripción del pago.
     *
     * @var string
     */
    protected $descripcion;

    /**
     * El monto del pago.
     *
     * @var mixed
     */
    protected $monto;

    /**
     * Fue pagado fuera de sistema o no.
     *
     * @var bool
     */
    protected $pagadoFueraSistema;

    /**
     * The Pagare object.
     *
     * @var Pagare
     */
    protected $pagare;

    /**
     * The PagareRepository object.
     *
     * @var PagareRepository
     */
    protected $rPagare;

    /**
     * Create a new job instance.
     *
     * @param  Pagare $pagare            The Pagare object.
     * @param  mixed  $monto             El monto del pago.
     * @param  bool   $pagadoFueraSitema Fue pagado fuera de sistema o no.
     * @param  string $descripcion       La descripción del pago.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, $monto, $pagadoFueraSistema = false, $descripcion = null)
    {
        $this->pagare = $pagare;
        $this->monto = $monto;
        $this->rPagare = new PagareRepository;
        $this->pagadoFueraSistema = $pagadoFueraSistema;
        $this->descripcion = $descripcion;

        $this->adminUser = $this->getAdminUser();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $descripcion = $this->descripcion;
        $monto = $this->monto;
        $pfs = $this->pagadoFueraSistema;
        $pagare = $this->pagare;
        $rPagare = $this->rPagare;

        if (!in_array($pagare->enum_clasificacion_pagare, ['OTRO', 'PAGO_DIFERIDO', 'RENTA'])) {
            throw \Exception('El pagare debe estar clasificado como "OTRO", "PAGO_DIFERIDO" o "RENTA"');
        }

        $uc = new TraerBoletaPago($pagare->id);
        $boleta = $uc->handle();

        // Pagare del tipo OTRO o PAGO_DIFERIDO.
        if (in_array($pagare->enum_clasificacion_pagare, ['OTRO', 'PAGO_DIFERIDO'])) {
            $p = $rPagare->update($pagare, ['descripcion' => $descripcion, 'enum_estado' => ($pfs ? 'TRANSFERIDO' : 'PAGADO'), 'fecha_pago_a_confirmar' => Carbon::today(), 'pagado_fuera_sistema' => $pfs])[1];
        }


        // Pagare del tipo RENTA.
        if ($pagare->enum_clasificacion_pagare === 'RENTA') {
            $items = $rPagare->where('descripcion', $descripcion)->whereIn('enum_clasificacion_pagare', ['COMISION_INMOBILIARIA', 'DEPOSITO_GARANTIA', 'MULTA_RENTA', 'RENTA'])->where('enum_estado', 'PENDIENTE')->where('id_tabla', $pagare->id_tabla)->where('fecha_pagare', DB::raw('DATE_FORMAT(fecha_pagare, "%Y-%m")'))->get();

            foreach($items as $item) {
                $i = $rPagare->update($item, ['descripcion' => $descripcion, 'enum_estado' => ($pfs ? 'TRANSFERIDO' : 'PAGADO'), 'fecha_pago_a_confirmar' => Carbon::today(), 'pagado_fuera_sistema' => $pfs])[1];

                $this->generarPagareComision($i);
            }

            $p = $rPagare->update($pagare, ['descripcion' => $descripcion, 'enum_estado' => ($pfs ? 'TRANSFERIDO' : 'PAGADO'), 'fecha_pago_a_confirmar' => Carbon::today(), 'pagado_fuera_sistema' => $pfs])[1];
        }

        if ($p) {
            if (!$pfs) {
                $this->generarPagareComision($p);
            }
    
            $uc = new AnularBoletaPago($p->id);
            $uc->handle();

            $p->idPersonaAcreedora->user->notify(new PagoConfirmadoAcreedor($p, $boleta['debt']));
            $p->idPersonaDeudora->user->notify(new PagoConfirmadoDeudor($p, $boleta['debt']));

            $this->adminUser->notify(new PagoConfirmadoParaAdminPoga($p, $boleta['debt']));
        }
    }

    protected function getAdminUser()
    {
        return User::where('email', env('MAIL_ADMIN_ADDRESS'))->first();
    }

    protected function generarPagareComision(Pagare $pagare) {
        // Para cada ítem crea un pagaré de comisión.
        $this->rPagare->create(
            [
                'descripcion' => 'Comisión POGA',
                'enum_clasificacion_pagare' => 'COMISION_POGA',
                'enum_estado' => 'PENDIENTE',
                'fecha_pagare' => Carbon::now(),
                'fecha_vencimiento' => Carbon::now()->addYear(),
                'id_inmueble' => $pagare->id_inmueble,
                'id_moneda' => $pagare->id_moneda,
                'id_persona_acreedora' => $this->adminUser->id_persona,
                'id_persona_deudora' => $pagare->id_persona_acreedora,
                'id_tabla' => $pagare->id,
                'monto' => $pagare->monto * 5.5 / 100
            ]
        );
    }
}

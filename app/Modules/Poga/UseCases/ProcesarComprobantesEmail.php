<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\User;
use Raffles\Modules\Poga\Notifications\{ PagoConfirmadoAcreedor, PagoConfirmadoParaAdminPoga, PagoConfirmadoDeudor };
use Raffles\Modules\Poga\Notifications\ProcesarComprobantesEmail\{ ImporteNoCoincidente, PagaresNoEncontrados, PersonasNoEncontradas };
use Raffles\Modules\Poga\Repositories\{ PagareRepository, PersonaRepository };

use Webklex\IMAP\Client;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class Comprobante {
    public $importe = 0;
    public $bancoRemitente = "";
    public $nroCuenta = "";
    public $nroCuentaCredito = "";
    public $cliente = "";
    public $fechaOperacion = null;
    public $fechaAcreditacion = null;
}

class ProcesarComprobantesEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rPersona = new PersonaRepository;
        $rPagare = new PagareRepository;

        $oClient = new Client([
            'host'          => 'c1590800.ferozo.com',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => false,
            'username'      => 'comprobantes@nodobroker.com',
            'password'      => 'TXTQPz@8gG',
            'protocol'      => 'imap',
        ]);

        $oClient->connect();

        $aFolder = $oClient->getFolders();

        foreach($aFolder as $oFolder) {
            $aMessage = $oFolder->getUnseenMessages(); //Mensajes sin leer
            
            foreach($aMessage as $oMessage){
                try {
                    $info = 'Leer emails de comprobantes de pago: "Nuevo mensaje sin leer"';
                    Log::info($info);
                } catch (\Exception $e) {
                    //
                }

                $asunto = $oMessage->getSubject();
                //$asuntosValidos = ['Fwd: Notificaciones BBVA', 'Fwd: Notificación de Transferencia Bancaria de Envío - BBVA POGA', 'Fwd: Transferencia Interbancaria', 'Notificación de Transferencia Bancaria de Recepción - BBVA POGA', 'Notificaciones BBVA', 'Transferencia Interbancaria'];
          
                if (true) { 
                //if (in_array($asunto, $asuntosValidos)) {
                    try {
                        $info = 'Asunto válido: '.$asunto;
                        Log::info($info);
                    } catch (\Exception $e) {
    
                    }

                    //$oMessage->unsetFlag('SEEN');
                    //$oClient->expunge();

                    $content = $oMessage->getTextBody(true);
                    $comprobante = $this->parseContent($content);

                    $personas = $rPersona->where('personas.enum_estado', 'ACTIVO')
                        ->where(function($query) use($comprobante) { 
                            $query->where('cuenta_bancaria', $comprobante->nroCuenta);
                            $query->orWhereRaw('LOWER(CONCAT(nombre, " ", apellido)) LIKE ?', ['%'.strtolower($comprobante->cliente).'%']);
                            $query->orWhereRaw('LOWER(CONCAT(apellido, " ", nombre)) LIKE ?', ['%'.strtolower($comprobante->cliente).'%']);
                        })
                        ->get();


                    if ($personas->count() === 0) {
                        // Notifica por mail al administrador.
                        $admin = $this->getAdminUser();
                        if ($admin) {
                            $admin->notify(new PersonasNoEncontradas($comprobante));
                        } 

                    } else {
                        foreach($personas as $persona) {
                            // Busca pagares clasificados como 'OTRO' o 'PAGO_DIFERIDO'.
                            $pagare = $rPagare->whereIn('enum_clasificacion_pagare', ['OTRO', 'PAGO_DIFERIDO'])->where('id_persona_deudora', $persona->id)->where('enum_estado', 'PENDIENTE')->where('monto', $comprobante->importe)->first();
                            if ($pagare) {
                                // Dispara caso de uso de pago de boleta.
                                $uc = new TraerBoletaPago($pagare->id);
                                $boleta = $uc->handle();

                                $uc = new AnularBoletaPago($pagare->id);
                                $uc->handle();

                                $p = $rPagare->update($pagare, ['enum_estado' => 'PAGADO', 'fecha_pago_a_confirmar' => Carbon::today(), 'pagado_fuera_sistema' => '1'])[1];
                                $p->idPersonaAcreedora->user->notify(new PagoConfirmadoAcreedor($p, $boleta['debt']));
                                $p->idPersonaDeudora->user->notify(new PagoConfirmadoDeudor($p, $boleta['debt']));

                                $admin = $this->getAdminUser();
                                $admin->notify(new PagoConfirmadoParaAdminPoga($p, $boleta['debt']));

                                break 1;
                            }

                            // Busca pagares clasificados como 'COMISION_INMOBILIARIA', 'DEPOSITO_GARANTIA', 'MULTA_RENTA' y 'RENTA' y los agrupa por el id de renta.
                            $pagaresConSuma = $rPagare->selectRaw('SUM(monto) as suma')->whereIn('enum_clasificacion_pagare', ['COMISION_INMOBILIARIA', 'DEPOSITO_GARANTIA', 'MULTA_RENTA', 'RENTA'])->where('id_persona_deudora', $persona->id)->where('enum_estado', 'PENDIENTE')->get();

                            $pagares = $rPagare->whereIn('enum_clasificacion_pagare', ['COMISION_INMOBILIARIA', 'DEPOSITO_GARANTIA', 'MULTA_RENTA', 'RENTA'])->where('id_persona_deudora', $persona->id)->where('enum_estado', 'PENDIENTE')->get();

                            $pagareRenta = $pagares->firstWhere('enum_clasificacion_pagare', 'RENTA');

                            if ($pagares->count() > 0) {
                                $importe = $pagaresConSuma->sum('suma');
                                if ($importe === (float) $comprobante->importe) {
                                    // Dispara caso de uso de pago de boleta.
                                    $uc = new TraerBoletaPago($pagareRenta->id);
                                    $boleta = $uc->handle();

                                    $uc = new AnularBoletaPago($pagareRenta->id);
                                    $uc->handle();

                                    foreach($pagares as $pagare) {
                                        $p = $rPagare->update($pagare, ['enum_estado' => 'PAGADO', 'fecha_pago_a_confirmar' => Carbon::today(), 'pagado_fuera_sistema' => '1'])[1];
                                    }

                                    $pagareRenta->idPersonaAcreedora->user->notify(new PagoConfirmadoAcreedor($p, $boleta['debt']));
                                    $pagareRenta->idPersonaDeudora->user->notify(new PagoConfirmadoDeudor($p, $boleta['debt']));

                                    $admin = $this->getAdminUser();
                                    $admin->notify(new PagoConfirmadoParaAdminPoga($pagareRenta, $boleta['debt']));

                                    break 1;
                                }
                            }

                            // Notifica por mail al administrador.
                            $admin = $this->getAdminUser();
                            if ($admin) {
                                $admin->notify(new PagaresNoEncontrados($comprobante));
                            }
                        }
                    }
                }
                
                // Mueve el mensaje a leidos
                /* if($oMessage->moveToFolder('INBOX.read') == true){
                    echo 'Message has ben moved';
                }else{
                    echo 'Message could not be moved';
                }*/
            }
        }
    }

    public function parseContent($content)
    {
        $str = str_replace('*', '', $content);

        $comprobante = new Comprobante();

        $pos = strpos($str, 'Importe:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->importe = $this->sanitizeImporte($arr[1]);
        }
    
        $pos = strpos($str, 'Banco Remitente:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->bancoRemitente = trim($arr[1]);
        }
    
        $pos = strpos($str, 'N° Cuenta Crédito:');
        if ($pos){
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->nroCuentaCredito = $this->sanitizeNroCuenta($arr[1]);
        }

        $pos = strpos($str, 'N° Cuenta Débito:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->nroCuenta = $this->sanitizeNroCuenta($arr[1]);
        }

        $pos = strpos($str, 'Cliente:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->cliente = trim($arr[1]);
        }

        $pos = strpos($str, 'Nombre Beneficiario:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->cliente = trim($arr[1]);
        }

        $pos = strpos($str, 'Fecha Operación:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->fechaOperacion = Carbon::createFromFormat('d/m/Y', trim($arr[1]));
        }

        $pos = strpos($str, 'Fecha Acreditación:');
        if ($pos) {
            $line = $this->getPosToEndOfLine($str, $pos);
            $arr = explode(':', $line);
            $comprobante->fechaAcreditacion = Carbon::createFromFormat('d/m/Y', trim($arr[1]));
        }   

        return $comprobante;

    }

    public function getPosToEndOfLine($str, $pos) {
        return substr($str, $pos, (strpos($str, PHP_EOL, $pos)) - $pos);
    }

    public function sanitizeImporte($str) {
        return (float) filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT);
    }

    public function sanitizeNroCuenta($str) {
        return (int) filter_var(ltrim(trim($str), 0), FILTER_SANITIZE_NUMBER_INT);
    }

    protected function getAdminUser()
    {
        return User::where('email', env('MAIL_ADMIN_ADDRESS'))->first();
    }
}

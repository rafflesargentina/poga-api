<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Gr8Shivam\SmsApi\Notifications\SmsApiChannel;
use Gr8Shivam\SmsApi\Notifications\SmsApiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PagareCreadoPersonaDeudora extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Pagare model.
     *
     * @var Pagare
     */
    protected $pagare;

    /**
     * Create a new notification instance.
     *
     * @param Pagare $pagare The Pagare model.
     *
     * @return void
     */
    public function __construct(Pagare $pagare)
    {
        $this->pagare = $pagare;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [SmsApiChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        try {
            $pagare = $this->pagare;
            $inmueble = $pagare->idInmueble;
            $unidad = $pagare->idUnidad;
            $acreedor = $pagare->idPersonaAcreedora;

            switch ($pagare->enum_clasificacion_pagare) {
            case 'RENTA':
                if ($unidad) {
                    $line1 = 'Se generó un pago de renta pendiente por el contrato del '.$unidad->idInmueble->idTipoInmueble->tipo.' piso '.$unidad->piso.' nº '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'"';
                } else {
                    $line1 = 'Se te generó un pago de renta pendiente por el contrato del inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
                }
                $line2 = 'Propietario: '.$acreedor->nombre_y_apellidos;
                $line3 = 'Monto: '.number_format($pagare->monto,0,',','.').' '.$pagare->idMoneda->abbr;
                $line4 = 'Vencimiento: '.Carbon::parse($pagare->fecha_vencimiento)->format('d/m/Y');
                $line5 = 'Comparta el siguiente link con el inquilino para la realización de pagos: '.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id));
                $subject = 'Pago pendiente de renta generado '.Carbon::parse($pagare->fecha_pagare)->format('d/m/Y');
            break;
            default:
                if ($unidad) {
                    $line1 = 'Se generó un pago pendiente para '.$unidad->idInmueble->idTipoInmueble->tipo.' piso '.$unidad->piso.' nº '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'"';
                } else {
                    $line1 = 'Se generó una solicitud de pago por el inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
                }
                $line2 = 'Propietario: '.$acreedor->nombre_y_apellidos;
                $line3 = 'Monto: '.number_format($pagare->monto,0,',','.').' '.$pagare->idMoneda->abbr;
		$line4 = 'Observación: '.$pagare->descripcion;
                $line5 = 'En el siguiente link podés ver la información para la cancelación de los pagos pendientes: '.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id));
                $subject = 'Solicitud de pago: '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
            }

            return (new MailMessage)
                ->subject($subject)
                ->greeting('Hola '.$notifiable->idPersona->nombre)
                ->line($line1)
                ->line($line2)
                ->line($line3)
	        ->line($line4)
		->line($line5);

	} catch (\Exception $e) {
//
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toSmsApi($notifiable)
    {
	$pagare = $this->pagare;
	
	switch($pagare->enum_clasificacion_pagare) {
	case 'RENTA':
            $content = normalize('Tenés un pago pendiente de renta que vence el '.Carbon::parse($pagare->fecha_vencimiento)->format('d/m/Y').', realiza tu pago en :'.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id)));
	    return (new SmsApiMessage)
                ->content($content);
	    break;
	case 'OTRO':
            $content = normalize('Se generó una solicitud de pago. Realiza tu pago de '.Carbon::parse($pagare->fecha_pagare)->format('m/Y').' en :'.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id)));
            return (new SmsApiMessage)
                ->content($content);
	break;
	}
    }
}

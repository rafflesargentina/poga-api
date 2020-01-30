<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PagareCreadoParaAdminPoga extends Notification implements ShouldQueue
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
        return ['mail'];
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
        $pagare = $this->pagare;
        $inmueble = $pagare->idInmueble;
        $unidad = $pagare->idUnidad;
	$acreedor = $pagare->idPersonaAcreedora;
	$deudor = $pagare->idPersonaDeudora;

	switch ($pagare->enum_clasificacion_pagare) {
        case 'RENTA':
            if ($unidad) {
                $line1 = 'Se generó un pago de renta pendiente por el contrato del '.$unidad->idInmueble->idTipoInmueble->tipo.' piso '.$unidad->piso.' nº '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'"';
            } else {
                $line1 = 'Se te generó un pago de renta pendiente por el contrato del inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
	    }
	    $line2 = 'Propietario: '.$acreedor->nombre_y_apellidos;
            $line3 = 'Inquilino: '.$deudor->nombre_y_apellidos;
            $line4 = 'Monto: '.number_format($pagare->monto,0,',','.').' '.$pagare->idMoneda->abbr;
            $line5 = 'Vencimiento: '.Carbon::parse($pagare->fecha_vencimiento)->format('d/m/Y');
            $line6 = 'Comparta el siguiente link con el inquilino para la realización de pagos: '.str_replace('.api', '', url('realiza-un-pago/'.$pagare->id));
	    $subject = 'Pago pendiente de renta generado '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
	    break;
            default:
                if ($unidad) {
                    $line1 = 'Se generó una solicitud de pago por el "'.$unidad->idInmueble->idTipoInmueble->tipo.' piso: '.$unidad->piso.' - '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre;
                } else {
                    $line1 = 'Se generó una solicitud de pago por el inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
		}	    
                $line2 = 'Inquilino: '.$deudor->nombre_y_apellidos;
                $line3 = 'Propietario: '.$acreedor->nombre_y_apellidos;
                $line4 = 'Observaciones: '.$pagare->descripcion;
		$line5 = 'Monto: '.number_format($pagare->monto,0,',','.').' '.$pagare->idMoneda->abbr;
		$line6 = '';
                $subject = 'Solicitud de pago: '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
	}

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line($line1)
            ->line($line2)
            ->line($line3)
	    ->line($line4)
	    ->line($line5)
	    ->line($line6);
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
}

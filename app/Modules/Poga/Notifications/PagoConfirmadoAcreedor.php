<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PagoConfirmadoAcreedor extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Pagare model.
     *
     * @var Pagare
     */
    protected $pagare;

    /**
     * La boleta de pago.
     *
     * @var mixed
     */
    protected $boleta;

    /**
     * Create a new notification instance.
     *
     * @param Pagare $pagare The Pagare model.
     * @param mixed  $boleta La boleta de pago.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, $boleta)
    {
        $this->pagare = $pagare;
        $this->boleta = $boleta;
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
        $estado = $pagare->enum_estado;
        $inmueble = $pagare->idInmueble;
        $unidad = $pagare->idUnidad;
        $deudor = $pagare->idPersonaDeudora;

        if ($unidad) {
            $line1 = 'Los pagos pendientes por el contrato del '.$unidad->idInmueble->idTipoInmueble->tipo.' '.$unidad->piso.' nº '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'" de '.Carbon::parse($pagare->fecha_pagare)->format('m/Y').' fueron cancelados.';
        } else {
            $line1 = 'Los pagos pendientes por el "'.$inmueble->idInmueblePadre->nombre.'" de '.Carbon::parse($pagare->fecha_pagare)->format('m/Y').' fueron cancelados.';
        }

        $line2 = 'Inquilino: '.$deudor->nombre_y_apellidos;
        $line3 = 'Se le notificará al momento de la transferencia a su cuenta bancaria.';

        return (new MailMessage)
            ->subject('Pago confirmado: '.Carbon::parse($pagare->fecha_pagare)->format('m/Y'))
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line($line1)
            ->line($line2)
            ->line($line3)
	    ->action('Ir a "Mis Pagos"', str_replace('api.', 'app.', url('/cuenta/mis-pagos')))
            ->markdown('poga::mail.pago-confirmado-para-acreedor', ['boleta' => $this->boleta, 'pagare' => $pagare, 'user' => $notifiable]);
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

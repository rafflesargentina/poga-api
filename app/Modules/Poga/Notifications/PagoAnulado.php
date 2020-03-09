<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PagoAnulado extends Notification implements ShouldQueue
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

        if ($unidad) {
            $line1 = 'El pago para la Unidad "Piso: '.$unidad->piso.' - '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'" fue anulado.';
        } else {
            $line1 = 'El pago para el inmueble "'.$inmueble->idInmueblePadre->nombre.'" fue anulado.';
        }

        $line2 = 'Persona acreedora: '.$acreedor->nombre_y_apellidos;
        $line3 = 'Persona deudora: '.$deudor->nombre_y_apellidos;
        $line4 = 'Tipo: '.$pagare->clasificacion;
    
        switch ($pagare->enum_clasificacion_pagare) {
        case 'MULTA_RENTA':
            $line5 = 'Contrato de renta con fecha '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
            break;
        case 'OTRO':
            $line5 = 'Observación: '.$pagare->descripcion;
            break;
        case 'RENTA':
            $line5 = 'Contrato de renta con fecha '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
            break;
        default:
            $line5 = null;
        }
        $line6 = 'Monto: '.$pagare->idMoneda->abbr.' '.number_format($pagare->monto, 0, ',', '.');

        return (new MailMessage)
            ->subject('El pago fue anulado')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line($line1)
            ->line($line2)
            ->line($line3)
            ->line($line4)
            ->line($line5)
            ->line($line6)
            ->action('Ir a "Mis Pagos"', str_replace('.api', '', url('/cuenta/mis-pagos')));
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

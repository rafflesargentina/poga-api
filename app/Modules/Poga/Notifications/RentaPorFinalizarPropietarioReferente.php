<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Renta;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RentaPorFinalizarPropietarioReferente extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Renta model;
     *
     * @var Renta
     */
    protected $renta;

    /**
     * Create a new notification instance.
     *
     * @param Renta $renta  The Renta model.
     *
     * @return void
     */
    public function __construct(Renta $renta)
    {
        $this->renta = $renta;
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
	$renta = $this->renta;
        $inmueble = $renta->idInmueble;
        $unidad = $renta->idUnidad;

        if ($unidad) {
            $line = 'El contrato de renta para el '.$unidad->tipo.' '.$unidad->piso.' nº '.$unidad->numero.' del Inmueble "'.$unidad->idInmueblePadre->nombre.'" está por vencer en '.$renta->dias_notificacion_previa_renovacion.' días. Coordina con tu inquilino la finalización o renovación.';
        } else {
            $line = 'El contrato de renta para el inmueble "'.$inmueble->idInmueblePadre->nombre.'" está por vencer en '.$this->renta->dias_notificacion_previa_renovacion.' días. Coordina con tu inquilino la finalización o renovación.';
        }

        $line2 = 'Inquilino: '.$renta->idInquilino->nombre_y_apellidos;

        return (new MailMessage)
            ->subject('Tu contrato de Renta próximo a vencer')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
	    ->line($line)
	    ->line($line2)
	    ->action('Ir a "Mis Contratos"', str_replace('api.', 'app.', url('/cuenta/mis-rentas')));
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

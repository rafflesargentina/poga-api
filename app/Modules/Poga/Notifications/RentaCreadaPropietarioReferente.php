<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Renta;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RentaCreadaPropietarioReferente extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Renta model and the boleta de pago.
     *
     * @var Renta
     */
    protected $renta, $boleta;

    /**
     * Create a new notification instance.
     *
     * @param Renta $renta  The Renta model.
     * @param mixed $boleta Boleta de pago.
     *
     * @return void
     */
    public function __construct(Renta $renta, $boleta)
    {
        $this->renta = $renta;
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
        $inmueble = $this->renta->idInmueble;
        $unidad = $this->renta->idUnidad;

        if ($unidad) {
            $line = 'Se creó un contrato de renta para tu Unidad "'.$unidad->piso.' - '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'"';
        } else {
            $line = 'Se creó un contrato de renta para tu inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
        }

        return (new MailMessage)
            ->subject('Se creó un contrato de Renta')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
	    ->line($line)
            ->action('Ver Contrato', str_replace('api.', 'app.', url('/rentas/'.$this->renta->id)))
	    ->markdown('poga::mail.renta-creada-para-propietario', ['renta' => $this->renta, 'user' => $notifiable, 'boleta' => $this->boleta]);
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

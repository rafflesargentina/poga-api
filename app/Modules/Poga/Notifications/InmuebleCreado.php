<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Inmueble;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InmuebleCreado extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Inmueble model.
     *
     * @var Inmueble $inmueble
     */
    protected $inmueble;

    /**
     * Create a new notification instance.
     *
     * @param  Inmueble $inmueble The Inmueble model.
     *
     * @return void
     */
    public function __construct(Inmueble $inmueble)
    {
        $this->inmueble = $inmueble;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
	$inmueble = $this->inmueble;
	$inmueblePadre = $inmueble->idInmueblePadre;
	$direccion = $inmueblePadre->idDireccion;

        return (new MailMessage)
            ->subject('Registraste un nuevo Inmueble')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line('Creaste el Inmueble nombrado "'.$inmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')).'.')
            ->action('Ir a "Mis Inmuebles"', str_replace('api.', 'app.', url('/cuenta/mis-inmuebles')));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

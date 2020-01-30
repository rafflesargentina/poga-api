<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Unidad;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UnidadCreada extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Unidad model.
     *
     * @var Unidad $unidad
     */
    protected $unidad;

    /**
     * Create a new notification instance.
     *
     * @param Unidad $unidad The Unidad model.
     *
     * @return void
     */
    public function __construct(Unidad $unidad)
    {
        $this->unidad = $unidad;
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
        $inmueblePadre = $this->unidad->idInmueblePadre;
	$direccion = $inmueblePadre->idDireccion;
	$tipoInmueble = $this->unidad->idInmueble->idTipoInmueble->tipo;

        return (new MailMessage)
            ->subject('Registraste el '.$tipoInmueble.' nº '.$this->unidad->numero)
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line('Registraste el '.$tipoInmueble.' piso '.$this->unidad->piso.' nº '.$this->unidad->numero.' para el inmueble "'.$inmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).'.')
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

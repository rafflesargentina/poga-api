<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\{ Inmueble, User };

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InmuebleCreadoParaAdminPoga extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Inmueble and User models.
     *
     * @var Inmueble $inmueble
     * @var User     $user
     */
    protected $inmueble, $user;

    /**
     * Create a new notification instance.
     *
     * @param  Inmueble $inmueble The Inmueble model.
     * @param  User     $user     The User model.
     *
     * @return void
     */
    public function __construct(Inmueble $inmueble, User $user)
    {
        $this->inmueble = $inmueble;
        $this->user = $user;
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
        $persona = $this->user->idPersona;
        $tipoInmueble = $inmueble->idTipoInmueble->tipo;
        $subject = 'Registraron el inmueble "'.$inmueblePadre->nombre.'"';

        return (new MailMessage)
            ->subject($subject)
            ->greeting($subject)
            ->line('El usuario '.$persona->enum_tipo_persona === 'FISICA' ? $persona->nombre.' '.$persona->apellido.' ('.$persona->ci.')' : $persona->nombre.' ('.$persona->ruc.') registró el inmueble "'.$inmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).'.');
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

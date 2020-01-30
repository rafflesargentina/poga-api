<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\{ Unidad, User };

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UnidadCreadaParaAdminPoga extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Unidad and User models.
     *
     * @var Unidad $unidad
     * @var User   $user
     */
    protected $unidad, $user;

    /**
     * Create a new notification instance.
     *
     * @param Unidad $unidad The Unidad model.
     * @param User   $user   The User model.
     *
     * @return void
     */
    public function __construct(Unidad $unidad, User $user)
    {
        $this->unidad = $unidad;
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
        $inmueblePadre = $this->unidad->idInmueblePadre;
	$direccion = $inmueblePadre->idDireccion;
	$persona = $this->user->idPersona;
	$tipoInmueble = $this->unidad->idInmueble->idTipoInmueble->tipo;
        $subject = 'Registraron el '.$tipoInmueble.' nº '.$this->unidad->numero;

        return (new MailMessage)
            ->subject($subject)
            ->greeting($subject)
            ->line('El usuario '.$persona->enum_tipo_persona === 'FISICA' ? $persona->nombre.' '.$persona->apellido.' ('.$persona->ci.')' : $persona->nombre.' ('.$persona->ruc.') registró el '.$tipoInmueble.' piso '.$this->unidad->piso.' nº '.$this->unidad->numero.' para el inmueble "'.$inmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).'.');
	    
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

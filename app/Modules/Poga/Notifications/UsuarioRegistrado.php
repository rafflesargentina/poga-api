<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UsuarioRegistrado extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The User model.
     *
     * @var User $user
     */
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param  User $user The User model.
     *
     * @return void
     */
    public function __construct(User $user)
    {
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
        switch ($this->user->role_id) {
	case 4:
	    $btnText = 'Ya podés publicar tu inmueble';
            $btnUrl = str_replace('api.', '', url('/inmuebles/crear'));
            $line = 'Bienvenido y gracias por registrate en POGA.';
        break;
	case 3:
            $btnText = 'Accede a tu cuenta';
            $btnUrl = str_replace('api.', '', url('/cuenta'));
            $line = 'Bienvenido y gracias por registrarte en POGA.';
        break;
	default:
	    $btnText = 'Accede a tu cuenta';
            $btnUrl = str_replace('api.', '', url('/cuenta'));
            $line = '';
	}

        return (new MailMessage)
            ->subject('Gracias por registrarte en POGA')
            ->greeting('Hola '.$this->user->idPersona->nombre)
            ->line($line)
            ->action($btnText, $btnUrl);
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

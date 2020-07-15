<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegistroCompletado extends Notification implements ShouldQueue
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
        switch ($notifiable->role_id) {
	case 4:	
	    $action = str_replace('.api', 'app.', url('/inmuebles/crear'));
	    $line2 = 'Ya podés comenzar a crear contratos de renta para tus inmuebles.';
	break;
	case 3:
	    $action = str_replace('.api', 'app.', url('/cuenta'));
	    $line2 = 'Accedé a tu portal:';
        break;
	default:
	    $action = str_replace('.api', 'app.', url('/cuenta'));
	    $line2 = '';
	}

        return (new MailMessage)
            ->subject('Tu registro en POGA está completo')
	    ->greeting('Hola '.$this->user->idPersona->nombre)
	    ->line('Gracias por completar tu registro en POGA')
            ->line($line2)
            ->action('Comienza ahora', $action);
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

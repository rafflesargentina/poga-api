<?php

namespace Raffles\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)
		->subject('Reestablece tu contraseña en '.env('APP_NAME'))
		->greeting('Hola '.$notifiable->idPersona->nombre)
		->line('Estás recibiendo esta notificación porque recibimos una solicitud de reestablecimiento de contraseña para tu cuenta.')
		->line('El link de reestablecimiento expirá en '.config('auth.passwords.users.expire').' minutos.')
		->line('Si no realizaste esta solicitud, por favor ignora este mensaje')
		->action('Reestablece tu contraseña', url(config('app.url').route('password.reset', $this->token, false)));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

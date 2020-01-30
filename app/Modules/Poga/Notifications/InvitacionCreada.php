<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Persona;

use Gr8Shivam\SmsApi\Notifications\SmsApiChannel;
use Gr8Shivam\SmsApi\Notifications\SmsApiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InvitacionCreada extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Persona model.
     *
     * @var Persona $persona
     */
    protected $persona;

    /**
     * El remitente (Persona model).
     *
     * @var Persona $remitente
     */
    protected $remitente;

    /**
     * Create a new notification instance.
     *
     * @param Persona $persona   The Persona model.
     * @param Persona $remitente El remitente.
     *
     * @return void
     */
    public function __construct(Persona $persona, $remitente = null)
    {
        $this->persona = $persona;
        $this->remitente = $remitente;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', SmsApiChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $persona = $this->persona;
        $remitente = $this->remitente;

	if ($remitente) {
            $line1 = ($remitente->enum_tipo_persona === 'FISICA' ? $remitente->nombre.' '.$remitente->apellido : $remitente->nombre).' te invita a formar parte de POGA.';
        } else {
            $line1 = 'Fuiste invitado a formar parte de POGA.';
	}

	$line2 = 'POGA es la plataforma digital que hace el alquiler simple y transparente: https://www.poga.com.py';

        return (new MailMessage)
            ->subject('Tenés una invitación pendiente')
            ->greeting('Hola '.$persona->nombre)
	    ->line($line1)
	    ->line($line2)
            ->action('Ir a "Completar Registro"', str_replace('api.', 'app.', str_replace('api.', 'app.', url('/registro-invitado/'.$persona->user->codigo_validacion))));
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

    public function toSmsApi($notifiable)
    {
        $persona = $this->persona;
        $remitente = $this->remitente;
        if ($remitente) {
            return (new SmsApiMessage)
	       ->content($remitente->nombre.' '.$remitente->apellido.' te invita a formar parte de POGA. Registrate en: '.str_replace('api.', 'app.', url('/registro-invitado/'.$persona->user->codigo_validacion)));
	} else {
            return (new SmsApiMessage)
               ->content('Te invitaron a formar parte de POGA. Registrate en: '.str_replace('api.', 'app.', url('/registro-invitado/'.$persona->user->codigo_validacion)));
	}
    }
}

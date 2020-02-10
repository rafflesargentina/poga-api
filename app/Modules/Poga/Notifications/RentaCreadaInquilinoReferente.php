<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Renta;

use Gr8Shivam\SmsApi\Notifications\SmsApiChannel;
use Gr8Shivam\SmsApi\Notifications\SmsApiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RentaCreadaInquilinoReferente extends Notification implements ShouldQueue
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
        return ['mail', SmsApiChannel::class];
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
        try {
            $inmueble = $this->renta->idInmueble;
            $unidad = $this->renta->idUnidad;

            if ($unidad) {
                $line = 'Se te asignó un contrato de renta para la Unidad "'.$unidad->piso.' - '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'"';
            } else {
                $line = 'Se te asignó un contrato de renta para la inmueble "'.$inmueble->idInmueblePadre->nombre.'"';
            }

            return (new MailMessage)
                ->subject('Se te asignó un contrato de Renta')
                ->greeting('Hola '.$notifiable->idPersona->nombre)
	        ->line($line)
                ->action('Ver Contrato', str_replace('api.', 'app.', url('/rentas/'.$this->renta->id)))
		->markdown('poga::mail.renta-creada-para-inquilino', ['renta' => $this->renta, 'user' => $notifiable, 'boleta' => $this->boleta]);
        } catch (\Exception $e) {

        }
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

    public function toSmsApi($notifiable)
    {
	$boleta = $this->boleta;
	$renta = $this->renta;
	$propietario = $renta->idInmueble->idPropietarioReferente->idPersona;

	if ($this->renta->vigente) {
            $content = normalize('Fuiste asociado a un contrato de renta por '.$propietario->nombre.' '.$propietario->apellido.', ve mas detalles en '.str_replace('api.', 'app.', url('/rentas/'.$renta->id)));
            return (new SmsApiMessage)
                ->content($content);
	} else {
            $content = normalize('Fuiste asociado a un contrato de renta que genero unos pagos pendientes, conoce cuanto pagar en '.str_replace('api.', 'app.', url('/realiza-un-pago/'.$boleta['debt']['docId'])));
            return (new SmsApiMessage)
                ->content($content);
        }   
    }
}

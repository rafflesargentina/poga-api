<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Renta;

use Gr8Shivam\SmsApi\Notifications\SmsApiChannel;
use Gr8Shivam\SmsApi\Notifications\SmsApiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RentaPorFinalizarInquilinoReferente extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Renta model:
     *
     * @var Renta
     */
    protected $renta;

    /**
     * Create a new notification instance.
     *
     * @param Renta $renta  The Renta model.
     *
     * @return void
     */
    public function __construct(Renta $renta)
    {
        $this->renta = $renta;
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
	$renta = $this->renta;    
	$inmueble = $this->renta->idInmueble;
        $unidad = $this->renta->idUnidad;

        if ($unidad) {
            $line = 'El contrato de renta para el'.$unidad->tipo.' '.$unidad->piso.' nº '.$unidad->numero.' del Inmueble "'.$unidad->idInmueblePadre->nombre.'" está por vencer en '.$renta->dias_notificacion_previa_renovacion.' días. Coordina con tu propietario la finalización o renovación.';
        } else {
            $line = 'El contrato de renta para el inmueble "'.$inmueble->idInmueblePadre->nombre.'" está por vencer en '.$this->renta->dias_notificacion_previa_renovacion.' días. Coordina con tu propietario la finalización o renovación.';
        }

        $line2 = 'Propietario: '.$inmueble->idPropietarioReferente->idPersona->nombre_y_apellidos;

        return (new MailMessage)
            ->subject('Contrato próximo a vencer')
            ->greeting('Hola '.$this->renta->idInmueble->idPropietarioReferente->idPersona->nombre)
	    ->line($line)
	    ->line($line2)
	    ->action('Ir a "Mis Contratos"', str_replace('api.', 'app.', url('/cuenta/mis-rentas')));
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
        $renta = $this->renta;
        $inmueble = $renta->idInmueble;
        $unidad = $renta->idUnidad;

        if ($unidad) {
            return (new SmsApiMessage)
                ->content('Tu contrato de renta para el '.$unidad->tipo.' '.' piso '.$unidad->piso.' nro '.$unidad->numero.' del inmueble "'.$unidad->idInmueblePadre->nombre.'", esta proximo a vencer. Ver detalles en: '.str_replace('api.', 'app.', url('/cuenta/mis-rentas')));
        } else {
            return (new SmsApiMessage)
                ->content('Tu contrato de renta para el inmueble "'.$inmueble->idInmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).', esta por vencer. Ver detalles en: '.str_replace('api.', 'app.', url('/cuenta/mis-rentas')));
        }
    }
}

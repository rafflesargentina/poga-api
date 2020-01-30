<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Gr8Shivam\SmsApi\Notifications\SmsApiChannel;
use Gr8Shivam\SmsApi\Notifications\SmsApiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PagareRentaVencidoDeudor extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Pagare model and the boleta object.
     *
     * @var Pagare
     * @var mixed
     */
    protected $pagare, $boleta;

    /**
     * Create a new notification instance.
     *
     * @param Pagare $pagare  The Pagare model.
     * @param mixed  $boleta  La boleta de pago.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, $boleta)
    {
        $this->pagare = $pagare;
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
	$pagare = $this->pagare;
        $inmueble = $pagare->idInmueble;
	$unidad = $pagare->idUnidad;
	$acreedor = $pagare->idPersonaAcreedora;

        if ($unidad) {
            $line1 = 'El pago de renta pendiente por el contrato del '.$unidad->idInmueble->idTipoInmueble->tipo.' '.$unidad->piso.' nº '.$unidad->numero.'" del Inmueble "'.$unidad->idInmueblePadre->nombre.'" ha vencido. A partir de hoy se podrían generar multas.';
        } else {
            $line1 = 'El pago de renta para el inmueble "'.$inmueble->idInmueblePadre->nombre.'" ha vencido. A partir de hoy se podrían generar multas.';
        }

        $line2 = 'Propietario: '.($acreedor->enum_tipo_persona === 'FISICA' ? ($acreedor->nombre.' '.$acreedor->apellido.' ('.$acreedor->ci.')') : $acreedor->nombre.' ('.$acreedor->ruc.')');
	$line3 = 'Monto: '.number_format($pagare->monto,0,',','.').' '.$pagare->idMoneda->abbr;
	$line4 = 'Vencimiento: '.Carbon::parse($pagare->fecha_vencimiento)->format('d/m/Y');
	$line5 = 'Multa por día: '.number_format($pagare->idRenta->monto_multa_dia,0,',','.').' '.$pagare->idRenta->idMoneda->abbr;
	$line6 = 'Comparta el siguiente link con el inquilino para la realización de pagos: '.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id));

        return (new MailMessage)
            ->subject('Pago de renta vencido: '.Carbon::parse($this->pagare->fecha_vencimiento)->format('m/Y'))
            ->greeting('Hola '.$notifiable->idPersona->nombre)
	    ->line($line1)
	    ->line($line2)
	    ->line($line3)
	    ->line($line4)
	    ->line($line5)
	    ->line($line6)
            ->action('Ir a "Mis Pagos"', str_replace('api.', 'app.', url('/cuenta/mis-pagos')));
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
	$pagare = $this->pagare;

        return (new SmsApiMessage)
            ->content('El pago pendiente de renta vencio el '.Carbon::parse($pagare->fecha_vencimiento)->format('d/m/Y').',  puede incluir multas, realiza tu pago en: '.str_replace('api.', 'app.', url('realiza-un-pago/'.$pagare->id)));
    }
}

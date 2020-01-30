<?php

namespace Raffles\Modules\Poga\Notifications;

use Raffles\Modules\Poga\Models\Renta;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RentaFinalizadaParaAdminPoga extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The Renta model.
     *
     * @var Renta
     */
    protected $renta;

    /**
     * Create a new notification instance.
     *
     * @param Renta $renta The Renta model.
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
        return ['mail'];
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
	$inmueble = $renta->idInmueble;
	$unidad = $renta->idUnidad;

	if ($unidad) {
	    $direccion = $unidad->idInmueblePadre->idDireccion;
	    $line = 'El contrato de renta para el '.$unidad->tipo.' '.' piso '.$unidad->piso.' nº '.$unidad->numero.' del inmueble "'.$unidad->idInmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).', ha finalizado.'; 
	} else {
            $direccion = $inmueble->idInmueblePadre->idDireccion;
            $line = 'El contrato de renta para el inmueble "'.$inmueble->idInmueblePadre->nombre.'", ubicado en '.$direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->numeracion : '')).', ha finalizado.';
	}

	$line2 = 'Propietario: '.$inmueble->idPropietarioReferente->idPersona->nombre_y_apellidos;
	$line3 = 'Inquilino: '.$renta->idInquilino->nombre_y_apellidos;
        $line4 = 'Fondo de garantía: '.number_format($renta->garantia,0,',','.').' '.$renta->idMoneda->abbr;
	$line5 = 'Utilizado del fondo de Garantía: '.number_format($renta->monto_descontado_garantia_finalizacion_contrato,0,',','.').' '.$renta->idMoneda->abbr;
        $line6 = 'Detalle de uso del depósito de Garantía: '.$renta->motivo_descuento_garantia;
	$line7 = 'Monto a ser reembolsado al Inquilino: '.number_format($renta->garantia - $renta->monto_descontado_garantia_finalizacion_contrato,0,',','.').' '.$renta->idMoneda->abbr;
        $line8 = 'Observaciones: '.$renta->observacion;

        return (new MailMessage)
            ->subject('Contrato de renta finalizado')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
	    ->line($line)
	    ->line($line2)
	    ->line($line3)
	    ->line($line4)
	    ->line($line5)
	    ->line($line6)
	    ->line($line7)
	    ->line($line8)
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
}

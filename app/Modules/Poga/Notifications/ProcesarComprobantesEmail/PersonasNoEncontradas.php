<?php

namespace Raffles\Modules\Poga\Notifications\ProcesarComprobantesEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PersonasNoEncontradas extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The comprobante object.
     *
     * @var Comprobante
     */
    protected $comprobante;

    /**
     * Create a new notification instance.
     *
     * @param  array $comprobante The User model.
     *
     * @return void
     */
    public function __construct($comprobante)
    {
        $this->comprobante = $comprobante;
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
        $comprobante = $this->comprobante;

        $line1 = 'No se encontraron personas activas con los datos del comprobante procesado:';
        $line2 = 'Importe: '.$comprobante->importe;
        $line3 = 'Nro. de Cuenta Débito: '.$comprobante->nroCuenta;
        $line4 = 'Cliente: '.$comprobante->cliente;
        $line5 = 'Nro. de Cuenta Crédito: '.$comprobante->nroCuentaCredito;
        $line6 = 'Fecha de Operación: '.$comprobante->fechaOperacion;
        return (new MailMessage)
            ->subject('Procesar Comprobantes via Email: Personas no encontradas')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line($line1)
            ->line($line2)
            ->line($line3)
            ->line($line4)
            ->line($line5)
            ->line($line6);
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

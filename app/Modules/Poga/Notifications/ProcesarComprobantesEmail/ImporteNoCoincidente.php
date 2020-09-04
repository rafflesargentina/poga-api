<?php

namespace Raffles\Modules\Poga\Notifications\ProcesarComprobantesEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ImporteNoCoincidente extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The comprobante object.
     *
     * @var Comprobante
     */
    protected $comprobante;

    /**
     * The pagare.
     *
     * @var Pagare
     */
    protected $pagare;

    /**
     * Create a new notification instance.
     *
     * @param  array $boleta The User model.
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

        $line1 = 'El importe procesado de '.$comprobante->importe.' no coincide con el comprobante:';
        $line2 = '';
        $line3 = '';
        $line4 = '';
        $line5 = '';
        return (new MailMessage)
            ->subject('Procesar Comprobantes via Email: Importe no coincidente')
            ->greeting('Hola '.$notifiable->idPersona->nombre)
            ->line($line);
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

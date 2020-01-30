<?php

namespace Raffles\Modules\Poga\Mail;

use Raffles\Modules\Poga\Models\{ Pagare, User };

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PagoConfirmadoDeudor extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The Pagare and User model.
     *
     * @var Pagare
     * @var User
     */
    protected $pagare, $user, $boleta;

    /**
     * Create a new notification instance.
     *
     * @param Pagare $pagare The Pagare model.
     * @param User  $user  The User model.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, User $user, $boleta)
    {
        $this->pagare = $pagare;
	$this->user = $user;
	$this->boleta = $boleta;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('poga::mail.pago-confirmado-para-deudor', ['pagare' => $this->pagare, 'user' => $this->user, 'boleta' => $this->boleta]);
    }
}

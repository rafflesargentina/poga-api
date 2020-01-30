<?php

namespace Raffles\Modules\Poga\Mail;

use Raffles\Modules\Poga\Models\{ Pagare, User };

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PagoTransferidoAcreedor extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The Pagare and User model.
     *
     * @var Pagare
     * @var User
     */
    protected $pagare, $user;

    /**
     * Create a new notification instance.
     *
     * @param Pagare $pagare The Pagare model.
     * @param User  $user  The User model.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, User $user)
    {
        $this->pagare = $pagare;
	$this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('poga::mail.pago-transferido-para-acreedor', ['pagare' => $this->pagare, 'user' => $this->user]);
    }
}

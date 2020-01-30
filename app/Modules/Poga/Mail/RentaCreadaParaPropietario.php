<?php

namespace Raffles\Modules\Poga\Mail;

use Raffles\Modules\Poga\Models\{ Renta, User };

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RentaCreadaParaPropietario extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The Renta and User model.
     *
     * @var Renta
     * @var User
     */
    protected $renta, $user, $boleta;

    /**
     * Create a new notification instance.
     *
     * @param Renta $renta The Renta model.
     * @param User  $user  The User model.
     *
     * @return void
     */
    public function __construct(Renta $renta, User $user, $boleta)
    {
        $this->renta = $renta;
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
        return $this->markdown('poga::mail.renta-creada-para-propietario', ['renta' => $this->renta, 'user' => $this->user, 'boleta' => $this->boleta]);
    }
}

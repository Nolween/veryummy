<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AcceptedIngredient extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<string,string>
     */
    public $informations;

    /**
     * Create a new message instance.
     *
     * @param  array<string, string>  $informations
     */
    public function __construct(array $informations)
    {
        $this->informations = $informations;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@randidea.fr', 'Veryummy')->subject("Proposition d'ingrédient acceptée!")->markdown('emails.ingredient.accepted');
    }
}

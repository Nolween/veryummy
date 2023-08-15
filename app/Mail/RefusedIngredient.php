<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefusedIngredient extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<string,string>
     */
    public $informations;

    /**
     * Create a new message instance.
     *
     * @param array<string, string> $informations
     * @return void
     */
    public function __construct($informations)
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
        return $this->from('noreply@randidea.fr', 'Veryummy')->subject("Proposition d'ingrédient refusée")->markdown('emails.ingredient.refused');
    }
}

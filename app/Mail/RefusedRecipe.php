<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefusedRecipe extends Mailable
{
    use Queueable, SerializesModels;

    public $informations;

    /**
     * Create a new message instance.
     *
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
        return $this->from('noreply@randidea.fr', 'Veryummy')->subject('Recette supprimée')->markdown('emails.recipe.refused');
    }
}

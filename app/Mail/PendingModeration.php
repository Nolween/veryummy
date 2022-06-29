<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingModeration extends Mailable
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
        return $this->from('noreply@randidea.fr', 'Veryummy')->subject("Rapport de modération quotidien")->markdown('emails.moderation.pending');
    }
}

<?php

namespace App\Mail;

use App\Models\Absence;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbsenceRejected extends Mailable
{
    use Queueable, SerializesModels;

    public Absence $absence;

    public function __construct(Absence $absence)
    {
        $this->absence = $absence;
    }

    public function build()
    {
        return $this->subject('❌ Votre demande de congé a été refusée')
                    ->view('emails.absence_rejected');
    }
}
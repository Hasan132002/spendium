<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\FamilyMemberInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FamilyInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FamilyMemberInvitation $invitation)
    {
        $this->invitation->loadMissing('family', 'inviter');
    }

    public function build()
    {
        $acceptUrl = route('family.invite.show', ['token' => $this->invitation->token]);

        return $this->subject("You've been invited to join {$this->invitation->family->name} on " . config('app.name'))
            ->view('emails.family_invitation')
            ->with([
                'invitation' => $this->invitation,
                'acceptUrl'  => $acceptUrl,
            ]);
    }
}

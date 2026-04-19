<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvitationAccepted extends Notification
{
    use Queueable;

    public function __construct(public User $newMember, public string $role)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'invitation.accepted',
            'title'   => 'New family member joined',
            'message' => $this->newMember->name . ' joined as ' . $this->role . '.',
            'icon'    => 'bi-person-plus',
            'url'     => url('/admin/family/members'),
            'meta'    => ['user_id' => $this->newMember->id, 'role' => $this->role],
        ];
    }
}

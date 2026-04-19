<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    use Queueable;

    public function __construct(public User $follower)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'user.follower',
            'title'   => 'New follower',
            'message' => $this->follower->name . ' started following you.',
            'icon'    => 'bi-person-plus',
            'url'     => url('/admin/users/' . $notifiable->id . '/followers'),
            'meta'    => ['follower_id' => $this->follower->id],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Reaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReaction extends Notification
{
    use Queueable;

    public function __construct(public Reaction $reaction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isPost = $this->reaction->reactable_type === \App\Models\Post::class;

        return [
            'type'    => 'post.reaction',
            'title'   => 'New ' . ($this->reaction->type ?? 'reaction'),
            'message' => ($this->reaction->user?->name ?? 'Someone') . ' reacted to your ' . ($isPost ? 'post' : 'comment') . '.',
            'icon'    => 'bi-heart',
            'url'     => $isPost ? url('/admin/posts/' . $this->reaction->reactable_id) : url('/admin/posts'),
            'meta'    => ['reactable_id' => $this->reaction->reactable_id, 'reactable_type' => $this->reaction->reactable_type],
        ];
    }
}

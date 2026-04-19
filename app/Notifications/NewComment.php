<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewComment extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'post.comment',
            'title'   => 'New comment on your post',
            'message' => ($this->comment->user?->name ?? 'Someone') . ' commented: "' . \Illuminate\Support\Str::limit($this->comment->content, 60) . '"',
            'icon'    => 'bi-chat-dots',
            'url'     => url('/admin/posts/' . $this->comment->post_id),
            'meta'    => ['post_id' => $this->comment->post_id, 'comment_id' => $this->comment->id],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\GoalContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GoalContributionAdded extends Notification
{
    use Queueable;

    public function __construct(public GoalContribution $contribution)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $goal = $this->contribution->goal_id
            ? \App\Models\Goal::find($this->contribution->goal_id)
            : null;

        return [
            'type'    => 'goal.contribution.added',
            'title'   => 'Goal contribution received',
            'message' => 'A contribution of ' . config('app.currency_symbol', '$') . number_format((float) $this->contribution->amount, 2) . ' was added to "' . ($goal?->title ?? 'a goal') . '".',
            'icon'    => 'bi-bullseye',
            'url'     => $goal ? url('/admin/goals/' . $goal->id . '/progress') : url('/admin/goals/personal'),
            'meta'    => ['goal_id' => $this->contribution->goal_id, 'contribution_id' => $this->contribution->id],
        ];
    }
}

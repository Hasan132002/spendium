<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetThresholdReached extends Notification
{
    use Queueable;

    public function __construct(public Budget $budget, public int $percentUsed)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $category = $this->budget->category?->name ?? 'budget';

        return [
            'type'    => $this->percentUsed >= 100 ? 'budget.exceeded' : 'budget.threshold',
            'title'   => $this->percentUsed >= 100 ? 'Budget exceeded' : 'Budget at ' . $this->percentUsed . '%',
            'message' => 'Your ' . $category . ' budget has reached ' . $this->percentUsed . '% of the allocated amount.',
            'icon'    => $this->percentUsed >= 100 ? 'bi-exclamation-triangle' : 'bi-exclamation-circle',
            'url'     => url('/admin/budget/assigned'),
            'meta'    => ['budget_id' => $this->budget->id, 'percent' => $this->percentUsed],
        ];
    }
}

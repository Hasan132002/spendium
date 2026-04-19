<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseApproved extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'expense.approved',
            'title'   => 'Expense approved',
            'message' => 'Your expense "' . $this->expense->title . '" was approved.',
            'icon'    => 'bi-check-circle',
            'url'     => url('/admin/expenses/my'),
            'meta'    => ['expense_id' => $this->expense->id],
        ];
    }
}

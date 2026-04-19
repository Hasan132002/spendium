<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseLogged extends Notification
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
            'type'    => 'expense.logged',
            'title'   => 'New expense logged',
            'message' => ($this->expense->user?->name ?? 'A member') . ' logged "' . $this->expense->title . '" for ' . config('app.currency_symbol', '$') . number_format((float) $this->expense->amount, 2),
            'icon'    => 'bi-wallet2',
            'url'     => url('/admin/expenses/family'),
            'meta'    => ['expense_id' => $this->expense->id],
        ];
    }
}

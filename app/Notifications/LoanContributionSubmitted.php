<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LoanContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanContributionSubmitted extends Notification
{
    use Queueable;

    public function __construct(public LoanContribution $contribution)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'loan.contribution.submitted',
            'title'   => 'New loan contribution',
            'message' => ($this->contribution->user?->name ?? 'A member') . ' contributed ' . config('app.currency_symbol', '$') . number_format((float) $this->contribution->amount, 2) . ' toward a loan.',
            'icon'    => 'bi-coin',
            'url'     => url('/admin/loans/' . $this->contribution->loan_id),
            'meta'    => ['contribution_id' => $this->contribution->id],
        ];
    }
}

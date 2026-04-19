<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LoanContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanContributionApproved extends Notification
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
            'type'    => 'loan.contribution.approved',
            'title'   => 'Loan contribution approved',
            'message' => 'Your contribution of ' . config('app.currency_symbol', '$') . number_format((float) $this->contribution->amount, 2) . ' was approved.',
            'icon'    => 'bi-check-circle',
            'url'     => url('/admin/loan-contributions/my'),
            'meta'    => ['contribution_id' => $this->contribution->id],
        ];
    }
}

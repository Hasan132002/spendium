<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FundRequestDeclined extends Notification
{
    use Queueable;

    public function __construct(public FundRequest $fundRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'fund_request.declined',
            'title'   => 'Fund request declined',
            'message' => 'Your request for ' . config('app.currency_symbol', '$') . number_format((float) $this->fundRequest->amount, 2) . ' was declined.',
            'icon'    => 'bi-x-circle',
            'url'     => url('/admin/fund-request/my'),
            'meta'    => ['fund_request_id' => $this->fundRequest->id],
        ];
    }
}

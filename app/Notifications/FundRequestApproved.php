<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundRequestApproved extends Notification
{
    use Queueable;

    public function __construct(public FundRequest $fundRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'fund_request.approved',
            'title'   => 'Fund request approved',
            'message' => 'Your request for ' . config('app.currency_symbol', '$') . number_format((float) $this->fundRequest->amount, 2) . ' was approved.',
            'icon'    => 'bi-check-circle',
            'url'     => url('/admin/fund-request/my'),
            'meta'    => ['fund_request_id' => $this->fundRequest->id],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fund request approved')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your fund request of ' . config('app.currency_symbol', '$') . number_format((float) $this->fundRequest->amount, 2) . ' has been approved.')
            ->action('View Details', url('/admin/fund-request/my'));
    }
}

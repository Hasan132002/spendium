<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundRequestSubmitted extends Notification
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
            'type'    => 'fund_request.submitted',
            'title'   => 'New fund request',
            'message' => $this->fundRequest->user?->name . ' requested ' . config('app.currency_symbol', '$') . number_format((float) $this->fundRequest->amount, 2),
            'icon'    => 'bi-envelope-paper',
            'url'     => url('/admin/fund-request/funds/all'),
            'meta'    => ['fund_request_id' => $this->fundRequest->id],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New fund request on ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->fundRequest->user?->name . ' has requested ' . config('app.currency_symbol', '$') . number_format((float) $this->fundRequest->amount, 2))
            ->line('Note: ' . ($this->fundRequest->note ?? '—'))
            ->action('Review Request', url('/admin/fund-request/funds/all'));
    }
}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Family Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2 style="color: #111;">You've been invited to join {{ $invitation->family->name }}</h2>

    <p>Hello{{ $invitation->name ? ' ' . $invitation->name : '' }},</p>

    <p>
        <strong>{{ $invitation->inviter->name }}</strong> has invited you to join
        <strong>{{ $invitation->family->name }}</strong> on {{ config('app.name') }} as a
        <strong>{{ ucfirst($invitation->role) }}</strong>.
    </p>

    <p>Click the button below to accept this invitation and set up your account:</p>

    <p style="text-align: center; margin: 32px 0;">
        <a href="{{ $acceptUrl }}"
           style="background: #3b82f6; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            Accept Invitation
        </a>
    </p>

    <p style="font-size: 13px; color: #666;">
        This invitation expires on
        <strong>{{ $invitation->expires_at?->format('d M Y, h:i A') }}</strong>.
    </p>

    <p style="font-size: 13px; color: #666;">
        If the button doesn't work, copy this link: <br>
        <a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a>
    </p>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">

    <p style="font-size: 12px; color: #999;">
        If you did not expect this invitation, you can safely ignore this email.
    </p>
</body>
</html>

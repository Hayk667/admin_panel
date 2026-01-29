<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Contact form message') }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .meta { background: #f4f4f4; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .meta p { margin: 4px 0; }
        .message { white-space: pre-wrap; padding: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>{{ __('Contact form') }}</h1>
    <p>{{ __('You received a message from the page') }}: <strong>{{ $pageTitle }}</strong></p>

    <div class="meta">
        <p><strong>{{ __('From') }}:</strong> {{ $senderEmail }}</p>
    </div>

    <h2>{{ __('Message') }}</h2>
    <div class="message">{{ $messageBody }}</div>
</body>
</html>

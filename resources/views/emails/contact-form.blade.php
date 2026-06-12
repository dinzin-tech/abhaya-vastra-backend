<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact Message</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4f46e5; /* nice purple/blue */
            color: #ffffff;
            padding: 20px 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 22px;
        }

        .email-body {
            padding: 30px;
        }

        .email-body h2 {
            color: #111827;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .email-body p {
            color: #374151;
            line-height: 1.6;
            margin: 8px 0;
        }

        .email-body .label {
            font-weight: bold;
            color: #111827;
        }

        .email-footer {
            background-color: #f4f6f8;
            text-align: center;
            padding: 15px 30px;
            font-size: 13px;
            color: #6b7280;
        }

        .email-footer a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="email-body">
            <h2>New Contact Message</h2>
            <p><span class="label">Name:</span> {{ $data['name'] }}</p>
            <p><span class="label">Email:</span> {{ $data['email'] }}</p>
            <p><span class="label">Message:</span> {{ $data['message'] }}</p>
        </div>

        <div class="email-footer">
            <p>Sent via <strong>{{ config('app.name') }}</strong> website</p>
            <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
        </div>
    </div>
</body>
</html>

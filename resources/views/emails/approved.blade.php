<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBLAN25 Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
        }

        .header {
            background: linear-gradient(135deg, #32b37d 0%, #11998e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .panel {
            background: #f8fafc;
            border-left: 4px solid #32b37d;
            padding: 20px;
            margin: 20px 0;
        }

        .button {
            background: #32b37d;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>✅ You're In, {{ $user->name }}!</h1>
            <p>Your registration for <strong>MBLAN25</strong> has been approved. Get ready for the adventure!</p>
        </div>

        <div class="content">
            <div class="panel">
                <h3>🎟️ You're officially on the list!</h3>
                <p>We've reviewed and approved your signup. Now it’s time to connect and prepare for the event.</p>
            </div>

            <h2>📍 Event Details</h2>
            <ul>
                <li><strong>Event:</strong> {{ $signup->edition->name }} ({{ $signup->edition->year }})</li>
                <li><strong>Location:</strong> Tachtig Bunderweg 6 - Sibculo</li>
                <li><strong>Check-in:</strong> {{ $signup->schedules->first()->name }} 10.00</li>
            </ul>

            <h2>📣 Join the Community</h2>
            <p>We're using Discord to communicate, coordinate, and hype the event. Join us there:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $discordLink }}" class="button">Join Discord Server</a>
            </p>

            <h2>🧳 What Should You Bring?</h2>
            <ul>
                <li>🛌 Sleeping gear if you're camping</li>
                <li>💻 A gaming-ready setup: laptop or PC, monitor/mounts, keyboard, mouse, headset</li>
                <li>😄 A good attitude—and your best jokes!</li>
            </ul>

            <h2>🧳 What can you expect:</h2>
            <ul>
                <li>🪑 Desks and Chairs provided, but bring your own chair if you want something extra comfy</li>
                <li>🥤 Desk-mounted beverage holders</li>
                <li>🌐 Network cable, Wi-Fi, and power are all provided—no need to bring your own</li>
                <li>🎸 A full Rock Band stage — team up and rock out with other guests</li>
                <li>🧊 Extra amenities in the LAN barn: refrigerator, coffee machines, and a chill-out corner</li>
                <li>⛺ A comfortable camping field to pitch your tent and relax</li>
            </ul>

            <hr style="margin: 30px 0;">

            <p>If you have any questions, email us at <strong>organisation@mblan.nl</strong>.</p>

            <p>Let’s make this event unforgettable. See you there!</p>

            <p><strong>Bart, Martin & Corneel</strong><br>
                <em>The Mblan Team</em> 🚀
            </p>
        </div>

        <div style="background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280;">
            This email was sent to {{ $user->email }}. Need help? Contact us at organisation@mblan.nl.
        </div>
    </div>
</body>

</html>

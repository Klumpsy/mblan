<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mblan Welcome Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            background: #fff;
        }

        .header {
            background: linear-gradient(135deg, #7366ea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .panel {
            background: #f8fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
        }

        .button {
            background: #667eea;
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
            <h1>🎉 Welcome to Mblan, {{ $user->name }}</h1>
            <p>We're absolutely thrilled to have you join us for <strong>MBLAN25</strong>! Your signup has been
                successfully received.</p>
        </div>

        <div class="content">
            <div class="panel">
                <h3>⏳ What's Next?</h3>
                <p>Your signup is currently being reviewed. We'll approve your registration as soon as
                    possible!</p>
            </div>

            <h2>📋 Your Signup Details</h2>
            <ul>
                <li><strong>Event:</strong> {{ $signup->edition->name }} ({{ $signup->edition->year }})</li>
                <li><strong>Status:</strong> ⏳ Pending Review</li>
                <li><strong>Campsite Stay:</strong>
                    {{ $signup->stays_on_campsite ? '🏕️ Yes, I\'ll be camping!' : '❌ No camping' }}
                </li>
                <li><strong>Barbecue:</strong>
                    {{ $signup->joins_barbecue ? '🍖 Count me in for the BBQ!' : '❌ No BBQ for me' }}
                </li>
                <li><strong>Barbecue:</strong>
                    {{ $signup->joins_pizza ? '🍕 Count me in for the Pizza!' : '❌ No Pizza for me' }}
                </li>
                <li><strong>Tshirt:</strong>
                    {{ $signup->wants_tshirt ? '👕 I ordered a MBLAN25 T-shirt (size: {{ $signup->tshirt_size }}) - text: {{ $signup->tshirt_text }}.' : '❌ No T-shirt for me' }}
                </li>
            </ul>

            <h2>🎯 Your Selected Activities</h2>
            <ul>
                @forelse ($signup->schedules as $schedule)
                    <li>{{ $schedule->name }}</li>
                @empty
                    <li>No activities selected.</li>
                @endforelse
            </ul>

            <h2>🥤 Your Beverage Preferences</h2>
            <ul>
                @forelse ($signup->beverages as $beverage)
                    <li>{{ $beverage->name }}</li>
                @empty
                    <li>No preferences selected.</li>
                @endforelse
            </ul>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $signedUrl }}" class="button">Visit Your Dashboard</a>
            </p>


            <h2>🚀 What Happens Next?</h2>
            <p><strong>📋 Review Process (as soon as possible)</strong><br>
                we will carefully review your signup.</p>

            <p><strong>📧 Confirmation Email Coming Soon</strong><br>
                Once approved, you'll receive detailed information about:</p>
            <ul>
                <li>Discord channel</li>
                <li>Final event schedules and timings</li>
                <li>Location details and directions</li>
                <li>What to bring and preparation tips</li>
            </ul>

            <hr style="margin: 30px 0;">

            <p><strong>Questions?</strong> Don't hesitate to reach out to us at <strong>organisation@mblan.nl</strong>
            </p>

            <p>Thank you for choosing MBLAN25 for your next adventure!</p>

            <p><strong>Bart, Martin & Corneel</strong><br>
                <em>The Mblan Team</em> 🌟
            </p>
        </div>

        <div style="background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280;">
            This email was sent to test@example.com. If you have questions, contact us at organisation@mblan.nl
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .order-info h2 {
            margin: 0 0 20px;
            color: #1a202c;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 140px;
        }
        .info-value {
            color: #1a202c;
        }
        .track-button {
            display: block;
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
            padding: 18px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }
        .track-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .feature {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .feature-icon {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .feature-text {
            font-size: 14px;
            color: #4a5568;
            font-weight: 600;
        }
        .footer {
            background: #1a202c;
            color: white;
            text-align: center;
            padding: 30px;
        }
        .footer p {
            margin: 5px 0;
            opacity: 0.8;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 20px;
                border-radius: 10px;
            }
            .header {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .content {
                padding: 30px 20px;
            }
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 KUOSHT GPS Tracking</h1>
            <p>Your order is on its way!</p>
        </div>

        <div class="content">
            <div class="order-info">
                <h2>Order Details</h2>
                <div class="info-row">
                    <span class="info-label">🏷️ Order Number:</span>
                    <span class="info-value"><strong>{{ $order->order_number }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">👤 Customer:</span>
                    <span class="info-value">{{ $order->customer_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📍 Delivery Address:</span>
                    <span class="info-value">{{ $order->delivery_address }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Scheduled Date:</span>
                    <span class="info-value">{{ $order->scheduled_date->format('F d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🚚 Courier:</span>
                    <span class="info-value">{{ $order->courier->name }}</span>
                </div>
            </div>

            <a href="{{ $trackingUrl }}" class="track-button">
                🗺️ Track Your Order in Real-Time
            </a>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">📍</div>
                    <div class="feature-text">Live GPS Tracking</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">⏱️</div>
                    <div class="feature-text">Estimated Arrival</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🗺️</div>
                    <div class="feature-text">Interactive Map</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-text">Real-Time Updates</div>
                </div>
            </div>

            <p style="margin-top: 30px; padding: 20px; background: #fff8dc; border-left: 4px solid #fbbf24; border-radius: 5px; font-size: 14px;">
                <strong>💡 Tip:</strong> Save this link to track your order anytime: <br>
                <a href="{{ $trackingUrl }}" style="color: #667eea; word-break: break-all;">{{ $trackingUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p><strong>KUOSHT GPS Tracking System</strong></p>
            <p>Your reliable delivery partner</p>
            <p style="margin-top: 15px; font-size: 12px;">
                If you have any questions, please contact us at support@kuosht.com
            </p>
        </div>
    </div>
</body>
</html>

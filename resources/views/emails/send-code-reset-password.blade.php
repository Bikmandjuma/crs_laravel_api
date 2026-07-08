<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            margin: 0;
            padding: 40px 0;
        }
        .email-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .email-card {
            background: #1e293b;
            width: 100%;
            max-width: 480px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            padding: 40px 30px;
            text-align: center;
            border-top: 6px solid #ff6b00;
        }
        .email-card h2 {
            color: #ff8c42;
            margin-bottom: 12px;
            font-size: 22px;
            font-weight: 700;
        }
        .email-card p {
            color: #cbd5e1;
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .code-container {
            background: linear-gradient(135deg, #1f2937, #0f172a);
            border: 2px dashed #ff8c42;
            border-radius: 12px;
            padding: 18px 25px;
            display: inline-block;
            font-size: 26px;
            font-weight: bold;
            color: #ff6b00;
            letter-spacing: 6px;
            margin-bottom: 25px;
        }
        .footer {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="email-wrapper">
        <div class="email-card">
            <h2>Welcome to {{ config('app.name') }}</h2>

            <p>
                We received your request to reset your account password.
                Please use the verification code below:
            </p>
            
            <div class="code-container">
                {{ $code }}
            </div>

            <p class="footer">
                This code is valid for one hour. If you did not request this,
                you can safely ignore this email.
            </p>
        </div>
    </div>
    
</body>
</html>
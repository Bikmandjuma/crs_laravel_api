<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create a new account</title>
    <style type="text/css">
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #fdfbfb, #ebedee);
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
            background: papayawhip;
            width: 100%;
            max-width: 480px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            text-align: center;
            border-top: 6px solid #ff6b00;
        }
        .email-card h2 {
            color: #ff6b00;
            margin-bottom: 8px;
            font-size: 22px;
            font-weight: 700;
        }
        .email-card h3 {
            color: #222;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 600;
        }
        .email-card p {
            color: #666;
            font-size: 15px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .code-container {
            background: linear-gradient(135deg, #fff8f2, #ffe9d9);
            border: 2px dashed #ff8c42;
            border-radius: 10px;
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
            color: #999;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="email-wrapper">
        <div class="email-card">
            <h2>Welcome to {{ config('app.name') }}</h2>
            <p>We have received your request to create a new account. Use the code below to complete your registration:</p>
            
            <div class="code-container">
                {{ $code }}
            </div>

            <p class="footer">This code is valid for one hour from the time it was sent. If you did not request this code, please ignore this email.</p>
        </div>
    </div>
    
</body>
</html>
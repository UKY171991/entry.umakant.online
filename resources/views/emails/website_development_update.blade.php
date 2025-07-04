<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100%;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            background-color: #4A90E2;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content p {
            margin-bottom: 15px;
        }
        .footer {
            background-color: #f0f0f0;
            color: #777777;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #eeeeee;
        }
        .button {
            display: inline-block;
            background-color: #4A90E2;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            Website Development Update
        </div>
        <div class="content">
            <p>Dear Valued Client,</p>
            <p>This is an important update regarding your website development project. We are making great progress and wanted to share the latest.</p>
            <p>{{ $body }}</p>
            <p>We are committed to delivering a high-quality product and appreciate your continued partnership.</p>
            <p>If you have any questions or require further information, please do not hesitate to contact us.</p>
            <p>Sincerely,</p>
            <p>Your Dedicated Development Team</p>
            <p><a href="#" class="button">Visit Our Website</a></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Your Company Name. All rights reserved.
        </div>
    </div>
</body>
</html>
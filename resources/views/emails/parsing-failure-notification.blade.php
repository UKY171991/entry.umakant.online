<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>High Parsing Failure Rate Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #ffc107; color: #212529; padding: 15px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
        .footer { background-color: #6c757d; color: white; padding: 10px; border-radius: 0 0 5px 5px; text-align: center; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { background-color: #fff; padding: 15px; border-radius: 5px; text-align: center; border: 1px solid #dee2e6; }
        .stat-number { font-size: 24px; font-weight: bold; color: #dc3545; }
        .recommendation { background-color: #d1ecf1; padding: 15px; border-left: 4px solid #bee5eb; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>⚠️ High Email Parsing Failure Rate Alert</h2>
        </div>
        
        <div class="content">
            <p><strong>The email parsing system is experiencing a high failure rate that requires attention.</strong></p>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number">{{ $failure_rate }}%</div>
                    <div>Failure Rate</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $failed_emails }}</div>
                    <div>Failed Emails</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $total_emails }}</div>
                    <div>Total Emails</div>
                </div>
            </div>

            <div class="recommendation">
                <h3>Recommendation</h3>
                <p>{{ $recommendation }}</p>
            </div>

            <h3>Suggested Actions</h3>
            <ul>
                <li>Review recent failed email transactions in the admin panel</li>
                <li>Check if banks have changed their email formats</li>
                <li>Update parsing patterns for affected banks</li>
                <li>Test parsing with sample emails</li>
                <li>Consider adding new bank-specific patterns</li>
            </ul>

            <p><strong>Timestamp:</strong> {{ $timestamp }}</p>
        </div>
        
        <div class="footer">
            <p>Email Transaction System - Automated Alert</p>
        </div>
    </div>
</body>
</html>
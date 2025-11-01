<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Connection Failure</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #fd7e14; color: white; padding: 15px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
        .footer { background-color: #6c757d; color: white; padding: 10px; border-radius: 0 0 5px 5px; text-align: center; }
        .config-details { background-color: #fff; padding: 15px; border-left: 4px solid #fd7e14; margin: 15px 0; }
        .troubleshooting { background-color: #e2e3e5; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📧 Email Connection Failure</h2>
        </div>
        
        <div class="content">
            <p><strong>An email configuration has failed to connect and requires attention.</strong></p>
            
            <div class="config-details">
                <h3>Configuration Details</h3>
                <p><strong>Configuration Name:</strong> {{ $config_name }}</p>
                <p><strong>Email Account:</strong> {{ $email_account }}</p>
                <p><strong>IMAP Host:</strong> {{ $imap_host }}</p>
                <p><strong>Error Message:</strong> {{ $error_message }}</p>
                <p><strong>Timestamp:</strong> {{ $timestamp }}</p>
            </div>

            <div class="troubleshooting">
                <h3>Troubleshooting Steps</h3>
                <ol>
                    @foreach($troubleshooting_steps as $step)
                    <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>

            <h3>Immediate Actions Required</h3>
            <ul>
                <li>Test the email configuration manually in the admin panel</li>
                <li>Verify the email account credentials</li>
                <li>Check if the email provider has updated their settings</li>
                <li>Ensure network connectivity to the IMAP server</li>
            </ul>

            <p><em>Note: Email fetching for this configuration has been temporarily disabled until the connection is restored.</em></p>
        </div>
        
        <div class="footer">
            <p>Email Transaction System - Automated Alert</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Critical Error Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 15px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
        .footer { background-color: #6c757d; color: white; padding: 10px; border-radius: 0 0 5px 5px; text-align: center; }
        .error-details { background-color: #fff; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .context-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .context-table th, .context-table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .context-table th { background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🚨 Critical Error in Email Transaction System</h2>
        </div>
        
        <div class="content">
            <p><strong>A critical error has occurred in the Email Transaction System that requires immediate attention.</strong></p>
            
            <div class="error-details">
                <h3>Error Details</h3>
                <p><strong>Component:</strong> {{ $component }}</p>
                <p><strong>Error Message:</strong> {{ $error_message }}</p>
                <p><strong>File:</strong> {{ $error_file }}</p>
                <p><strong>Line:</strong> {{ $error_line }}</p>
                <p><strong>Timestamp:</strong> {{ $timestamp }}</p>
                <p><strong>Server:</strong> {{ $server }}</p>
            </div>

            @if(!empty($context))
            <h3>Additional Context</h3>
            <table class="context-table">
                @foreach($context as $key => $value)
                <tr>
                    <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                </tr>
                @endforeach
            </table>
            @endif

            <h3>Recommended Actions</h3>
            <ul>
                <li>Check the application logs for more details</li>
                <li>Verify email configuration settings</li>
                <li>Test email connections manually</li>
                <li>Check server resources and connectivity</li>
                <li>Review recent code changes if applicable</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>Email Transaction System - Automated Alert</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 15px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
        .footer { background-color: #6c757d; color: white; padding: 10px; border-radius: 0 0 5px 5px; text-align: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background-color: #fff; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .danger { color: #dc3545; }
        .info { color: #17a2b8; }
        .summary-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .summary-table th, .summary-table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .summary-table th { background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📊 Email Transaction System - Daily Summary</h2>
            <p>{{ $date }}</p>
        </div>
        
        <div class="content">
            <h3>Processing Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number info">{{ $total_processed }}</div>
                    <div>Total Processed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number success">{{ $successful }}</div>
                    <div>Successful</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number danger">{{ $failed }}</div>
                    <div>Failed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number warning">{{ $pending }}</div>
                    <div>Pending</div>
                </div>
            </div>

            <table class="summary-table">
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Success Rate</td>
                    <td class="{{ $success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'danger') }}">{{ $success_rate }}%</td>
                </tr>
                <tr>
                    <td>Total Amount Processed</td>
                    <td class="info">₹{{ number_format($total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Income Transactions</td>
                    <td class="success">{{ $income_transactions }}</td>
                </tr>
                <tr>
                    <td>Expense Transactions</td>
                    <td class="info">{{ $expense_transactions }}</td>
                </tr>
                <tr>
                    <td>Active Email Configurations</td>
                    <td>{{ $active_configurations }}</td>
                </tr>
            </table>

            @if($success_rate < 70)
            <div style="background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0;">
                <h4>⚠️ Attention Required</h4>
                <p>The success rate is below 70%. Please review failed transactions and consider updating parsing patterns.</p>
            </div>
            @elseif($success_rate < 90)
            <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;">
                <h4>📈 Room for Improvement</h4>
                <p>The success rate could be improved. Consider reviewing recent failures for pattern updates.</p>
            </div>
            @else
            <div style="background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;">
                <h4>✅ Excellent Performance</h4>
                <p>The system is performing well with a high success rate.</p>
            </div>
            @endif

            <h3>Quick Actions</h3>
            <ul>
                <li><a href="{{ url('/email-transactions') }}">View All Transactions</a></li>
                <li><a href="{{ url('/email-configurations') }}">Manage Email Configurations</a></li>
                <li><a href="{{ url('/inbox/dashboard') }}">View Dashboard</a></li>
            </ul>
        </div>
        
        <div class="footer">
            <p>Email Transaction System - Daily Report</p>
        </div>
    </div>
</body>
</html>
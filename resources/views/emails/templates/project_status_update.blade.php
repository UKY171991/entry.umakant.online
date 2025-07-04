<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .progress-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        .progress-bar-container {
            background: #e0e0e0;
            border-radius: 10px;
            padding: 3px;
            margin: 15px 0;
        }
        .progress-bar {
            background: linear-gradient(90deg, #4caf50, #45a049);
            height: 20px;
            border-radius: 8px;
            position: relative;
            transition: width 0.3s ease;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .task-list {
            list-style: none;
            padding: 0;
        }
        .task-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .completed-task:before {
            content: "✅";
            margin-right: 10px;
        }
        .upcoming-task:before {
            content: "📋";
            margin-right: 10px;
        }
        .milestone {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📈 Project Status Update</h1>
        <p>{{ $projectName }}</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $clientName }},</h2>
        
        <p>We're excited to share the latest progress update on your project. Here's what we've accomplished and what's coming next:</p>
        
        <div class="progress-section">
            <h3>🎯 Overall Progress</h3>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $progressPercentage }}%;">
                    <span class="progress-text">{{ $progressPercentage }}% Complete</span>
                </div>
            </div>
            <p style="text-align: center; margin-top: 10px; font-weight: bold; color: #4caf50;">
                {{ $progressPercentage }}% of your project is now complete!
            </p>
        </div>
        
        @if(!empty($completedTasks))
        <div class="progress-section">
            <h3>✅ Recently Completed Tasks</h3>
            <ul class="task-list">
                @foreach($completedTasks as $task)
                <li class="completed-task">{{ $task }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @if(!empty($upcomingTasks))
        <div class="progress-section">
            <h3>📋 Upcoming Tasks</h3>
            <ul class="task-list">
                @foreach($upcomingTasks as $task)
                <li class="upcoming-task">{{ $task }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @if($progressPercentage >= 25 && $progressPercentage < 50)
        <div class="milestone">
            <h4>🎉 Milestone Achieved: Design Phase Complete!</h4>
            <p>We've successfully completed the design phase and are now moving into development.</p>
        </div>
        @elseif($progressPercentage >= 50 && $progressPercentage < 75)
        <div class="milestone">
            <h4>🎉 Milestone Achieved: Development 50% Complete!</h4>
            <p>We're halfway through development. The core functionality is taking shape!</p>
        </div>
        @elseif($progressPercentage >= 75 && $progressPercentage < 90)
        <div class="milestone">
            <h4>🎉 Milestone Achieved: Testing Phase!</h4>
            <p>Development is nearly complete and we're now in the testing and refinement phase.</p>
        </div>
        @elseif($progressPercentage >= 90)
        <div class="milestone">
            <h4>🎉 Almost There: Final Preparations!</h4>
            <p>We're in the final stretch! Just putting the finishing touches before launch.</p>
        </div>
        @endif
        
        @if(!empty($notes))
        <div class="progress-section">
            <h3>📝 Additional Notes</h3>
            <p>{{ $notes }}</p>
        </div>
        @endif
        
        <div class="progress-section">
            <h3>📞 Questions or Feedback?</h3>
            <p>We value your input throughout the development process. If you have any questions, suggestions, or feedback, please don't hesitate to reach out. Your satisfaction is our top priority!</p>
            <p>📞 Call us: <strong>+91-9453619260</strong><br>
            📧 Email us: <strong>uky171991@gmail.com</strong></p>
        </div>
        
        <p>Thank you for choosing us for your web development needs. We're committed to delivering exceptional results and will keep you updated every step of the way.</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Development Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pathology Management System Proposal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .proposal-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .cost-highlight {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .cost-amount {
            font-size: 24px;
            font-weight: bold;
            color: #155724;
        }
        .features-list {
            list-style: none;
            padding: 0;
        }
        .features-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .features-list li:before {
            content: "🔬";
            font-weight: bold;
            margin-right: 10px;
        }
        .modules-list {
            list-style: none;
            padding: 0;
        }
        .modules-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .modules-list li:before {
            content: "⚕️";
            font-weight: bold;
            margin-right: 10px;
        }
        .cta-button {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        .highlight-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔬 Pathology Management System</h1>
        <p>Professional Lab Management Solution by CodeApka</p>
    </div>
    
    <div class="content">
        <h2>Dear {{ $clientName ?? 'Valued Client' }},</h2>
        
        <p>Thank you for your interest in our comprehensive <strong>Pathology Management System</strong>. We're excited to present you with a solution that will revolutionize your laboratory operations and improve patient care.</p>
        
        <div class="proposal-section">
            <h3>🔬 System Overview</h3>
            <p><strong>Project:</strong> {{ $projectName ?? 'Advanced Pathology Management System' }}</p>
            <p><strong>Scope:</strong> Complete laboratory management solution with patient tracking, test management, reporting, and billing integration.</p>
            @if(isset($notes) && $notes)
            <p><strong>Requirements:</strong> {{ $notes }}</p>
            @else
            <p><strong>Requirements:</strong> Full-featured pathology lab management system with modern interface and comprehensive reporting capabilities.</p>
            @endif
        </div>

        <div class="proposal-section">
            <h3>⚕️ Core Modules</h3>
            <ul class="modules-list">
                <li><strong>Patient Registration & Management</strong> - Complete patient records and history</li>
                <li><strong>Test Catalog Management</strong> - Comprehensive test database with pricing</li>
                <li><strong>Sample Collection & Tracking</strong> - Barcode-based sample management</li>
                <li><strong>Laboratory Workflow</strong> - Automated test processing and validation</li>
                <li><strong>Report Generation</strong> - Professional lab reports with digital signatures</li>
                <li><strong>Billing & Invoice Management</strong> - Integrated billing with payment tracking</li>
                <li><strong>Inventory Management</strong> - Reagent and equipment tracking</li>
                <li><strong>Quality Control</strong> - QC protocols and compliance monitoring</li>
                <li><strong>Doctor Portal</strong> - Online access for referring physicians</li>
                <li><strong>Patient Portal</strong> - Online report access and appointment booking</li>
            </ul>
        </div>

        <div class="proposal-section">
            <h3>🚀 Key Features</h3>
            <ul class="features-list">
                <li><strong>Multi-location Support</strong> - Manage multiple lab branches</li>
                <li><strong>LIS Integration</strong> - Connect with laboratory instruments</li>
                <li><strong>LIMS Compliance</strong> - Laboratory Information Management standards</li>
                <li><strong>Barcode Integration</strong> - Sample tracking and automation</li>
                <li><strong>Digital Signatures</strong> - Secure report authentication</li>
                <li><strong>Automated Alerts</strong> - Critical value notifications</li>
                <li><strong>Data Backup & Security</strong> - HIPAA compliant data protection</li>
                <li><strong>Mobile App</strong> - Android/iOS apps for staff and patients</li>
                <li><strong>Analytics Dashboard</strong> - Business intelligence and reporting</li>
                <li><strong>API Integration</strong> - Connect with hospital management systems</li>
            </ul>
        </div>

        @if(isset($estimatedCost) && $estimatedCost)
        <div class="cost-highlight">
            <h3>💰 Investment</h3>
            <div class="cost-amount">₹{{ number_format($estimatedCost) }}</div>
            <p>Complete Pathology Management System</p>
            <small>Including development, testing, training, and 6 months support</small>
        </div>
        @endif

        <div class="highlight-box">
            <h4>🎯 Why Choose Our Pathology Management System?</h4>
            <ul>
                <li>✅ <strong>Industry Experience:</strong> 5+ years in healthcare software development</li>
                <li>✅ <strong>Compliance Ready:</strong> NABH, CAP, and ISO 15189 compliant</li>
                <li>✅ <strong>Scalable Architecture:</strong> Grows with your laboratory</li>
                <li>✅ <strong>24/7 Support:</strong> Dedicated technical support team</li>
                <li>✅ <strong>Training Included:</strong> Comprehensive staff training program</li>
                <li>✅ <strong>Data Migration:</strong> Seamless migration from existing systems</li>
            </ul>
        </div>

        @if(isset($timeframe) && $timeframe)
        <div class="proposal-section">
            <h3>⏱️ Project Timeline</h3>
            <p><strong>Estimated Duration:</strong> {{ $timeframe }}</p>
            <div style="margin: 15px 0;">
                <p><strong>Project Phases:</strong></p>
                <ol>
                    <li><strong>Week 1-2:</strong> Requirements analysis and system design</li>
                    <li><strong>Week 3-8:</strong> Core module development and testing</li>
                    <li><strong>Week 9-10:</strong> Integration and system testing</li>
                    <li><strong>Week 11-12:</strong> User training and deployment</li>
                </ol>
            </div>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="mailto:uky171991@gmail.com?subject=Pathology Management System Proposal - {{ $clientName ?? 'Client' }}" class="cta-button">
                💬 Let's Discuss Your Requirements
            </a>
        </div>
        
        <p>Our pathology management system is designed to streamline your laboratory operations, improve efficiency, and enhance patient care. We're committed to delivering a solution that meets your specific requirements and exceeds your expectations.</p>
        
        <p>For any questions or to schedule a demo, please feel free to contact us at <strong>+91-9453619260</strong>, WhatsApp us at <strong>+91-9453619260</strong>, or reply to this email.</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Healthcare Solutions Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 💬 WhatsApp: +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
        <p>🔬 Transforming Healthcare with Technology 🔬</p>
    </div>
</body>
</html>

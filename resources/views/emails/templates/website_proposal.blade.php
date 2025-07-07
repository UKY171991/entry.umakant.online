<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Development Proposal</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .proposal-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .cost-highlight {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .cost-amount {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
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
            content: "✓";
            color: #4caf50;
            font-weight: bold;
            margin-right: 10px;
        }
        .cta-button {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
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
        <h1>🚀 Website Development Proposal</h1>
        <p>Professional Web Solutions by CodeApka</p>
    </div>
    
    <div class="content">
        <h2>Dear {{ $clientName }},</h2>
        
        <p>Thank you for considering our web development services. We're excited to present this comprehensive proposal for your upcoming project.</p>
        
        <div class="proposal-section">
            <h3>📋 Project Overview</h3>
            <p><strong>Project:</strong> {{ $projectName }}</p>
            @if(!empty($notes))
            <p><strong>Requirements:</strong> {{ $notes }}</p>
            @else
            <p>We will create a modern, professional website tailored to your business needs with all the latest technologies and best practices.</p>
            @endif
        </div>
        
        <div class="proposal-section">
            <h3>🎯 What's Included</h3>
            <ul class="features-list">
                <li>Responsive Design (Mobile, Tablet, Desktop)</li>
                <li>Modern UI/UX Design</li>
                <li>Search Engine Optimization (SEO)</li>
                <li>Cross-browser Compatibility</li>
                <li>Content Management System</li>
                <li>Security Implementation</li>
                <li>Performance Optimization</li>
                <li>Google Analytics Integration</li>
                <li>Contact Forms & Email Integration</li>
                <li>Social Media Integration</li>
                <li>SSL Certificate Setup</li>
                <li>3 Months Free Support</li>
            </ul>
        </div>
        
        <div class="cost-highlight">
            <h3>💰 Investment</h3>
            <div class="cost-amount">₹{{ number_format($estimatedCost) }}</div>
            <p>One-time development cost</p>
        </div>
        
        <div class="proposal-section">
            <h3>⏰ Timeline</h3>
            <p><strong>Estimated Completion:</strong> {{ $timeframe }}</p>
            <p>We follow a structured development process with regular updates and client feedback sessions.</p>
        </div>
        
        <div class="proposal-section">
            <h3>🔄 Development Process</h3>
            <ol>
                <li><strong>Planning & Design (25%)</strong> - Wireframes, mockups, and design approval</li>
                <li><strong>Development (50%)</strong> - Frontend and backend development</li>
                <li><strong>Testing & Refinement (15%)</strong> - Quality assurance and bug fixes</li>
                <li><strong>Launch & Support (10%)</strong> - Go-live and initial support</li>
            </ol>
        </div>
        
        <div style="text-align: center;">
            <a href="mailto:uky171991@gmail.com?subject=Website Proposal Acceptance - {{ $clientName }}" class="cta-button">
                Accept Proposal & Get Started
            </a>
        </div>
        
        <p>We're committed to delivering a website that not only looks great but also drives results for your business. Our team is ready to start working on your project immediately upon approval.</p>
        
        <p>If you have any questions or would like to discuss any aspect of this proposal, please don't hesitate to reach out.</p>
        
        <p>Looking forward to working with you!</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Web Development Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 💬 WhatsApp: +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
    </div>
</body>
</html>

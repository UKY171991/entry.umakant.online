<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System Proposal</title>
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
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
            border-left: 4px solid #007bff;
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
            content: "🏥";
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
            background: #007bff;
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
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .benefits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .benefit-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Hospital Management System</h1>
        <p>Complete Healthcare Management Solution by CodeApka</p>
    </div>
    
    <div class="content">
        <h2>Dear {{ $clientName ?? 'Valued Client' }},</h2>
        
        <p>We are pleased to present our comprehensive <strong>Hospital Management System (HMS)</strong> solution designed to digitize and streamline your healthcare facility operations. Our system will enhance patient care, improve operational efficiency, and provide valuable insights for better decision-making.</p>
        
        <div class="proposal-section">
            <h3>🏥 System Overview</h3>
            <p><strong>Project:</strong> {{ $projectName ?? 'Comprehensive Hospital Management System' }}</p>
            <p><strong>Scope:</strong> End-to-end hospital management solution covering patient care, administrative functions, financial management, and regulatory compliance.</p>
            @if(isset($notes) && $notes)
            <p><strong>Requirements:</strong> {{ $notes }}</p>
            @else
            <p><strong>Requirements:</strong> Complete hospital management system with integrated modules for patient care, administration, billing, pharmacy, and reporting.</p>
            @endif
        </div>

        <div class="proposal-section">
            <h3>⚕️ Core Modules</h3>
            <ul class="modules-list">
                <li><strong>Patient Registration & EMR</strong> - Complete electronic medical records</li>
                <li><strong>Appointment Management</strong> - Online booking and scheduling system</li>
                <li><strong>OPD Management</strong> - Outpatient department workflow</li>
                <li><strong>IPD Management</strong> - Inpatient care and bed management</li>
                <li><strong>Emergency Management</strong> - ER patient tracking and triage</li>
                <li><strong>Operation Theater Management</strong> - Surgery scheduling and tracking</li>
                <li><strong>Pharmacy Management</strong> - Drug inventory and dispensing</li>
                <li><strong>Laboratory Integration</strong> - Lab test orders and results</li>
                <li><strong>Radiology Management</strong> - Imaging department workflow</li>
                <li><strong>Billing & Finance</strong> - Insurance, billing, and accounting</li>
                <li><strong>HR Management</strong> - Staff scheduling and payroll</li>
                <li><strong>Inventory Management</strong> - Medical supplies and equipment</li>
            </ul>
        </div>

        <div class="proposal-section">
            <h3>🚀 Advanced Features</h3>
            <ul class="features-list">
                <li><strong>HMIS Compliance</strong> - Government healthcare standards</li>
                <li><strong>HL7 Integration</strong> - Healthcare data exchange standards</li>
                <li><strong>NABH Compliance</strong> - National accreditation board standards</li>
                <li><strong>Multi-location Support</strong> - Manage multiple hospital branches</li>
                <li><strong>Mobile Applications</strong> - iOS/Android apps for staff and patients</li>
                <li><strong>Telemedicine Integration</strong> - Virtual consultation platform</li>
                <li><strong>AI-powered Analytics</strong> - Predictive healthcare insights</li>
                <li><strong>Digital Signature</strong> - Secure document authentication</li>
                <li><strong>Cloud Infrastructure</strong> - Scalable and secure cloud hosting</li>
                <li><strong>Integration APIs</strong> - Connect with external systems</li>
            </ul>
        </div>

        @if(isset($estimatedCost) && $estimatedCost)
        <div class="cost-highlight">
            <h3>💰 Investment</h3>
            <div class="cost-amount">₹{{ number_format($estimatedCost) }}</div>
            <p>Complete Hospital Management System</p>
            <small>Including development, implementation, training, and 1 year support</small>
        </div>
        @endif

        <div class="highlight-box">
            <h4>🎯 Why Choose Our Hospital Management System?</h4>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <h5>🔒 Security & Compliance</h5>
                    <p>HIPAA compliant with advanced security protocols</p>
                </div>
                <div class="benefit-card">
                    <h5>📱 User-Friendly Interface</h5>
                    <p>Intuitive design for healthcare professionals</p>
                </div>
                <div class="benefit-card">
                    <h5>🔧 Customizable</h5>
                    <p>Tailored to your hospital's specific needs</p>
                </div>
                <div class="benefit-card">
                    <h5>📊 Real-time Analytics</h5>
                    <p>Data-driven insights for better decisions</p>
                </div>
            </div>
            <ul>
                <li>✅ <strong>Healthcare Expertise:</strong> 5+ years in healthcare IT solutions</li>
                <li>✅ <strong>Proven Track Record:</strong> Successfully deployed in 20+ hospitals</li>
                <li>✅ <strong>24/7 Support:</strong> Round-the-clock technical assistance</li>
                <li>✅ <strong>Training & Support:</strong> Comprehensive staff training program</li>
                <li>✅ <strong>Data Migration:</strong> Seamless transition from legacy systems</li>
                <li>✅ <strong>Regular Updates:</strong> Continuous feature enhancements</li>
            </ul>
        </div>

        @if(isset($timeframe) && $timeframe)
        <div class="proposal-section">
            <h3>⏱️ Implementation Timeline</h3>
            <p><strong>Estimated Duration:</strong> {{ $timeframe }}</p>
            <div style="margin: 15px 0;">
                <p><strong>Implementation Phases:</strong></p>
                <ol>
                    <li><strong>Phase 1 (Weeks 1-3):</strong> Requirements gathering and system design</li>
                    <li><strong>Phase 2 (Weeks 4-12):</strong> Core modules development and integration</li>
                    <li><strong>Phase 3 (Weeks 13-16):</strong> Testing, customization, and quality assurance</li>
                    <li><strong>Phase 4 (Weeks 17-20):</strong> Data migration and staff training</li>
                    <li><strong>Phase 5 (Weeks 21-24):</strong> Go-live support and optimization</li>
                </ol>
            </div>
        </div>
        @endif

        <div class="proposal-section">
            <h3>🔧 Support & Maintenance</h3>
            <ul>
                <li>🔧 <strong>24/7 Technical Support</strong> - Round-the-clock assistance</li>
                <li>📚 <strong>Training Programs</strong> - Comprehensive staff training</li>
                <li>🔄 <strong>Regular Updates</strong> - System updates and new features</li>
                <li>💾 <strong>Data Backup</strong> - Automated daily backups</li>
                <li>🛡️ <strong>Security Monitoring</strong> - Continuous security assessment</li>
                <li>📊 <strong>Performance Monitoring</strong> - System optimization</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="mailto:uky171991@gmail.com?subject=Hospital Management System Proposal - {{ $clientName ?? 'Client' }}" class="cta-button">
                💬 Schedule a Demo
            </a>
        </div>
        
        <p>Our Hospital Management System is designed to transform your healthcare facility into a modern, efficient, and patient-centric organization. We're committed to providing you with a solution that improves patient outcomes while reducing operational costs.</p>
        
        <p>For a detailed demo or to discuss your specific requirements, please contact us at <strong>+91-9453619260</strong>, WhatsApp us at <strong>+91-9453619260</strong>, or reply to this email. We're here to help you revolutionize your healthcare operations.</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Healthcare Solutions Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 💬 WhatsApp: +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
        <p>🏥 Empowering Healthcare with Innovation 🏥</p>
    </div>
</body>
</html>

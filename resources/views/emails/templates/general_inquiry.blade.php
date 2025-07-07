<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Inquiry Response</title>
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
            background: linear-gradient(135deg, #2196f3 0%, #21cbf3 100%);
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
        .services-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        .cta-button {
            background: #2196f3;
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
        <h1>📧 Thank You for Your Inquiry!</h1>
        <p>Professional Web Development Services by CodeApka</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $data['client_name'] ?? 'Valued Client' }},</h2>
        
        <p>Thank you for reaching out to us regarding your web development needs. We're excited about the opportunity to work with you and help bring your digital vision to life.</p>
        
        <div class="services-section">
            <h3>🚀 Our Services</h3>
            <ul>
                <li><strong>Website Development</strong> - Custom, responsive websites</li>
                <li><strong>E-commerce Solutions</strong> - Online stores and payment integration</li>
                <li><strong>Web Applications</strong> - Custom web-based applications</li>
                <li><strong>SEO Optimization</strong> - Improve your search rankings</li>
                <li><strong>Maintenance & Support</strong> - Ongoing website care</li>
                <li><strong>Digital Marketing</strong> - Grow your online presence</li>
            </ul>
        </div>
        
        <div class="services-section">
            <h3>⭐ Why Choose Us?</h3>
            <ul>
                <li>✅ 5+ years of experience in web development</li>
                <li>✅ 100+ successful projects completed</li>
                <li>✅ Modern, responsive design approach</li>
                <li>✅ SEO-optimized websites</li>
                <li>✅ Ongoing support and maintenance</li>
                <li>✅ Competitive pricing</li>
                <li>✅ On-time delivery guarantee</li>
            </ul>
        </div>
        
        <div style="text-align: center;">
            <a href="mailto:uky171991@gmail.com?subject=Project Discussion - {{ $data['client_name'] ?? 'Client' }}" class="cta-button">
                Schedule a Free Consultation
            </a>
        </div>
        
        <p>We'd love to discuss your project in detail and provide you with a customized proposal. Please feel free to call us at <strong>+91-9453619260</strong>, WhatsApp us at <strong>+91-9453619260</strong>, or reply to this email with your project requirements.</p>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Schedule a free consultation call</li>
            <li>Discuss your project requirements</li>
            <li>Receive a detailed proposal</li>
            <li>Start building your amazing website!</li>
        </ol>
        
        <p>We look forward to hearing from you soon!</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Web Development Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 💬 WhatsApp: +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
    </div>
</body>
</html>

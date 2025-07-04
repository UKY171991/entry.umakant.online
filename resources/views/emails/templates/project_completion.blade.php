<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Completion</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
        .celebration {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
        }
        .website-info {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #ff6b6b;
        }
        .website-link {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .website-url {
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            word-break: break-all;
        }
        .credentials-box {
            background: #fff3e0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ffb74d;
        }
        .cta-button {
            background: #ff6b6b;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .support-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
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
        <h1>🎉 Your Website is Live!</h1>
        <p>{{ $projectName }} - Project Completed Successfully</p>
    </div>
    
    <div class="content">
        <div class="celebration">🚀✨🎊</div>
        
        <h2>Congratulations {{ $clientName }}!</h2>
        
        <p>We're thrilled to announce that your website project "<strong>{{ $projectName }}</strong>" has been completed and is now live on the internet!</p>
        
        <div class="website-info">
            <h3>🌐 Your Website is Ready</h3>
            <p>After careful development, testing, and optimization, your website is now available for the world to see. We've ensured that everything is working perfectly and meets the highest standards of quality.</p>
        </div>
        
        <div class="website-link">
            <h3>🔗 Visit Your Website</h3>
            <div class="website-url">
                <a href="{{ $websiteUrl }}" target="_blank">{{ $websiteUrl }}</a>
            </div>
            <p>Click the link above to view your new website!</p>
        </div>
        
        @if(!empty($loginCredentials))
        <div class="credentials-box">
            <h3>🔐 Admin Access Credentials</h3>
            <p><strong>⚠️ Please keep these credentials secure!</strong></p>
            @if(isset($loginCredentials['admin_url']))
            <p><strong>Admin Panel:</strong> {{ $loginCredentials['admin_url'] }}</p>
            @endif
            @if(isset($loginCredentials['username']))
            <p><strong>Username:</strong> {{ $loginCredentials['username'] }}</p>
            @endif
            @if(isset($loginCredentials['password']))
            <p><strong>Password:</strong> {{ $loginCredentials['password'] }}</p>
            @endif
            <p><em>We recommend changing the password after your first login for security purposes.</em></p>
        </div>
        @endif
        
        <div class="website-info">
            <h3>✅ What We've Delivered</h3>
            <ul>
                <li>✅ Fully responsive website (mobile, tablet, desktop)</li>
                <li>✅ Modern and professional design</li>
                <li>✅ Search engine optimized (SEO)</li>
                <li>✅ Fast loading and optimized performance</li>
                <li>✅ Cross-browser compatibility</li>
                <li>✅ Security measures implemented</li>
                <li>✅ Content management system</li>
                <li>✅ Contact forms and email integration</li>
                <li>✅ Social media integration</li>
                <li>✅ SSL certificate installed</li>
                <li>✅ Google Analytics setup</li>
                <li>✅ Backup systems in place</li>
            </ul>
        </div>
        
        <div class="support-section">
            <h3>🛠️ Ongoing Support</h3>
            @if(!empty($supportDetails))
            <p>{{ $supportDetails }}</p>
            @else
            <p>We're here to support you! Your package includes:</p>
            <ul>
                <li>🕐 <strong>3 months of free support</strong> - Bug fixes and minor updates</li>
                <li>📞 <strong>Technical assistance</strong> - Help with using your website</li>
                <li>🔧 <strong>Maintenance services</strong> - Keep your website running smoothly</li>
                <li>📈 <strong>Performance monitoring</strong> - Ensure optimal speed and uptime</li>
            </ul>
            @endif
        </div>
        
        <div style="text-align: center;">
            <a href="mailto:uky171991@gmail.com?subject=Website Launch Feedback - {{ $projectName }}" class="cta-button">
                Share Your Feedback
            </a>
        </div>
        
        <div class="website-info">
            <h3>📱 Next Steps</h3>
            <ol>
                <li><strong>Test your website</strong> - Browse through all pages and features</li>
                <li><strong>Update your content</strong> - Add your latest information and images</li>
                <li><strong>Share your website</strong> - Update your business cards, social media, etc.</li>
                <li><strong>Monitor performance</strong> - We'll send you monthly analytics reports</li>
            </ol>
        </div>
        
        <p>Thank you for choosing us for your web development needs. It has been a pleasure working with you, and we're proud of what we've accomplished together.</p>
        
        <p>If you have any questions, need assistance, or would like to discuss future enhancements, please don't hesitate to contact us at <strong>+91-9453619260</strong> or <strong>uky171991@gmail.com</strong>.</p>
        
        <p>Congratulations again on your new website!</p>
        
        <p><strong>Best regards,</strong><br>
        CodeApka Web Development Team</p>
    </div>
    
    <div class="footer">
        <p>📧 uky171991@gmail.com | 📱 +91-9453619260 | 🌐 https://codeapka.com</p>
        <p>© {{ date('Y') }} CodeApka. All rights reserved.</p>
        <p>🌟 Thank you for trusting us with your web development project! 🌟</p>
    </div>
</body>
</html>

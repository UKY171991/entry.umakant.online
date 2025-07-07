<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Development Update</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            -webkit-text-size-adjust: none;
            width: 100%;
        }
        
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 0;
            height: 0;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-top: 15px solid #764ba2;
            transform: translateX(-50%);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.8;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .content p {
            margin-bottom: 20px;
            font-size: 15px;
        }
        
        .update-section {
            background: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        
        .update-section h3 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .progress-bar {
            background: #e0e7ff;
            height: 10px;
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        
        .milestone {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .milestone-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .milestone.completed .milestone-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .footer {
            background: linear-gradient(135deg, #f8f9ff 0%, #e0e7ff 100%);
            color: #6b7280;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin: 0 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🚀 Website Development Update</h1>
            <p>Your project is progressing beautifully!</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello {{ $clientName ?? 'Valued Client' }}! 👋
            </div>
            
            <p>
                We're excited to share the latest progress on your website development project. 
                Our team has been working diligently to bring your vision to life, and we're thrilled 
                with the results so far.
            </p>
            
            <!-- Progress Section -->
            <div class="update-section">
                <h3>📊 Project Progress: {{ $progress ?? '65' }}% Complete</h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $progress ?? '65' }}%"></div>
                </div>
                
                <div class="milestone completed">
                    <div class="milestone-icon">✓</div>
                    <div>
                        <strong>Design Phase Completed</strong><br>
                        <small>UI/UX design approved and finalized</small>
                    </div>
                </div>
                
                <div class="milestone completed">
                    <div class="milestone-icon">✓</div>
                    <div>
                        <strong>Frontend Development</strong><br>
                        <small>Responsive design implementation finished</small>
                    </div>
                </div>
                
                <div class="milestone">
                    <div class="milestone-icon">⚡</div>
                    <div>
                        <strong>Backend Development - In Progress</strong><br>
                        <small>Database integration and API development</small>
                    </div>
                </div>
                
                <div class="milestone">
                    <div class="milestone-icon">🔜</div>
                    <div>
                        <strong>Testing & Quality Assurance</strong><br>
                        <small>Comprehensive testing across all devices</small>
                    </div>
                </div>
            </div>
            
            <!-- Recent Updates -->
            <div class="update-section">
                <h3>🔥 Recent Achievements</h3>
                <ul style="padding-left: 20px; color: #555;">
                    <li style="margin-bottom: 10px;">✨ Implemented modern, responsive design with smooth animations</li>
                    <li style="margin-bottom: 10px;">🔧 Integrated advanced contact forms with real-time validation</li>
                    <li style="margin-bottom: 10px;">📱 Optimized for mobile devices and tablets</li>
                    <li style="margin-bottom: 10px;">⚡ Enhanced page loading speed by 40%</li>
                    <li style="margin-bottom: 10px;">🎨 Added interactive elements and micro-interactions</li>
                </ul>
            </div>
            
            <!-- Next Steps -->
            <div class="update-section">
                <h3>🎯 Upcoming Milestones</h3>
                <p>
                    <strong>This Week:</strong> Completing the backend infrastructure and database optimization<br>
                    <strong>Next Week:</strong> Content management system integration and admin panel development<br>
                    <strong>Following Week:</strong> Final testing, performance optimization, and launch preparation
                </p>
            </div>
            
            <p>
                We're committed to delivering a website that not only meets but exceeds your expectations. 
                Your feedback has been invaluable in shaping this project, and we appreciate your patience 
                as we craft something truly exceptional.
            </p>
            
            <!-- Call to Action -->
            <div class="cta-section">
                <a href="{{ $previewLink ?? '#' }}" class="cta-button">
                    🎨 View Latest Preview
                </a>
            </div>
            
            <p>
                If you have any questions, concerns, or would like to schedule a progress review call, 
                please don't hesitate to reach out. We're here to ensure your complete satisfaction.
            </p>
            
            <p style="margin-top: 30px;">
                <strong>Best regards,</strong><br>
                <span style="color: #667eea; font-weight: 600;">{{ $teamName ?? 'The Development Team' }}</span><br>
                <small style="color: #6b7280;">{{ $companyName ?? 'Doofer Admin' }} | Web Development Division</small>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="{{ $websiteUrl ?? '#' }}">Visit Our Website</a>
                <a href="{{ $portfolioUrl ?? '#' }}">View Portfolio</a>
                <a href="{{ $contactUrl ?? '#' }}">Contact Support</a>
            </div>
            
            <div class="social-links">
                <a href="{{ $facebookUrl ?? '#' }}">f</a>
                <a href="{{ $twitterUrl ?? '#' }}">t</a>
                <a href="{{ $linkedinUrl ?? '#' }}">in</a>
                <a href="{{ $instagramUrl ?? '#' }}">ig</a>
            </div>
            
            <p style="font-size: 12px; margin-top: 20px;">
                © {{ date('Y') }} {{ $companyName ?? 'Doofer Admin' }}. All rights reserved.<br>
                This email was sent to {{ $clientEmail ?? 'you' }} regarding your website development project.
            </p>
            
            <p style="font-size: 11px; margin-top: 15px; color: #9ca3af;">
                If you no longer wish to receive these updates, you can 
                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #667eea;">unsubscribe here</a>.
            </p>
        </div>
    </div>
</body>
</html>
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            Website Development Update
        </div>
        <div class="content">
            <p>Dear Valued Client,</p>
            <p>This is an important update regarding your website development project. We are making great progress and wanted to share the latest.</p>
            <p>{{ $body }}</p>
            <p>We are committed to delivering a high-quality product and appreciate your continued partnership.</p>
            <p>If you have any questions or require further information, please do not hesitate to contact us at <strong>+91-9453619260</strong>, <strong>uky171991@gmail.com</strong>, or WhatsApp us at <strong>+91-9453619260</strong>.</p>
            <p>Sincerely,</p>
            <p>CodeApka Development Team</p>
            <p><a href="https://codeapka.com" class="button">Visit Our Website</a></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CodeApka. All rights reserved. | 📧 uky171991@gmail.com | 📱 +91-9453619260 | 💬 WhatsApp: +91-9453619260 | 🌐 https://codeapka.com
        </div>
    </div>
</body>
</html>
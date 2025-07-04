<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Doofer</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .welcome-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
            width: 90%;
            backdrop-filter: blur(10px);
        }
        .welcome-logo {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .welcome-subtitle {
            font-size: 1.3rem;
            color: #6c757d;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        .feature-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .feature-desc {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .welcome-buttons {
            margin-top: 40px;
        }
        .btn-welcome {
            padding: 15px 30px;
            margin: 10px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-primary-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .btn-primary-welcome:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        .btn-outline-welcome {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-outline-welcome:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
            text-decoration: none;
        }
        .stats-section {
            margin: 40px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .welcome-container {
                padding: 30px 20px;
            }
            .welcome-title {
                font-size: 2rem;
            }
            .welcome-subtitle {
                font-size: 1.1rem;
            }
            .btn-welcome {
                padding: 12px 25px;
                font-size: 1rem;
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-logo">
            <i class="fas fa-chart-line"></i>
        </div>
        
        <h1 class="welcome-title">Doofer Admin</h1>
        
        <p class="welcome-subtitle">
            Your comprehensive business management solution for tracking clients, managing finances, and optimizing operations.
        </p>

        <div class="welcome-features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="feature-title">Client Management</div>
                <div class="feature-desc">Organize and manage all your client information in one place</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="feature-title">Income Tracking</div>
                <div class="feature-desc">Monitor your revenue streams and pending payments</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="feature-title">Expense Control</div>
                <div class="feature-desc">Track and categorize all your business expenses</div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="feature-title">Task Management</div>
                <div class="feature-desc">Stay organized with pending tasks and deadlines</div>
            </div>
        </div>

        <div class="stats-section">
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Availability</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Secure</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">∞</div>
                <div class="stat-label">Scalable</div>
            </div>
        </div>

        <div class="welcome-buttons">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-welcome btn-primary-welcome">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-welcome btn-primary-welcome">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login
                </a>
                
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-welcome btn-outline-welcome">
                        <i class="fas fa-user-plus mr-2"></i>
                        Register
                    </a>
                @endif
            @endauth
        </div>
    </div>

    <!-- AdminLTE JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>

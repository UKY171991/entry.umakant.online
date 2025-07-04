<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Nunito', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(to right, #6dd5ed, #2193b0); /* Blue gradient */
                color: #fff;
                text-align: center;
            }
            .auth-container {
                background-color: rgba(255, 255, 255, 0.15);
                padding: 40px 60px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                width: 100%;
                max-width: 450px; /* Adjust as needed */
            }
            .auth-logo {
                margin-bottom: 30px;
            }
            .auth-logo svg {
                width: 80px;
                height: 80px;
                fill: #fff; /* Change logo color to white */
            }
            .auth-form-card {
                background-color: rgba(255, 255, 255, 0.9);
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }
            .auth-form-card label {
                color: #333;
            }
            .auth-form-card input[type="email"],
            .auth-form-card input[type="password"] {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
                width: 100%;
                box-sizing: border-box;
                margin-top: 5px;
                color: #333;
            }
            .auth-form-card .checkbox-label {
                color: #555;
            }
            .auth-form-card .forgot-password-link {
                color: #2193b0;
                text-decoration: none;
            }
            .auth-form-card .forgot-password-link:hover {
                text-decoration: underline;
            }
            .auth-form-card .primary-button {
                background-color: #2193b0;
                color: #fff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }
            .auth-form-card .primary-button:hover {
                background-color: #1a7a92;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="auth-container">
                <div class="auth-logo">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <div class="auth-form-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

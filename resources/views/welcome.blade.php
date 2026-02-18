<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Express Bank - Coming Soon</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            /* Animated background particles */
            .particles {
                position: absolute;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }

            .particle {
                position: absolute;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                animation: float 15s infinite;
            }

            .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
            .particle:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-delay: 2s; }
            .particle:nth-child(3) { width: 100px; height: 100px; left: 35%; animation-delay: 4s; }
            .particle:nth-child(4) { width: 50px; height: 50px; left: 50%; animation-delay: 0s; }
            .particle:nth-child(5) { width: 70px; height: 70px; left: 65%; animation-delay: 3s; }
            .particle:nth-child(6) { width: 90px; height: 90px; left: 80%; animation-delay: 5s; }

            @keyframes float {
                0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
                10% { opacity: 1; }
                90% { opacity: 1; }
                100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
            }

            .container {
                position: relative;
                z-index: 10;
                text-align: center;
                padding: 2rem;
                max-width: 600px;
                width: 100%;
            }

            .logo-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 3rem;
                border-radius: 30px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: fadeInUp 1s ease-out;
                margin-bottom: 2rem;
            }

            .logo {
                font-size: 3.5rem;
                font-weight: 700;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin-bottom: 0.5rem;
                letter-spacing: -1px;
            }

            .subtitle {
                font-size: 1.25rem;
                color: #4a5568;
                font-weight: 500;
                margin-bottom: 2rem;
            }

            .divider {
                width: 80px;
                height: 4px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0 auto 2rem;
                border-radius: 2px;
            }

            .coming-soon {
                font-size: 2rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 1rem;
                animation: pulse 2s ease-in-out infinite;
            }

            .description {
                font-size: 1.1rem;
                color: #718096;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .info-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
                margin-top: 2rem;
            }

            .info-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                padding: 1.5rem;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                animation: fadeInUp 1s ease-out;
                animation-fill-mode: both;
            }

            .info-card:nth-child(1) { animation-delay: 0.2s; }
            .info-card:nth-child(2) { animation-delay: 0.4s; }
            .info-card:nth-child(3) { animation-delay: 0.6s; }

            .info-card-icon {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }

            .info-card-title {
                font-size: 0.9rem;
                color: #4a5568;
                font-weight: 600;
            }

            .login-button {
                display: inline-block;
                margin-top: 2rem;
                padding: 1rem 3rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                font-size: 1.1rem;
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
                transition: all 0.3s ease;
                animation: fadeInUp 1s ease-out 0.8s;
                animation-fill-mode: both;
            }

            .login-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            @media (max-width: 640px) {
                .logo {
                    font-size: 2.5rem;
                }

                .coming-soon {
                    font-size: 1.5rem;
                }

                .subtitle {
                    font-size: 1rem;
                }

                .description {
                    font-size: 1rem;
                }

                .logo-container {
                    padding: 2rem;
                }

                .info-cards {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="container">
            <div class="logo-container">
                <div class="logo">{{ env('app_name') }}</div>
                <div class="subtitle">{{ __('Payment Solutions') }}</div>
                <div class="divider"></div>
                <div class="coming-soon">{{ __('Coming Soon') }}</div>
                <p class="description">
                    {{ __('We are developing a new generation of payment solutions. You are ready to meet us. Secure, fast and easy banking experience is coming soon.') }}
                </p>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">🔒</div>
                    <div class="info-card-title">{{ __('Secure') }}</div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">⚡</div>
                    <div class="info-card-title">{{ __('Fast') }}</div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">💎</div>
                    <div class="info-card-title">{{ __('Easy') }}</div>
                </div>
            </div>
        </div>
    </body>
</html>

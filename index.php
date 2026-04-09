<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker System - Your Friendly Financial Companion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Enhanced floating background elements */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            opacity: 0.6;
        }

        .floating-shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            background: radial-gradient(circle, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 55%;
            left: 85%;
            background: radial-gradient(circle, rgba(255,255,255,0.12), rgba(255,255,255,0.03));
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 75%;
            left: 15%;
            background: radial-gradient(circle, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
            animation-delay: 4s;
        }

        .floating-shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 8%;
            left: 75%;
            background: radial-gradient(circle, rgba(255,255,255,0.14), rgba(255,255,255,0.04));
            animation-delay: 1s;
        }

        .floating-shape:nth-child(5) {
            width: 90px;
            height: 90px;
            top: 40%;
            left: 5%;
            background: radial-gradient(circle, rgba(255,255,255,0.16), rgba(255,255,255,0.05));
            animation-delay: 3s;
        }

        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 15px 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo i {
            font-size: 2rem;
            background: linear-gradient(45deg, #fff, #e3f2fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 20px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .hero {
            max-width: 800px;
            margin: 120px auto 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.15);
            padding: 70px 50px 50px 50px;
            text-align: center;
            animation: slideInUp 1s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }

        .hero .emoji {
            font-size: 5rem;
            margin-bottom: 25px;
            animation: bounce 2s infinite, glow 3s ease-in-out infinite alternate;
            display: inline-block;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
        }

        .hero h1 {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 800;
            animation: fadeInDown 1s ease-out 0.5s both;
            line-height: 1.2;
        }

        .hero .subtitle {
            color: #666;
            font-size: 1.4rem;
            margin-bottom: 15px;
            font-weight: 600;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .hero p {
            color: #777;
            font-size: 1.2rem;
            margin-bottom: 45px;
            line-height: 1.8;
            animation: fadeInUp 1s ease-out 0.7s both;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 45px;
        }

        .cta-buttons {
            margin-bottom: 35px;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .cta-buttons a {
            text-decoration: none;
            color: #fff;
            padding: 18px 45px;
            border-radius: 50px;
            margin: 0 12px 15px 12px;
            font-weight: 700;
            font-size: 1.15rem;
            transition: all 0.4s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            min-width: 180px;
        }

        .cta-buttons a.primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            box-shadow: 0 10px 35px rgba(102, 126, 234, 0.4);
        }

        .cta-buttons a.secondary {
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            box-shadow: 0 10px 35px rgba(79, 172, 254, 0.4);
        }

        .cta-buttons a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .cta-buttons a:hover::before {
            left: 100%;
        }

        .cta-buttons a:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.6);
        }

        .welcome-message {
            color: #555;
            font-size: 1.1rem;
            margin-top: 25px;
            padding: 20px 30px;
            background: linear-gradient(45deg, #f8f9ff, #e8f4f8);
            border-radius: 20px;
            border-left: 6px solid #667eea;
            animation: fadeInUp 1s ease-out 1.1s both;
            position: relative;
        }

        .welcome-message::before {
            content: '✨';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
        }

        .welcome-message .text {
            margin-left: 25px;
        }

        .trust-indicators {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 1.2s both;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .trust-item i {
            color: #10b981;
            font-size: 1.2rem;
        }

        .stats-section {
            max-width: 900px;
            margin: 80px auto;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.1);
            padding: 50px;
            text-align: center;
            animation: fadeInUp 1s ease-out 1.5s both;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stats-title {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .stats-subtitle {
            color: #666;
            font-size: 1.3rem;
            margin-bottom: 40px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .stat-item {
            padding: 30px 20px;
            background: linear-gradient(135deg, #f8f9ff, #e8f4f8);
            border-radius: 20px;
            border: 2px solid transparent;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #667eea, #764ba2);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .stat-item:hover::before {
            opacity: 0.1;
        }

        .stat-item:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: #667eea;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .features {
            max-width: 1200px;
            margin: 80px auto 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
            padding: 0 20px;
            animation: fadeInUp 1s ease-out 1.7s both;
        }

        .feature {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature:hover::before {
            transform: scaleX(1);
        }

        .feature:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        .feature .icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: pulse 3s infinite;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-title {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .feature-desc {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.7;
            font-weight: 500;
        }

        .testimonial-section {
            max-width: 1000px;
            margin: 80px auto;
            text-align: center;
            animation: fadeInUp 1s ease-out 1.9s both;
        }

        .testimonial-title {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 50px;
        }

        .testimonials {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .testimonial {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s ease;
        }

        .testimonial:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        .testimonial-text {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .testimonial-info {
            text-align: left;
        }

        .testimonial-name {
            font-weight: 700;
            color: #333;
            margin-bottom: 2px;
        }

        .testimonial-role {
            color: #666;
            font-size: 0.9rem;
        }

        footer {
            margin-top: 100px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            padding: 40px 20px;
            animation: fadeInUp 1s ease-out 2.1s both;
        }

        .footer-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .footer-heart {
            color: #ff6b6b;
            animation: heartbeat 2s infinite;
            display: inline-block;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(180deg); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        @keyframes glow {
            0% { filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.3)); }
            100% { filter: drop-shadow(0 0 25px rgba(102, 126, 234, 0.8)); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(80px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero {
                margin: 100px 20px 0 20px;
                padding: 50px 30px;
            }
            
            .hero h1 {
                font-size: 2.8rem;
            }
            
            .hero .subtitle {
                font-size: 1.2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .cta-buttons a {
                display: block;
                margin: 15px auto;
                width: 100%;
                max-width: 280px;
            }
            
            .trust-indicators {
                gap: 20px;
            }
            
            .features {
                margin: 60px 20px 0 20px;
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .stats-section,
            .testimonial-section {
                margin: 60px 20px;
                padding: 40px 25px;
            }
            
            .stats-title,
            .testimonial-title {
                font-size: 2rem;
            }
        }
		* {
    animation: none !important;
    transition: none !important;
}
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <nav class="nav">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-gem"></i>
                <span>Income & Expense Tracker</span>
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="login.php">Sign In</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="emoji">🌟</div>
        <h1>Your Financial Journey Starts Here</h1>
        <div class="subtitle">Welcome to a friendlier way to manage money!</div>
        <p>We believe managing finances should be simple, encouraging, and actually enjoyable. Join thousands of people who have discovered a better relationship with their money through our caring, intuitive platform.</p>
        <div class="cta-buttons">
            <a href="register.php" class="primary">
                <i class="fas fa-rocket" style="margin-right: 8px;"></i>
                Start Your Journey
            </a>
            <a href="login.php" class="secondary">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                Welcome Back
            </a>
        </div>
        
        <div class="trust-indicators">
            <div class="trust-item">
                <i class="fas fa-shield-alt"></i>
                <span>100% Secure</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-heart"></i>
                <span>Always Free</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-clock"></i>
                <span>Setup in 30 seconds</span>
            </div>
        </div>
        
        <div class="welcome-message">
            <div class="text">
                <strong>You're in great company!</strong> Join our community of mindful money managers who've taken control of their financial future. We're here to support you every step of the way! 
            </div>
        </div>
    </div>

    <div class="stats-section">
        <div class="stats-title">Why Our Users Love Us</div>
        <div class="stats-subtitle">Real benefits that make a real difference in your life</div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon"><i class="fas fa-infinity"></i></div>
                <div class="stat-number">∞</div>
                <div class="stat-label">Unlimited Tracking</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number">30s</div>
                <div class="stat-label">Quick Setup</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="fas fa-mobile-alt"></i></div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Access Anywhere</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon"><i class="fas fa-gift"></i></div>
                <div class="stat-number">100%</div>
                <div class="stat-label">Free Forever</div>
            </div>
        </div>
    </div>

    <div class="features" id="features">
        <div class="feature">
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <div class="feature-title">Beautiful Insights</div>
            <div class="feature-desc">Discover your spending patterns with gorgeous visualizations that make understanding your finances a joy, not a chore.</div>
        </div>
        <div class="feature">
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <div class="feature-title">Your Privacy Matters</div>
            <div class="feature-desc">Bank-level security with enterprise encryption means your financial data stays yours. We respect your privacy completely.</div>
        </div>
        <div class="feature">
            <div class="icon"><i class="fas fa-magic"></i></div>
            <div class="feature-title">Effortlessly Simple</div>
            <div class="feature-desc">Add transactions in seconds with our intuitive design. Smart categorization learns your habits so you can focus on living.</div>
        </div>
        <div class="feature">
            <div class="icon"><i class="fas fa-heart"></i></div>
            <div class="feature-title">Designed with Love</div>
            <div class="feature-desc">Every feature is crafted with care to make your financial journey positive, encouraging, and genuinely helpful.</div>
        </div>
        <div class="feature">
            <div class="icon"><i class="fas fa-trophy"></i></div>
            <div class="feature-title">Celebrate Progress</div>
            <div class="feature-desc">Set meaningful goals and celebrate every milestone. We believe in recognizing your financial wins, big and small.</div>
        </div>
        <div class="feature">
            <div class="icon"><i class="fas fa-users"></i></div>
            <div class="feature-title">Join Our Community</div>
            <div class="feature-desc">You're not alone in this journey. Connect with others who are also building better financial habits and achieving their dreams.</div>
        </div>
    </div>

    <!-- <div class="testimonial-section">
        <div class="testimonial-title">What Our Family Says</div>
        <div class="testimonials">
            <div class="testimonial">
                <div class="testimonial-text">"This app completely changed how I think about money. It's so encouraging and easy to use - I actually look forward to checking my expenses now!"</div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">L</div>
                    <div class="testimonial-info">
                        <div class="testimonial-name">NDACYAYISABA L.</div>
                        <div class="testimonial-role">Data Scientist</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-text">"Finally, a finance app that doesn't make me feel guilty! The positive approach helped me build better habits without the stress."</div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">M</div>
                    <div class="testimonial-info">
                        <div class="testimonial-name">Christian M.</div>
                        <div class="testimonial-role">Mixologist</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-text">"I love how this app celebrates my small wins. It's like having a supportive friend helping me with my finances every day."</div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">I</div>
                    <div class="testimonial-info">
                        <div class="testimonial-name">GATESI I.</div>
                        <div class="testimonial-role">Highschool Student</div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <footer>
        <div class="footer-content">
            <p>&copy; Expense Tracker. Crafted to help you build a brighter financial future.</p>
            <p style="margin-top: 10px; font-size: 1rem; opacity: 0.8;">Your success is our success. We're cheering you on! 🌟</p>
        </div>
    </footer>

    <script>
        // Enhanced smooth scroll and interaction animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate navigation on scroll
            let lastScrollTop = 0;
            const nav = document.querySelector('.nav');
            
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 100) {
                    nav.style.background = 'rgba(255, 255, 255, 0.15)';
                    nav.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
                } else {
                    nav.style.background = 'rgba(255, 255, 255, 0.1)';
                    nav.style.boxShadow = 'none';
                }
                
                lastScrollTop = scrollTop;
            });

            // Animate elements on scroll with Intersection Observer
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        
                        // Add special animation for feature cards
                        if (entry.target.classList.contains('feature')) {
                            setTimeout(() => {
                                entry.target.style.transform = 'translateY(0) scale(1)';
                            }, 100);
                        }
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.feature, .stat-item, .testimonial').forEach(element => {
                observer.observe(element);
            });

            // Enhanced button click animations with ripple effect
            document.querySelectorAll('.cta-buttons a, .nav-links a').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.background = 'rgba(255,255,255,0.6)';
                    ripple.style.borderRadius = '50%';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    ripple.style.zIndex = '1000';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add hover sound effect simulation (visual feedback)
            document.querySelectorAll('.feature, .stat-item, .testimonial').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.transition = 'all 0.4s ease';
                });
            });

            // Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add welcome animation for returning users
            const isReturningUser = localStorage.getItem('visitedBefore');
            if (isReturningUser) {
                const welcomeMessage = document.querySelector('.welcome-message .text');
                if (welcomeMessage) {
                    welcomeMessage.innerHTML = '<strong>Welcome back!</strong> Ready to continue your amazing financial journey? We\'ve missed you and we\'re excited to help you reach your goals!';
                }
            } else {
                localStorage.setItem('visitedBefore', 'true');
            }

            // Add dynamic emoji rotation
            const emojis = ['🌟', '💎', '🚀', '🎯', '💫'];
            let currentEmojiIndex = 0;
            const heroEmoji = document.querySelector('.hero .emoji');
            
            setInterval(() => {
                heroEmoji.style.transform = 'scale(0)';
                setTimeout(() => {
                    currentEmojiIndex = (currentEmojiIndex + 1) % emojis.length;
                    heroEmoji.textContent = emojis[currentEmojiIndex];
                    heroEmoji.style.transform = 'scale(1)';
                }, 200);
            }, 5000);

            // Add encouraging messages based on time of day
            const hour = new Date().getHours();
            let timeGreeting = '';
            
            if (hour < 12) {
                timeGreeting = 'Good morning! ☀️';
            } else if (hour < 17) {
                timeGreeting = 'Good afternoon! 🌤️';
            } else {
                timeGreeting = 'Good evening! 🌙';
            }
            
            const subtitle = document.querySelector('.hero .subtitle');
            if (subtitle) {
                subtitle.textContent = `${timeGreeting} Welcome to a friendlier way to manage money!`;
            }
        });

        // Add ripple animation keyframes and additional effects
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            /* Add subtle parallax effect to floating shapes */
            .floating-shape {
                will-change: transform;
            }
            
            /* Enhanced button interactions */
            .cta-buttons a {
                will-change: transform, box-shadow;
            }
            
            /* Smooth transitions for all interactive elements */
            .feature, .stat-item, .testimonial {
                will-change: transform, box-shadow;
            }
        `;
        document.head.appendChild(style);

        // Add subtle parallax effect on mouse move
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.floating-shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const xPos = (x - 0.5) * speed;
                const yPos = (y - 0.5) * speed;
                
                shape.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });
    </script>
</body>
</html>
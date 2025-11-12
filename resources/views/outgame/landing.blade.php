<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGameX - Conquer the Universe</title>
    <meta name="description" content="OGameX - The legendary space strategy game. Explore the universe, build your empire, and conquer the stars!">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #0a1929;
            --secondary-blue: #112240;
            --accent-purple: #6c63ff;
            --accent-teal: #00d4ff;
            --accent-cyan: #64ffda;
            --text-light: #e6f1ff;
            --text-muted: #8892b0;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-space: linear-gradient(135deg, #0a1929 0%, #1a237e 50%, #004d40 100%);
        }

        body {
            font-family: 'Exo 2', sans-serif;
            background: var(--primary-blue);
            color: var(--text-light);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Space Background */
        .space-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: var(--gradient-space);
        }

        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 20px 30px, #eee, transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 90px 40px, #fff, transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(255,255,255,0.6), transparent),
                radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,0.4), transparent);
            background-repeat: repeat;
            background-size: 200px 100px;
            animation: sparkle 20s linear infinite;
        }

        @keyframes sparkle {
            from { transform: translateY(0px); }
            to { transform: translateY(-100px); }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 1rem 2rem;
            background: rgba(10, 25, 41, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--accent-teal);
            text-decoration: none;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--accent-teal);
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-teal);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .hero-content {
            max-width: 800px;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
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

        .hero-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(0, 212, 255, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(0, 212, 255, 0.8), 0 0 40px rgba(108, 99, 255, 0.3);
            }
        }

        .hero-subtitle {
            font-size: clamp(1.2rem, 3vw, 2rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: var(--text-light);
            opacity: 0.9;
        }

        .hero-tagline {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--accent-cyan);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            color: var(--text-muted);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Glass Morphism Cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 212, 255, 0.2);
            border-color: rgba(0, 212, 255, 0.3);
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 150px;
            text-align: center;
            font-family: 'Exo 2', sans-serif;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-purple));
            color: white;
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-light);
            border: 2px solid var(--accent-teal);
        }

        .btn-secondary:hover {
            background: var(--accent-teal);
            color: var(--primary-blue);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.4);
        }

        /* Features Section */
        .features {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2rem, 5vw, 3rem);
            text-align: center;
            margin-bottom: 3rem;
            color: var(--accent-teal);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--accent-purple);
            filter: drop-shadow(0 0 10px rgba(108, 99, 255, 0.5));
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .feature-description {
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 4rem 2rem;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .stat-item {
            padding: 2rem;
        }

        .stat-number {
            font-family: 'Orbitron', monospace;
            font-size: 3rem;
            font-weight: 900;
            color: var(--accent-teal);
            display: block;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Footer */
        footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid var(--glass-border);
            background: rgba(10, 25, 41, 0.3);
            backdrop-filter: blur(10px);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-teal);
        }

        .copyright {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero {
                padding: 1rem;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Loading animation */
        .loading {
            opacity: 0;
            animation: fadeIn 1s ease-in forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Space Background -->
    <div class="space-bg">
        <div class="stars"></div>
    </div>

    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <a href="#" class="logo">OGameX</a>
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#stats">Statistics</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">OGameX</h1>
            <p class="hero-subtitle">The Ultimate Space Strategy Experience</p>
            <h2 class="hero-tagline">Conquer the Universe</h2>
            <p class="hero-description">
                Build your galactic empire, forge alliances, and battle across the stars in the most 
                advanced space strategy game ever created. Your destiny awaits among the cosmos.
            </p>
            
            <div class="btn-group">
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <h2 class="section-title">Game Features</h2>
        
        <div class="features-grid">
            <div class="glass-card feature-card loading">
                <div class="feature-icon">🚀</div>
                <h3 class="feature-title">Fleet Management</h3>
                <p class="feature-description">
                    Build and command massive fleets of spaceships. Deploy tactical formations and 
                    engage in epic battles across the galaxy.
                </p>
            </div>
            
            <div class="glass-card feature-card loading">
                <div class="feature-icon">🌍</div>
                <h3 class="feature-title">Planet Colonization</h3>
                <p class="feature-description">
                    Expand your empire by colonizing new planets. Develop infrastructure, extract 
                    resources, and establish profitable trade routes.
                </p>
            </div>
            
            <div class="glass-card feature-card loading">
                <div class="feature-icon">🤝</div>
                <h3 class="feature-title">Alliance Warfare</h3>
                <p class="feature-description">
                    Form powerful alliances with other players. Coordinate attacks, share resources, 
                    and dominate entire star systems together.
                </p>
            </div>
            
            <div class="glass-card feature-card loading">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Advanced Research</h3>
                <p class="feature-description">
                    Unlock cutting-edge technologies through comprehensive research trees. Develop 
                    new weapons, ships, and defensive systems.
                </p>
            </div>
            
            <div class="glass-card feature-card loading">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Strategic Combat</h3>
                <p class="feature-description">
                    Engage in sophisticated real-time battles requiring tactical thinking and 
                    strategic planning to achieve victory.
                </p>
            </div>
            
            <div class="glass-card feature-card loading">
                <div class="feature-icon">🏆</div>
                <h3 class="feature-title">Epic Campaigns</h3>
                <p class="feature-description">
                    Embark on challenging missions and expeditions that will test your skills and 
                    reward you with rare resources and artifacts.
                </p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="stats">
        <h2 class="section-title">Join Millions of Players</h2>
        
        <div class="stats-grid">
            <div class="glass-card stat-item">
                <span class="stat-number">2.5M+</span>
                <span class="stat-label">Active Players</span>
            </div>
            
            <div class="glass-card stat-item">
                <span class="stat-number">50+</span>
                <span class="stat-label">Universes</span>
            </div>
            
            <div class="glass-card stat-item">
                <span class="stat-number">15+</span>
                <span class="stat-label">Years Online</span>
            </div>
            
            <div class="glass-card stat-item">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Action</span>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="features">
        <div class="glass-card" style="text-align: center; max-width: 600px; margin: 0 auto;">
            <h2 class="section-title" style="margin-bottom: 1rem;">Ready to Begin Your Journey?</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.1rem;">
                Join the ranks of space commanders and forge your legend among the stars. 
                The universe is waiting for your command.
            </p>
            <div class="btn-group">
                <a href="{{ route('register') }}" class="btn btn-primary">Start Playing Free</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Game Rules</a>
                <a href="#">Support</a>
                <a href="#">Contact</a>
            </div>
            <p class="copyright">
                © 2025 OGameX. All rights reserved. | Conquer the Universe
            </p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
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

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all feature cards and stat items
        document.querySelectorAll('.glass-card, .feature-card, .stat-item').forEach(card => {
            observer.observe(card);
        });

        // Add parallax effect to stars
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            const stars = document.querySelector('.stars');
            if (stars) {
                stars.style.transform = `translateY(${rate}px)`;
            }
        });

        // Add hover effects to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.05)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.style.background = 'rgba(10, 25, 41, 0.95)';
            } else {
                nav.style.background = 'rgba(10, 25, 41, 0.1)';
            }
        });

        // Loading animation delay
        setTimeout(() => {
            document.querySelectorAll('.loading').forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                }, index * 200);
            });
        }, 500);
    </script>
</body>
</html>
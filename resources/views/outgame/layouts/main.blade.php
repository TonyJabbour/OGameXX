<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!--
     ===========================================
       ____   _____                     __   __
      / __ \ / ____|                    \ \ / /
     | |  | | |  __  __ _ _ __ ___   ___ \ V /
     | |  | | | |_ |/ _` | '_ ` _ \ / _ \ > <
     | |__| | |__| | (_| | | | | | |  __// . \
      \____/ \_____|\__,_|_| |_| |_|\___/_/ \_\
     ===========================================

     Powered by OGameX - Explore the universe! Conquer your enemies!
     GitHub: https://github.com/lanedirt/OGameX
     Version: {{ \OGame\Facades\GitInfoUtil::getAppVersionBranchCommit() }}

    This application is released under the MIT License. For more details, visit the GitHub repository.
-->
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="OGameX - The legendary space browser game! Discover the universe together with thousands of players.">
    <meta name="keywords" content="OGame, browser game, online game, space game, MMOG, free to play, strategy">
    <meta name="author" content="OGameX">
    <meta name="robots" content="index, follow">
    <meta name="language" content="{{ app()->getLocale() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ config('app.name', 'OGameX') }}">
    <meta property="og:description" content="The legendary space browser game! Discover the universe together with thousands of players.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name', 'OGameX') }}">
    <meta name="twitter:description" content="The legendary space browser game! Discover the universe together with thousands of players.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/outgame/20da7e6c416e6cd5f8544a73f588e5.png">
    <link rel="apple-touch-icon" href="/img/outgame/20da7e6c416e6cd5f8544a73f588e5.png">
    
    <title>{{ config('app.name', 'OGameX') }}</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ mix('css/outgame.css') }}">
    <link rel="stylesheet" href="{{ mix('css/modern-auth.css') }}">
    
    <!-- JavaScript -->
    <script type="module" src="{{ mix('js/outgame.min.js') }}"></script>
    
    <script>
        // Global configuration for modern OGameX
        window.OGameX = {
            config: {
                emailOnlySignup: true,
                emailOnlyLogin: true,
                csrfToken: '{{ csrf_token() }}',
                appName: '{{ config('app.name', 'OGameX') }}'
            },
            
            // Utility functions for form validation
            validators: {
                email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                password: /^.{4,20}$/,
                username: /^[a-zA-Z0-9_-]{3,20}$/
            },
            
            // Initialize modern interactive elements
            init() {
                this.initMobileMenu();
                this.initSmoothScroll();
                this.initTooltips();
                this.initZebraTables();
                this.initHoverEffects();
            },
            
            initMobileMenu() {
                const menuToggle = document.querySelector('.mobile-menu-toggle');
                const nav = document.querySelector('nav');
                
                if (menuToggle && nav) {
                    menuToggle.addEventListener('click', () => {
                        nav.classList.toggle('active');
                        menuToggle.classList.toggle('active');
                    });
                }
            },
            
            initSmoothScroll() {
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    });
                });
            },
            
            initTooltips() {
                const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
                tooltipTriggers.forEach(trigger => {
                    trigger.addEventListener('mouseenter', (e) => this.showTooltip(e));
                    trigger.addEventListener('mouseleave', () => this.hideTooltip());
                });
            },
            
            initZebraTables() {
                document.querySelectorAll('.zebra tr:nth-child(odd)').forEach(row => {
                    row.classList.add('alt');
                });
            },
            
            initHoverEffects() {
                // Add hover effects for interactive elements
                document.querySelectorAll('.btn, button, .link-btn').forEach(element => {
                    element.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    
                    element.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });
            },
            
            showTooltip(e) {
                const tooltip = e.target.getAttribute('data-tooltip');
                // Implement tooltip display logic
            },
            
            hideTooltip() {
                // Implement tooltip hide logic
            }
        };
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            OGameX.init();
        });
    </script>
</head>
<body class='en'>
    <!-- IE6 Warning (keeping original for compatibility) -->
    <div id="dieIE6">
        <div class="logo_gf"></div>
        <div class="logo_ogame"></div>
        <h1 class="ie6_header">Your browser is not up to date.</h1>
        <p class="ie6_desc">Your Internet Explorer version does not correspond to the existing standards and is not supported by this website anymore.</p>
        <p class="ie6_desc_box">To use this website please update your web browser to a current version or use another web browser. If you are already using the latest version, please reload the page to display it properly.</p>
        <p class="ie6_desc">Here's a list of the most popular browsers. Click on one of the symbols to get to the download page:</p>
        <div class="browser_downloads">
            <a href="http://windows.microsoft.com/en-GB/internet-explorer/download-ie" target="_blank" class="browserimg ie">IE 8+</a>
            <a href="http://www.mozilla.org/de/firefox/" target="_blank" class="browserimg firefox">Firefox 16+</a>
            <a href="http://www.google.com/chrome" target="_blank" class="browserimg chrome">Chrome 23+</a>
            <a href="http://www.apple.com/de/safari/" target="_blank" class="browserimg safari">Safari 5+</a>
        </div>
    </div>
    
    <!-- Products Section -->
    <div class="products">
        <div id="pagefoldtarget"></div>
    </div>
    
    <!-- Main Layout Container -->
    <div id="app">
        <!-- Modern Navigation Header -->
        <header class="main-header">
            <nav class="navbar" role="navigation" aria-label="Main navigation">
                <div class="navbar-brand">
                    <h1>
                        <a href="{{ route('welcome') }}" title="OGameX - Conquer the universe">
                            <span class="logo-text">OGame</span>
                            <span class="logo-subtitle">Conquer the universe</span>
                        </a>
                    </h1>
                </div>
                
                <button class="mobile-menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                
                <div class="navbar-nav">
                    <ul class="nav-links">
                        <li><a href="{{ route('welcome') }}">Home</a></li>
                        <li><a href="#" title="Game Features">Features</a></li>
                        <li><a href="#" title="Game Rules">Rules</a></li>
                        <li><a href="#" title="Contact Us">Contact</a></li>
                    </ul>
                    
                    <div class="auth-buttons">
                        <a href="{{ route('login') }}" class="btn btn-outline" title="Login to your account">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary" title="Create a new account">Register</a>
                    </div>
                </div>
            </nav>
        </header>
        
        <!-- Main Content Area -->
        <main id="main-content" role="main">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer id="footer" role="contentinfo">
            <div class="footer-content">
                <div class="footer-main">
                    <div class="footer-brand">
                        <h2 class="footer-title">
                            <span class="logo-text">OGame</span>
                        </h2>
                        <p class="footer-tagline">Explore the universe. Conquer your enemies.</p>
                    </div>
                    
                    <div class="footer-links">
                        <div class="link-group">
                            <h3>Legal</h3>
                            <ul>
                                <li><a href="#" target="_blank">Terms & Conditions</a></li>
                                <li><a href="#" target="_blank">Privacy Policy</a></li>
                                <li><a href="#" target="_blank">Legal Notice</a></li>
                            </ul>
                        </div>
                        
                        <div class="link-group">
                            <h3>Game</h3>
                            <ul>
                                <li><a href="#" title="Game Rules">Rules</a></li>
                                <li><a href="#" title="Contact Support">Contact</a></li>
                                <li><a href="#" title="Help Center">Help</a></li>
                            </ul>
                        </div>
                        
                        <div class="link-group">
                            <h3>Community</h3>
                            <ul>
                                <li><a href="#" target="_blank" title="Follow us on Google+">Google+</a></li>
                                <li><a href="#" target="_blank" title="Follow us on Facebook">Facebook</a></li>
                                <li><a href="#" target="_blank" title="Follow us on Twitter">Twitter</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p class="copyright">&copy; {{ date('Y') }} OGameX. All rights reserved. {{ \OGame\Facades\GitInfoUtil::getAppVersion() }}</p>
                    <p class="powered-by">
                        Powered by <a href="https://github.com/lanedirt/OGameX" target="_blank" rel="noopener">OGameX</a>
                    </p>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Additional JavaScript for Enhanced Functionality -->
    <script>
        // Enhance user experience with modern interactions
        document.addEventListener('DOMContentLoaded', () => {
            // Add loading state management
            const links = document.querySelectorAll('a[href]:not([target="_blank"])');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    if (!this.classList.contains('no-loader')) {
                        document.body.classList.add('loading');
                    }
                });
            });
            
            // Smooth scroll to top functionality
            const scrollTopBtn = document.createElement('button');
            scrollTopBtn.innerHTML = '↑';
            scrollTopBtn.className = 'scroll-to-top';
            scrollTopBtn.setAttribute('aria-label', 'Scroll to top');
            document.body.appendChild(scrollTopBtn);
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollTopBtn.classList.add('visible');
                } else {
                    scrollTopBtn.classList.remove('visible');
                }
            });
            
            scrollTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            
            // Keyboard navigation enhancement
            document.addEventListener('keydown', (e) => {
                // ESC key to close mobile menu
                if (e.key === 'Escape') {
                    const nav = document.querySelector('nav');
                    const menuToggle = document.querySelector('.mobile-menu-toggle');
                    if (nav && nav.classList.contains('active')) {
                        nav.classList.remove('active');
                        menuToggle.classList.remove('active');
                        menuToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
        
        // Error handling for JavaScript
        window.addEventListener('error', (e) => {
            console.error('JavaScript error:', e.error);
            // Could send error reports to logging service in production
        });
        
        // Performance monitoring
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                console.log(`Page loaded in ${loadTime}ms`);
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>

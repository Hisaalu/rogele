<?php
// File: /views/layouts/footer.php
?>
    </main>

    <?php if (!isset($hideFooter) || !$hideFooter): ?>
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <!-- About Column -->
                    <div class="footer-col">
                        <div class="footer-logo">
                            <div class="logo-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="logo-text">
                                <span class="logo-main">Rays of Grace</span>
                                <span class="logo-sub">Junior School</span>
                            </div>
                        </div>
                        <p class="footer-about">
                            Providing quality education through innovative e-learning solutions since 2010.
                        </p>
                        <div class="footer-social">
                            <a href="https://www.facebook.com/profile.php?id=100057146993995" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://x.com/raysofgracejr" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.linkedin.com/company/raysofgracejr" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            <a href="https://www.youtube.com/@raysofgraceacademy" target="_blank"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-col">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>/"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="#about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="#courses"><i class="fas fa-chevron-right"></i> Courses</a></li>
                            <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div class="footer-col">
                        <h4>Contact Us</h4>
                        <ul class="contact-info">
                            <li><i class="fas fa-phone"></i> +256 778 086 883</li>
                            <li><i class="fas fa-envelope"></i> info@raysofgrace.cac.ug</li>
                            <li><i class="fas fa-map-marker"></i> Kampala, Uganda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Rays of Grace Junior School | All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript for Mobile Menu -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileClose = document.getElementById('mobileClose');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const body = document.body;

        // Function to open menu
        function openMenu() {
            mobileToggle.classList.add('active');
            mobileMenu.classList.add('active');
            mobileOverlay.classList.add('active');
            body.classList.add('menu-open');
        }

        // Function to close menu
        function closeMenu() {
            mobileToggle.classList.remove('active');
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            body.classList.remove('menu-open');
        }

        // Toggle menu on hamburger click
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (mobileMenu.classList.contains('active')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        // Close menu on close button click
        if (mobileClose) {
            mobileClose.addEventListener('click', function(e) {
                e.preventDefault();
                closeMenu();
            });
        }

        // Close menu on overlay click
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                closeMenu();
            });
        }

        // Close menu when clicking on a link
        const mobileLinks = document.querySelectorAll('.mobile-nav-links a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMenu();
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
                closeMenu();
            }
        });

        // Prevent menu from closing when clicking inside it
        if (mobileMenu) {
            mobileMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    if (mobileMenu.classList.contains('active')) {
                        closeMenu();
                    }
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
    </script>

    <style>
        /* Footer Styles */
        .site-footer {
            background: linear-gradient(135deg, #1E293B, #0F172A);
            color: white;
            padding: 60px 0 20px;
            width: 100%;
            margin-top: 60px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .footer-logo .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            transform: rotate(-5deg);
        }

        .footer-logo .logo-main {
            color: white;
            font-size: 1.2rem;
        }

        .footer-logo .logo-sub {
            color: #94A3B8;
        }

        .footer-about {
            color: #94A3B8;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .footer-social {
            display: flex;
            gap: 15px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: linear-gradient(135deg, #8B5CF6, #F97316);
            transform: translateY(-3px);
        }

        .footer-col h4 {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #8B5CF6, #F97316);
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            color: #94A3B8;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .footer-col ul li a:hover {
            color: #F97316;
            transform: translateX(5px);
        }

        .footer-col ul li a i {
            font-size: 0.8rem;
            color: #8B5CF6;
        }

        .contact-info li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94A3B8;
            margin-bottom: 15px;
        }

        .contact-info i {
            color: #8B5CF6;
            width: 20px;
            font-size: 1.1rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .footer-bottom p {
            color: #94A3B8;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }

            .footer-logo {
                justify-content: center;
            }

            .footer-social {
                justify-content: center;
            }

            .footer-col h4::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .footer-col ul li a {
                justify-content: center;
            }

            .contact-info li {
                justify-content: center;
            }
        }

        @media (prefers-color-scheme: dark) {
            .footer-social a {
                background: rgba(255,255,255,0.05);
            }
        }
    </style>
</body>
</html>
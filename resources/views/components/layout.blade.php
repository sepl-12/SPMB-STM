<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'PPDB SMK Muh 1 - Penerimaan Peserta Didik Baru Online 2025/2026' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* FAQ Animation */
        .faq-content {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.3s ease-in-out;
        }
        
        .faq-content:not(.faq-closed) {
            opacity: 1;
        }
        
        .faq-icon {
            transition: transform 0.3s ease-in-out;
        }
        
        /* Glassmorphism effect enhancement */
        header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
        }
    </style>
    
    @stack('styles')
</head>
<body class="overflow-x-hidden">
    {{ $slot }}
    
    <script>
        // Mobile menu toggle with animation
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuToggle && mobileMenu) {
            let isMenuOpen = false;
            
            mobileMenuToggle.addEventListener('click', function() {
                if (!isMenuOpen) {
                    // Open menu
                    mobileMenu.classList.remove('hidden');
                    setTimeout(() => {
                        mobileMenu.style.maxHeight = '500px';
                        mobileMenu.style.opacity = '1';
                    }, 10);
                    isMenuOpen = true;
                } else {
                    // Close menu
                    mobileMenu.style.maxHeight = '0';
                    mobileMenu.style.opacity = '0';
                    setTimeout(() => {
                        mobileMenu.classList.add('hidden');
                    }, 300);
                    isMenuOpen = false;
                }
            });
            
            // Close mobile menu when clicking a link
            const mobileMenuLinks = mobileMenu.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.style.maxHeight = '0';
                    mobileMenu.style.opacity = '0';
                    setTimeout(() => {
                        mobileMenu.classList.add('hidden');
                    }, 300);
                    isMenuOpen = false;
                });
            });
        }
        
        // FAQ Accordion with smooth animation
        const faqToggles = document.querySelectorAll('.faq-toggle');
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('.faq-icon');
                const isOpen = !content.classList.contains('faq-closed');
                
                // Close other FAQs (optional - remove if you want multiple open at once)
                faqToggles.forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        const otherContent = otherToggle.nextElementSibling;
                        const otherIcon = otherToggle.querySelector('.faq-icon');
                        if (!otherContent.classList.contains('faq-closed')) {
                            otherContent.classList.add('faq-closed');
                            otherContent.style.maxHeight = '0';
                            otherContent.style.opacity = '0';
                            otherIcon.classList.remove('rotate-180');
                        }
                    }
                });
                
                // Toggle current FAQ with animation
                if (isOpen) {
                    // Close current FAQ
                    content.classList.add('faq-closed');
                    content.style.maxHeight = '0';
                    content.style.opacity = '0';
                    icon.classList.remove('rotate-180');
                } else {
                    // Open current FAQ
                    content.classList.remove('faq-closed');
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    icon.classList.add('rotate-180');
                }
            });
        });
        
        // Smooth scroll
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
    </script>
    
    @stack('scripts')
</body>
</html>

// File: /public/js/header.js
$(document).ready(function() {
    // Mobile menu toggle
    $('#mobileToggle').click(function() {
        $(this).find('.hamburger').toggleClass('active');
        $('#mobileMenu').toggleClass('active');
        $('#mobileOverlay').toggleClass('active');
        $('body').toggleClass('no-scroll');
    });
    
    // Close mobile menu
    $('#mobileClose, #mobileOverlay').click(function() {
        $('#mobileToggle .hamburger').removeClass('active');
        $('#mobileMenu').removeClass('active');
        $('#mobileOverlay').removeClass('active');
        $('body').removeClass('no-scroll');
    });
    
    // Sticky header on scroll
    let lastScroll = 0;
    $(window).scroll(function() {
        const currentScroll = $(this).scrollTop();
        const header = $('.site-header');
        
        if (currentScroll > 100) {
            if (currentScroll > lastScroll) {
                // Scrolling down
                header.addClass('header-hidden');
            } else {
                // Scrolling up
                header.removeClass('header-hidden');
            }
        } else {
            header.removeClass('header-hidden');
        }
        
        lastScroll = currentScroll;
    });
    
    // Dropdown on hover for desktop
    if ($(window).width() > 992) {
        $('.user-dropdown').hover(
            function() {
                $(this).find('.dropdown-content').stop(true, true).fadeIn(300);
            },
            function() {
                $(this).find('.dropdown-content').stop(true, true).fadeOut(300);
            }
        );
    }
    
    // Dropdown on click for mobile
    if ($(window).width() <= 992) {
        $('.dropdown-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).siblings('.dropdown-content').slideToggle(300);
            $(this).find('.dropdown-arrow').toggleClass('rotate');
        });
    }
    
    // Notification bell click
    $('.notification-bell').click(function() {
        // Show notifications panel (you can implement this)
        $(this).find('.notification-badge').fadeOut();
        showNotification('No new notifications', 'info');
    });
    
    // Active nav item based on scroll position
    $(window).scroll(function() {
        const scrollPos = $(this).scrollTop();
        
        $('section[id]').each(function() {
            const section = $(this);
            const sectionTop = section.offset().top - 100;
            const sectionBottom = sectionTop + section.outerHeight();
            
            if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                const sectionId = section.attr('id');
                $('.nav-item a').removeClass('active');
                $(`.nav-item a[href="#${sectionId}"]`).addClass('active');
            }
        });
    });
    
    // Search functionality (if implemented)
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        }
    });
    
    function performSearch(query) {
        // Implement search AJAX call
        console.log('Searching for:', query);
    }
    
    // Prevent body scroll when mobile menu is open
    $('body').on('touchmove', function(e) {
        if ($('#mobileMenu').hasClass('active')) {
            e.preventDefault();
        }
    });
    
    // Close mobile menu on window resize (if open)
    $(window).resize(function() {
        if ($(window).width() > 992) {
            if ($('#mobileMenu').hasClass('active')) {
                $('#mobileToggle .hamburger').removeClass('active');
                $('#mobileMenu').removeClass('active');
                $('#mobileOverlay').removeClass('active');
                $('body').removeClass('no-scroll');
            }
        }
    });
    
    // Smooth scroll for navigation links
    $('a[href^="#"]').not('[href="#"]').click(function(e) {
        e.preventDefault();
        
        const target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
            
            // Close mobile menu if open
            if ($('#mobileMenu').hasClass('active')) {
                $('#mobileToggle .hamburger').removeClass('active');
                $('#mobileMenu').removeClass('active');
                $('#mobileOverlay').removeClass('active');
                $('body').removeClass('no-scroll');
            }
        }
    });
});
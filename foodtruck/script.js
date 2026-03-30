document.addEventListener('DOMContentLoaded', () => {
    // Mobile Navigation Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = menuToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Close mobile menu when clicking a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        });
    });

    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Intersection Observer for Scroll Animations
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Elements to animate on scroll
    const animatedElements = document.querySelectorAll('.card, .feature-item, .sauce-box, .image-stack');
    animatedElements.forEach(el => {
        el.style.opacity = '0'; // Hide initially
        el.style.animationFillMode = 'forwards'; // Keep state after animation
        observer.observe(el);
    });

    // Truck Animation Sync
    const truck = document.querySelector('.moving-truck');
    const roadStops = document.querySelectorAll('.road-stop');

    if (truck && roadStops.length > 0) {
        function checkTruckPosition() {
            const truckRect = truck.getBoundingClientRect();
            const truckCenterY = truckRect.top + truckRect.height / 2;

            roadStops.forEach(stop => {
                const stopRect = stop.getBoundingClientRect();
                // Check if truck center is within the vertical range of the stop with some buffer
                if (truckCenterY >= stopRect.top - 50 && truckCenterY <= stopRect.bottom + 50) {
                    stop.classList.add('active');
                } else {
                    stop.classList.remove('active');
                }
            });

            requestAnimationFrame(checkTruckPosition);
        }

        // Start the loop
        requestAnimationFrame(checkTruckPosition);
    }

    // Mute Toggle Logic
    const video = document.getElementById('hero-video');
    const muteBtn = document.getElementById('muteToggle');
    const bgMusic = document.getElementById('bg-music');

    if (video && muteBtn) {
        console.log('Mute toggle initialized');

        // Attempt to play background music immediately
        if (bgMusic) {
            bgMusic.volume = 0.5;
            // Try to play, but catch error if browser blocks it
            bgMusic.play().catch(e => {
                console.log('Autoplay blocked:', e);
                // If blocked, we might need to mute video and show mute icon
                video.muted = true;
                muteBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
                muteBtn.setAttribute('aria-label', 'Unmute Video');
            });
        }

        muteBtn.addEventListener('click', () => {
            console.log('Mute toggle clicked. Current state:', video.muted);
            if (video.muted) {
                // Unmute video and play background music
                video.muted = false;
                if (bgMusic) {
                    bgMusic.play().catch(e => console.log('Audio play failed:', e));
                }
                muteBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
                muteBtn.setAttribute('aria-label', 'Mute Video');
                console.log('Video unmuted, music playing');
            } else {
                // Mute video and pause background music
                video.muted = true;
                if (bgMusic) {
                    bgMusic.pause();
                }
                muteBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
                muteBtn.setAttribute('aria-label', 'Unmute Video');
                console.log('Video muted, music paused');
            }
        });
    } else {
        console.error('Video or Mute Button not found');
    }

    // Limit Audio to Hero Section
    const heroSection = document.getElementById('home');

    if (heroSection && bgMusic && video) {
        const audioObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    // Left the viewport - pause music and video
                    if (!bgMusic.paused) {
                        bgMusic.pause();
                    }
                    if (!video.paused) {
                        video.pause();
                    }
                    console.log('Hero section out of view - media paused');
                } else {
                    // Entered the viewport - play video
                    if (video.paused) {
                        video.play().catch(e => console.log('Video play failed:', e));
                    }
                    // Resume music if video is unmuted
                    if (!video.muted && bgMusic.paused) {
                        bgMusic.play().catch(e => console.log('Audio resume failed:', e));
                    }
                    console.log('Hero section in view - media resumed');
                }
            });
        }, { threshold: 0.1 }); // Trigger when 10% of the hero is visible

        audioObserver.observe(heroSection);
    }
});

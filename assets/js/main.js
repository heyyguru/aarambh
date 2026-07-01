/**
 * =====================================================
 * AARAMBH by HeyyGuru — Main JavaScript
 * Domain: aarambh.heyyguru.in
 * =====================================================
 */

(function () {
    'use strict';

    // ---------------------------------------------------
    // Configuration
    // ---------------------------------------------------
    const CONFIG = {
        razorpayKeyId: window.AARAMBH_CONFIG ? window.AARAMBH_CONFIG.razorpayKeyId : '',
        courseName: 'AARAMBH Course',
        coursePrice: 1900, // in paise
        coursePriceDisplay: '₹19',
        supportEmail: 'academics@heyyguru.in',
        supportPhone: '7676798650',
        counterBase: 243,
        counterLaunchDate: '2026-06-18',
        counterDailyIncrement: 24,
        counterHourlyVariation: 5,
    };

    // ---------------------------------------------------
    // Fake Names for Toast Notifications
    // ---------------------------------------------------
    const FAKE_STUDENTS = [
        { name: 'Aarav from Mumbai', time: '2 minutes ago' },
        { name: 'Priya from Delhi', time: '5 minutes ago' },
        { name: 'Rohan from Bangalore', time: '3 minutes ago' },
        { name: 'Ananya from Pune', time: '8 minutes ago' },
        { name: 'Karthik from Chennai', time: '1 minute ago' },
        { name: 'Ishita from Kolkata', time: '6 minutes ago' },
        { name: 'Arjun from Hyderabad', time: '4 minutes ago' },
        { name: 'Sneha from Jaipur', time: '7 minutes ago' },
        { name: 'Vivaan from Lucknow', time: '3 minutes ago' },
        { name: 'Diya from Ahmedabad', time: '9 minutes ago' },
        { name: 'Advait from Nagpur', time: '2 minutes ago' },
        { name: 'Kavya from Indore', time: '5 minutes ago' },
        { name: 'Reyansh from Bhopal', time: '4 minutes ago' },
        { name: 'Anika from Chandigarh', time: '1 minute ago' },
        { name: 'Shaurya from Patna', time: '6 minutes ago' },
        { name: 'Myra from Surat', time: '3 minutes ago' },
        { name: 'Arnav from Ranchi', time: '8 minutes ago' },
        { name: 'Saanvi from Varanasi', time: '2 minutes ago' },
        { name: 'Kabir from Dehradun', time: '5 minutes ago' },
        { name: 'Riya from Guwahati', time: '7 minutes ago' },
    ];

    // Ticker data
    const TICKER_DATA = [
        'Rahul from Delhi enrolled in AARAMBH',
        'Priya from Mumbai just joined!',
        'Amit from Bangalore started learning',
        'Sneha from Pune enrolled today',
        'Karthik from Chennai just paid ₹19',
        'Ananya from Kolkata joined AARAMBH',
        'Rohit from Hyderabad enrolled',
        'Isha from Jaipur started today',
        'Vikram from Lucknow just joined!',
        'Meera from Ahmedabad enrolled',
    ];

    // ---------------------------------------------------
    // DOM Elements
    // ---------------------------------------------------
    const $ = (sel) => document.querySelector(sel);
    const $$ = (sel) => document.querySelectorAll(sel);

    // ---------------------------------------------------
    // Initialization
    // ---------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        initNavbar();
        initFakeCounter();
        initCountdownTimer();
        initScrollReveal();
        initFAQ();
        initForm();
        initTicker();
        initToastNotifications();
        initTestimonialSlider();
        initFeaturesSlider();
        captureUTM();
    });

    // ---------------------------------------------------
    // Navbar Scroll Effect
    // ---------------------------------------------------
    function initNavbar() {
        const navbar = $('#navbar');
        const navToggle = $('#navToggle');
        const navLinks = $('#navLinks');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 80) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile toggle
        if (navToggle) {
            navToggle.addEventListener('click', () => {
                navLinks.classList.toggle('open');
                // Animate hamburger
                const spans = navToggle.querySelectorAll('span');
                if (navLinks.classList.contains('open')) {
                    spans[0].style.transform = 'rotate(45deg) translateY(7px)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'rotate(-45deg) translateY(-7px)';
                } else {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            });

            // Close nav on link click
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('open');
                    const spans = navToggle.querySelectorAll('span');
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                });
            });
        }
    }

    // ---------------------------------------------------
    // Fake Enrollment Counter
    // ---------------------------------------------------
    function initFakeCounter() {
        const now = new Date();
        const launch = new Date(CONFIG.counterLaunchDate);
        const daysSinceLaunch = Math.floor((now - launch) / (1000 * 60 * 60 * 24));
        const hourOfDay = now.getHours();

        // Base + daily growth + hourly variation
        const dailyGrowth = daysSinceLaunch * CONFIG.counterDailyIncrement;
        const hourlyVar = Math.floor(Math.sin(hourOfDay * 0.5) * CONFIG.counterHourlyVariation + CONFIG.counterHourlyVariation);
        const minuteVar = Math.floor(now.getMinutes() / 10);

        const totalCount = CONFIG.counterBase + dailyGrowth + hourlyVar + minuteVar;

        // Format number with commas
        const formatted = totalCount.toLocaleString('en-IN');

        // Update all counter elements
        const liveCounter = $('#live-counter');
        const heroCount = $('#hero-count');

        if (liveCounter) animateCounter(liveCounter, totalCount);
        if (heroCount) heroCount.textContent = formatted;

        // Increment every 3 to 8 seconds so it's highly visible
        setInterval(() => {
            const currentCount = parseInt(liveCounter?.textContent.replace(/,/g, '') || totalCount);
            const newCount = currentCount + Math.floor(Math.random() * 3) + 1; // Increase by 1 to 3
            if (liveCounter) liveCounter.textContent = newCount.toLocaleString('en-IN');
            if (heroCount) heroCount.textContent = newCount.toLocaleString('en-IN');
        }, Math.random() * 5000 + 3000);
    }

    function animateCounter(element, target) {
        let current = 0;
        const increment = Math.ceil(target / 60);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = current.toLocaleString('en-IN');
        }, 30);
    }

    // ---------------------------------------------------
    // Countdown Timer (Urgency)
    // ---------------------------------------------------
    function initCountdownTimer() {
        // Reset every 24 hours from midnight (for existing timer if any)
        function getTimeRemaining() {
            const now = new Date();
            const endOfDay = new Date();
            endOfDay.setHours(23, 59, 59, 999);
            const diff = endOfDay - now;

            return {
                hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
                minutes: Math.floor((diff / (1000 * 60)) % 60),
                seconds: Math.floor((diff / 1000) % 60)
            };
        }

        let formCountdownSeconds = 10 * 60; // 10 minutes

        function updateTimer() {
            const time = getTimeRemaining();
            const hours = $('#timer-hours');
            const minutes = $('#timer-minutes');
            const seconds = $('#timer-seconds');

            if (hours) hours.textContent = String(time.hours).padStart(2, '0');
            if (minutes) minutes.textContent = String(time.minutes).padStart(2, '0');
            if (seconds) seconds.textContent = String(time.seconds).padStart(2, '0');
            
            // New Form Countdown (10 minutes)
            const formTimer = $('#countdown-timer');
            if (formTimer) {
                if (formCountdownSeconds > 0) formCountdownSeconds--;
                const m = Math.floor(formCountdownSeconds / 60);
                const s = formCountdownSeconds % 60;
                formTimer.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // ---------------------------------------------------
    // Scroll Reveal Animation
    // ---------------------------------------------------
    function initScrollReveal() {
        const revealElements = $$('.reveal');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('active');
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => observer.observe(el));
    }

    // ---------------------------------------------------
    // FAQ Accordion
    // ---------------------------------------------------
    function initFAQ() {
        $$('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const item = button.parentElement;
                const isActive = item.classList.contains('active');

                // Close all
                $$('.faq-item').forEach(i => i.classList.remove('active'));

                // Toggle clicked
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    }

    // ---------------------------------------------------
    // Enrollment Form
    // ---------------------------------------------------
    function initForm() {
        const form = $('#enrollmentForm');
        if (!form) return;

        // Chip selection logic
        const chips = document.querySelectorAll('.class-chip');
        const hiddenInput = $('#student_class');
        const phoneInput = $('#phone');
        
        function trackVisitor() {
            if (!hiddenInput || !phoneInput) return;
            const phoneVal = phoneInput.value.trim();
            const classVal = hiddenInput.value;
            const phoneRegex = /^[6-9]\d{9}$/;
            
            // If we have a valid phone and a class selected, track silently
            if (classVal && phoneRegex.test(phoneVal)) {
                // Gather UTMs if they exist
                const utmSource = $('#utm_source') ? $('#utm_source').value : '';
                const utmMedium = $('#utm_medium') ? $('#utm_medium').value : '';
                const utmCampaign = $('#utm_campaign') ? $('#utm_campaign').value : '';
                
                fetch('track_visitor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        phone: phoneVal, 
                        student_class: classVal,
                        utm_source: utmSource,
                        utm_medium: utmMedium,
                        utm_campaign: utmCampaign
                    })
                }).catch(err => console.error('Tracking failed', err));
            }
        }
        
        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                if (hiddenInput) {
                    hiddenInput.value = chip.dataset.value;
                    clearFieldError('class');
                    
                    // Auto-focus phone input and attempt to track
                    if (phoneInput) {
                        phoneInput.focus();
                        trackVisitor();
                    }
                }
            });
        });
        
        // Track when phone is typed (if class is already selected)
        if (phoneInput) {
            phoneInput.addEventListener('keyup', () => {
                if (phoneInput.value.trim().length === 10) {
                    trackVisitor();
                }
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate
            if (!validateForm()) return;

            const submitBtn = $('#submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            try {
                // Step 1: Submit lead data
                const formData = new FormData(form);
                const response = await fetch('submit_lead.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    showFormError(data.message || 'Something went wrong. Please try again.');
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                // Step 2: Create Razorpay order
                const orderResponse = await fetch('create_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ student_id: data.student_id })
                });

                const orderData = await orderResponse.json();

                if (!orderData.success) {
                    showFormError(orderData.message || 'Failed to create payment order.');
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                // Step 3: Open Razorpay Checkout
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                openRazorpay(orderData, data.student_id, formData);

            } catch (error) {
                console.error('Form submission error:', error);
                showFormError('Network error. Please check your connection and try again.');
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });
    }

    function validateForm() {
        let isValid = true;

        // Name
        const name = $('#student_name');
        if (name) {
            clearFieldError('name');
        }

        // Email
        const email = $('#email');
        if (email) {
            clearFieldError('email');
        }

        // Phone
        const phone = $('#phone');
        const phoneRegex = /^[6-9]\d{9}$/;
        if (!phone.value.trim() || !phoneRegex.test(phone.value.trim())) {
            showFieldError('phone', 'Please enter a valid 10-digit mobile number');
            isValid = false;
        } else {
            clearFieldError('phone');
        }

        // Class
        const studentClass = $('#student_class');
        if (!studentClass.value) {
            showFieldError('class', 'Please select a class');
            isValid = false;
        } else {
            clearFieldError('class');
        }

        return isValid;
    }

    function showFieldError(field, message) {
        const errorEl = $(`#error-${field}`);
        const inputEl = field === 'name' ? $('#student_name') :
                        field === 'email' ? $('#email') :
                        field === 'phone' ? $('#phone') :
                        field === 'class' ? $('#student_class') : null;

        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
        if (inputEl) inputEl.classList.add('error');
    }

    function clearFieldError(field) {
        const errorEl = $(`#error-${field}`);
        const inputEl = field === 'name' ? $('#student_name') :
                        field === 'email' ? $('#email') :
                        field === 'phone' ? $('#phone') :
                        field === 'class' ? $('#student_class') : null;

        if (errorEl) errorEl.classList.remove('show');
        if (inputEl) inputEl.classList.remove('error');
    }

    function showFormError(message) {
        // Create temporary error message
        const container = $('#enrollment-form-container');
        let errorDiv = container.querySelector('.form-global-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-global-error';
            errorDiv.style.cssText = 'background:#FEE2E2;color:#DC2626;padding:0.8rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem;text-align:center;';
            container.insertBefore(errorDiv, container.firstChild);
        }
        errorDiv.textContent = message;
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // ---------------------------------------------------
    // Razorpay Checkout
    // ---------------------------------------------------
    function openRazorpay(orderData, studentId, formData) {
        // DEMO MODE: If key contains XXXXX, use Fake Razorpay
        if (CONFIG.razorpayKeyId.includes('XXXXX')) {
            showDemoRazorpay(studentId, formData);
            return;
        }

        const options = {
            key: CONFIG.razorpayKeyId,
            amount: CONFIG.coursePrice,
            currency: 'INR',
            name: 'HeyyGuru',
            description: CONFIG.courseName + ' - 6 Days Learning Experience',
            order_id: orderData.order_id,
            prefill: {
                name: formData.get('student_name'),
                email: formData.get('email'),
                contact: '+91' + formData.get('phone')
            },
            notes: {
                student_id: studentId,
                course: 'AARAMBH'
            },
            theme: {
                color: '#4A6CF7',
                backdrop_color: 'rgba(0,0,0,0.6)'
            },
            modal: {
                ondismiss: function () {
                    console.log('Payment modal closed');
                }
            },
            handler: function (response) {
                // Payment successful — verify on server
                verifyPayment(response, studentId);
            }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response) {
            console.error('Payment failed:', response.error);
            showFormError('Payment failed: ' + response.error.description + '. Please try again.');
        });
        rzp.open();
    }

    function showDemoRazorpay(studentId, formData) {
        // Create a fake processing overlay
        const demoOverlay = document.createElement('div');
        demoOverlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;color:white;font-family:sans-serif;flex-direction:column;';
        demoOverlay.innerHTML = `
            <div style="background:white;color:black;padding:2rem;border-radius:12px;text-align:center;max-width:400px;width:90%;">
                <h3 style="margin-bottom:1rem;color:#4A6CF7;">Razorpay Demo Mode</h3>
                <p style="margin-bottom:1.5rem;color:#666;">Simulating a successful payment of ₹19...</p>
                <div class="loader" style="border:4px solid #f3f3f3;border-top:4px solid #4A6CF7;border-radius:50%;width:40px;height:40px;animation:spin 1s linear infinite;margin:0 auto 1.5rem;"></div>
                <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
            </div>
        `;
        document.body.appendChild(demoOverlay);

        // Simulate network delay then success
        setTimeout(() => {
            demoOverlay.remove();
            
            // Fake Razorpay response
            const fakeResponse = {
                razorpay_order_id: 'order_demo_' + Math.floor(Math.random()*10000),
                razorpay_payment_id: 'pay_demo_' + Math.floor(Math.random()*10000),
                razorpay_signature: 'demo_signature'
            };
            
            // Bypass verification script for demo, show success directly
            const successOverlay = $('#success-overlay');
            const successEmail = $('#success-email');
            if (successEmail) successEmail.textContent = formData.get('email');
            if (successOverlay) successOverlay.classList.add('show');
            $('#enrollmentForm').reset();
            
        }, 2000);
    }

    async function verifyPayment(paymentData, studentId) {
        try {
            const response = await fetch('verify_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    razorpay_order_id: paymentData.razorpay_order_id,
                    razorpay_payment_id: paymentData.razorpay_payment_id,
                    razorpay_signature: paymentData.razorpay_signature,
                    student_id: studentId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Show success modal
                const successOverlay = $('#success-overlay');
                const successEmail = $('#success-email');
                if (successEmail) successEmail.textContent = $('#email').value;
                if (successOverlay) successOverlay.classList.add('show');

                // Reset form
                $('#enrollmentForm').reset();
            } else {
                showFormError(data.message || 'Payment verification failed. Contact academics@heyyguru.in');
            }
        } catch (error) {
            console.error('Payment verification error:', error);
            showFormError('Verification error. If amount was deducted, contact academics@heyyguru.in');
        }
    }

    // ---------------------------------------------------
    // Enrollment Ticker (Marquee)
    // ---------------------------------------------------
    function initTicker() {
        const track = $('#ticker-track');
        if (!track) return;

        let html = '';
        // Duplicate for seamless loop
        for (let i = 0; i < 2; i++) {
            TICKER_DATA.forEach(text => {
                html += `
                    <div class="ticker-item">
                        <span class="dot"></span>
                        <span class="name">${text.split(' ')[0]}</span>
                        <span>${text.substring(text.indexOf(' ') + 1)}</span>
                    </div>
                `;
            });
        }
        track.innerHTML = html;
    }

    // ---------------------------------------------------
    // Toast Notifications (Social Proof)
    // ---------------------------------------------------
    function initToastNotifications() {
        const toast = $('#enrollment-toast');
        if (!toast) return;

        let availableIndices = [];
        let lastIndex = -1;

        function getRandomIndex() {
            if (availableIndices.length === 0) {
                availableIndices = Array.from({length: FAKE_STUDENTS.length}, (_, i) => i);
                if (lastIndex !== -1) {
                    availableIndices = availableIndices.filter(i => i !== lastIndex);
                }
            }
            const r = Math.floor(Math.random() * availableIndices.length);
            const index = availableIndices[r];
            availableIndices.splice(r, 1);
            lastIndex = index;
            return index;
        }

        function showToast() {
            const index = getRandomIndex();
            const student = FAKE_STUDENTS[index];
            const timeAgo = Math.floor(Math.random() * 12) + 1; // 1 to 12 minutes
            const toastName = $('#toast-name');
            const toastTime = $('#toast-time');

            if (toastName) toastName.textContent = student.name;
            if (toastTime) toastTime.textContent = timeAgo === 1 ? '1 minute ago' : `${timeAgo} minutes ago`;

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        function scheduleNextToast() {
            setTimeout(() => {
                showToast();
                scheduleNextToast();
            }, Math.random() * 5000 + 5000); // Next toast in 5 to 10 seconds
        }

        // Show first toast after 3 seconds
        setTimeout(() => {
            showToast();
            scheduleNextToast();
        }, 3000);
    }

    // ---------------------------------------------------
    // Testimonial Auto Slider
    // ---------------------------------------------------
    function initTestimonialSlider() {
        const slider = $('#testimonials-slider');
        const dotsContainer = $('#slider-dots');
        if (!slider || !dotsContainer) return;

        const cards = Array.from(slider.querySelectorAll('.testimonial-card'));
        let currentSlide = 0;
        let cardsPerView = window.innerWidth <= 768 ? 1 : (window.innerWidth <= 992 ? 2 : 3);
        let maxSlides = Math.max(0, cards.length - cardsPerView);

        // Create dots
        dotsContainer.innerHTML = '';
        for (let i = 0; i <= maxSlides; i++) {
            const dot = document.createElement('div');
            dot.className = `slider-dot ${i === 0 ? 'active' : ''}`;
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }

        function goToSlide(index) {
            currentSlide = index;
            // Calculate exact move amount in pixels based on card width + gap
            const cardWidth = cards[0].offsetWidth;
            const gap = parseFloat(window.getComputedStyle(slider).gap) || 24;
            const moveAmount = -(currentSlide * (cardWidth + gap));
            slider.style.transform = `translateX(${moveAmount}px)`;
            
            const currentDots = dotsContainer.querySelectorAll('.slider-dot');
            currentDots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }

        function nextSlide() {
            if (currentSlide >= maxSlides) {
                goToSlide(0);
            } else {
                goToSlide(currentSlide + 1);
            }
        }

        // Auto slide
        let slideInterval = setInterval(nextSlide, 3500);

        // Pause on hover
        slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
        slider.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 3500);
        });

        // Handle resize
        window.addEventListener('resize', () => {
            const newCardsPerView = window.innerWidth <= 768 ? 1 : (window.innerWidth <= 992 ? 2 : 3);
            if (newCardsPerView !== cardsPerView) {
                cardsPerView = newCardsPerView;
                maxSlides = Math.max(0, cards.length - cardsPerView);
                
                // Recreate dots
                dotsContainer.innerHTML = '';
                for (let i = 0; i <= maxSlides; i++) {
                    const dot = document.createElement('div');
                    dot.className = `slider-dot ${i === 0 ? 'active' : ''}`;
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
                
                goToSlide(0);
            }
        });
    }

    // ---------------------------------------------------
    // Features Auto Slider
    // ---------------------------------------------------
    function initFeaturesSlider() {
        const slider = $('#features-slider');
        const dotsContainer = $('#features-dots');
        if (!slider || !dotsContainer) return;

        const cards = Array.from(slider.querySelectorAll('.feature-card'));
        let currentSlide = 0;
        let cardsPerView = window.innerWidth <= 768 ? 1 : (window.innerWidth <= 992 ? 2 : 3);
        let maxSlides = Math.max(0, cards.length - cardsPerView);

        // Create dots
        dotsContainer.innerHTML = '';
        for (let i = 0; i <= maxSlides; i++) {
            const dot = document.createElement('div');
            dot.className = `slider-dot ${i === 0 ? 'active' : ''}`;
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }

        function goToSlide(index) {
            currentSlide = index;
            const cardWidth = cards[0].offsetWidth;
            const gap = parseFloat(window.getComputedStyle(slider).gap) || 24;
            const moveAmount = -(currentSlide * (cardWidth + gap));
            slider.style.transform = `translateX(${moveAmount}px)`;
            
            const currentDots = dotsContainer.querySelectorAll('.slider-dot');
            currentDots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }

        function nextSlide() {
            if (currentSlide >= maxSlides) {
                goToSlide(0);
            } else {
                goToSlide(currentSlide + 1);
            }
        }

        let slideInterval = setInterval(nextSlide, 3500);

        slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
        slider.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 3500);
        });

        window.addEventListener('resize', () => {
            const newCardsPerView = window.innerWidth <= 768 ? 1 : (window.innerWidth <= 992 ? 2 : 3);
            if (newCardsPerView !== cardsPerView) {
                cardsPerView = newCardsPerView;
                maxSlides = Math.max(0, cards.length - cardsPerView);
                
                dotsContainer.innerHTML = '';
                for (let i = 0; i <= maxSlides; i++) {
                    const dot = document.createElement('div');
                    dot.className = `slider-dot ${i === 0 ? 'active' : ''}`;
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
                
                goToSlide(0);
            }
        });
    }

    // ---------------------------------------------------
    // UTM Parameter Capture
    // ---------------------------------------------------
    function captureUTM() {
        const params = new URLSearchParams(window.location.search);
        const fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];

        fields.forEach(field => {
            const value = params.get(field);
            const input = $(`#${field}`);
            if (value && input) {
                input.value = value;
            }
        });
    }

    // ---------------------------------------------------
    // Smooth Scroll for Anchor Links
    // ---------------------------------------------------
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href^="#"]');
        if (link) {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                const offset = 80; // Account for fixed navbar
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });

    // ---------------------------------------------------
    // Form Field Real-time Validation
    // ---------------------------------------------------
    document.addEventListener('input', (e) => {
        if (e.target.id === 'phone') {
            // Only allow digits
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        }
    });

    document.addEventListener('blur', (e) => {
        if (e.target.classList.contains('form-control') && e.target.classList.contains('error')) {
            // Re-validate on blur
            if (e.target.value.trim()) {
                e.target.classList.remove('error');
                const errorEl = e.target.closest('.form-group')?.querySelector('.form-error');
                if (errorEl) errorEl.classList.remove('show');
            }
        }
    }, true);

    // ---------------------------------------------------
    // Auto-Save Lead Data (Abandonment Tracking)
    // ---------------------------------------------------
    let trackingTimeout;
    const trackLead = () => {
        const form = $('#enrollmentForm');
        if (!form) return;
        
        const phone = $('#phone').value.trim();
        const email = $('#email').value.trim();
        
        // Only track if there's at least a phone or email
        if (phone.length < 10 && !email.includes('@')) return;

        clearTimeout(trackingTimeout);
        trackingTimeout = setTimeout(() => {
            const formData = new FormData(form);
            fetch('save_lead_partial.php', {
                method: 'POST',
                body: formData
            }).catch(e => console.error('Tracking error:', e));
        }, 1500); // Wait 1.5s after typing to send
    };

    const inputs = document.querySelectorAll('#enrollmentForm input, #enrollmentForm select');
    inputs.forEach(input => {
        input.addEventListener('blur', trackLead);
        input.addEventListener('change', trackLead);
    });

    // ---------------------------------------------------
    // Modal Logic
    // ---------------------------------------------------
    window.openEnrollModal = function() {
        const modal = document.getElementById('enroll-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeEnrollModal = function() {
        const modal = document.getElementById('enroll-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    const modalForm = document.getElementById('modalEnrollmentForm');
    if (modalForm) {
        const modalChips = document.querySelectorAll('.modal-class-chip');
        const modalHiddenInput = document.getElementById('modal_student_class');
        const modalPhoneInput = document.getElementById('modal-phone');
        
        modalChips.forEach(chip => {
            chip.addEventListener('click', () => {
                modalChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                if (modalHiddenInput) {
                    modalHiddenInput.value = chip.dataset.value;
                    const errorClass = document.getElementById('modal-error-class');
                    if (errorClass) errorClass.classList.remove('show');
                    
                    if (modalPhoneInput) {
                        modalPhoneInput.focus();
                    }
                }
            });
        });

        modalForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            let isValid = true;
            
            // Phone validation
            const phoneRegex = /^[6-9]\d{9}$/;
            const phoneVal = modalPhoneInput.value.trim();
            const phoneError = document.getElementById('modal-error-phone');
            if (!phoneVal || !phoneRegex.test(phoneVal)) {
                if (phoneError) phoneError.classList.add('show');
                modalPhoneInput.classList.add('error');
                isValid = false;
            } else {
                if (phoneError) phoneError.classList.remove('show');
                modalPhoneInput.classList.remove('error');
            }

            // Class validation
            const classVal = modalHiddenInput.value;
            const classError = document.getElementById('modal-error-class');
            if (!classVal) {
                if (classError) classError.classList.add('show');
                isValid = false;
            } else {
                if (classError) classError.classList.remove('show');
            }

            if (!isValid) return;

            const submitBtn = document.getElementById('modalSubmitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            try {
                // Submit lead data
                const formData = new FormData(modalForm);
                // Also pull UTM tags from main form if present
                const utmTags = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
                utmTags.forEach(tag => {
                    const el = document.getElementById(tag);
                    if (el && el.value) formData.append(tag, el.value);
                });

                const response = await fetch('submit_lead.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Something went wrong. Please try again.');
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                // Create Razorpay order
                const orderResponse = await fetch('create_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ student_id: data.student_id })
                });

                const orderData = await orderResponse.json();

                if (!orderData.success) {
                    alert(orderData.message || 'Failed to create payment order.');
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }

                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                // Close modal and open razorpay
                window.closeEnrollModal();
                openRazorpay(orderData, data.student_id, formData);

            } catch (error) {
                console.error('Modal Form submission error:', error);
                alert('Network error. Please check your connection and try again.');
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });
    }

})();

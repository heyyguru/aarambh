<?php
/**
 * =====================================================
 * AARAMBH by HeyyGuru — Main Landing Page
 * Domain: aarambh.heyyguru.in
 * =====================================================
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

// Track page visit
if (!RateLimiter::checkLimit('page_views', 60, 60)) {
    http_response_code(429);
    die("Too Many Requests. Please slow down.");
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO page_visits (ip_address, user_agent, referrer, page_url, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        getClientIP(),
        InputValidator::validateString($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
        InputValidator::validateUrl($_SERVER['HTTP_REFERER'] ?? '', 255),
        InputValidator::validateUrl($_SERVER['REQUEST_URI'] ?? '', 255) ?: '/',
        InputValidator::validateAlphaNumSpace($_GET['utm_source'] ?? '', 100) ?: null,
        InputValidator::validateAlphaNumSpace($_GET['utm_medium'] ?? '', 100) ?: null,
        InputValidator::validateAlphaNumSpace($_GET['utm_campaign'] ?? '', 100) ?: null
    ]);
} catch (Exception $e) {
    // Silently fail — don't break page for analytics
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5">

    <!-- Primary Meta Tags -->
    <title>Aarambh by HeyyGuru | Online Live Classes for Kids | Just ₹19</title>
    <meta name="title" content="Aarambh by HeyyGuru | Online Live Classes for Kids | Just ₹19">
    <meta name="description" content="Join Aarambh by HeyyGuru for live interactive online classes. Get expert mentorship, Vedic Maths, Public Speaking & school subjects for Class 1-10 at just ₹19. Start your learning journey today!">
    <meta name="keywords" content="HeyyGuru, Aarambh by HeyyGuru, live online classes for kids, best online tuition for class 1 to 10, Vedic Maths online classes, Public Speaking for kids, online learning platform India, affordable education, ₹19 course, smart learning">
    <meta name="author" content="HeyyGuru">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo SITE_URL; ?>/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>/">
    <meta property="og:title" content="Aarambh by HeyyGuru — Smart Learning @ ₹19">
    <meta property="og:description" content="Experience 6 days of live interactive classes with expert mentors. Maths, Science, English, Vedic Maths, Public Speaking — all for just ₹19!">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    <meta property="og:site_name" content="HeyyGuru">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo SITE_URL; ?>/">
    <meta property="twitter:title" content="Aarambh by HeyyGuru — Smart Learning @ ₹19">
    <meta property="twitter:description" content="Experience 6 days of live interactive classes with expert mentors. All for just ₹19!">

    <!-- Favicon - Universal compatibility -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon.png">
    <link rel="mask-icon" href="/assets/images/favicon.png" color="#4A90E2">
    <meta name="theme-color" content="#4A90E2">
    <link rel="shortcut icon" href="assets/images/favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    [{
        "@context": "https://schema.org",
        "@type": "Course",
        "name": "AARAMBH - Smart Learning Experience",
        "description": "6-day trial learning experience by HeyyGuru for Class 1-10 students. Includes Maths, Science, English, Vedic Maths, Public Speaking, and more.",
        "provider": {
            "@type": "Organization",
            "name": "HeyyGuru",
            "url": "https://heyyguru.in",
            "sameAs": ["https://heyyguru.in"]
        },
        "offers": {
            "@type": "Offer",
            "price": "19",
            "priceCurrency": "INR",
            "availability": "https://schema.org/InStock",
            "url": "<?php echo SITE_URL; ?>/"
        },
        "educationalLevel": "Class 1-10",
        "inLanguage": "en",
        "hasCourseInstance": {
            "@type": "CourseInstance",
            "courseMode": "online",
            "duration": "P6D"
        }
    },
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [{
            "@type": "Question",
            "name": "What is AARAMBH?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "AARAMBH is a 6-day trial learning experience by HeyyGuru. For just ₹19, your child gets access to live interactive classes, expert mentors, Vedic Maths, Public Speaking, and all core subjects."
            }
        }, {
            "@type": "Question",
            "name": "What's included in the ₹19 AARAMBH pack?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "6 days of live interactive classes in Maths, Science, English, Social Science, Vedic Maths, Public Speaking, and Learn India. Plus, daily practice problems (DPP), animation videos, weekly tests, 1:1 doubt sessions, and personal mentor guidance."
            }
        }, {
            "@type": "Question",
            "name": "Which classes is this available for?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "AARAMBH is available for students from Class 1 to Class 10, across all boards — CBSE, ICSE, and State Boards."
            }
        }]
    }]
    </script>
</head>
<body>

    <!-- ==================== NAVIGATION ==================== -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#" class="nav-logo" id="nav-logo">
                <img src="assets/images/logo.png" alt="HeyyGuru Logo" class="nav-logo-img">
                <span><span class="logo-text-hey">Heyy</span><span class="logo-text-guru">Guru</span></span>
            </a>
            <div class="nav-links" id="navLinks">
                <a href="#features">Features</a>
                <a href="#subjects">Subjects</a>
                <a href="#testimonials">Results</a>
                <a href="#faq">FAQ</a>
                <a href="#enroll" class="nav-cta">Enroll Now — ₹19</a>
            </div>
            <button class="nav-mobile-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero" id="hero">
        <!-- Floating Particles -->
        <div class="hero-particles">
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
            <div class="hero-particle"></div>
        </div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge" style="display:inline-flex;align-items:center;gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2l.5-.5a5.4 5.4 0 0 0 1-4.6v-.6A10.4 10.4 0 0 1 12 4.5h.6a5.4 5.4 0 0 0-4.6 1l-.5.5Z"></path><path d="m12 4.5 7.5-3s1 1 1 5l-3 7.5"></path><path d="m9 12 3 3"></path><path d="m15 9-6-6"></path></svg>
                    Launching Future Leaders
                </div>
                <h1 class="hero-title">
                    AARAMBH —<br>
                    <span class="highlight">Experience 6 Days</span><br>
                    of Smart Learning
                </h1>
                <p class="hero-subtitle" style="margin-bottom: 1.5rem;">
                    India's #1 Smart Learning Platform presents a powerful starter program. 
                    Experience Live Classes, Expert Mentors & more — before you commit.
                </p>
                
                <img src="assets/images/science_class_screen.png" alt="Live Online Classes" style="width: 100%; max-width: 500px; border-radius: 12px; margin-bottom: 1.5rem; object-fit: cover; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            </div>

            <div class="hero-visual" style="position: sticky; top: 100px; align-self: start;">
                <div class="timer-banner" style="background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%); color: white; text-align: center; padding: 10px; border-radius: 12px 12px 0 0; font-weight: 700; font-size: 1.1rem; display: flex; justify-content: center; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Offer Ends In: <span id="countdown-timer">10:00</span>
                </div>
                <div class="hero-visual-card" style="padding: 2rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 0 0 16px 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.4); backdrop-filter: blur(10px);">
                    
                    <h3 style="color:var(--text-white);margin-bottom:0.5rem;font-size:1.5rem; text-align:center; font-weight: 700;">
                        Concept Booster Course
                    </h3>
                    <div style="background: rgba(255, 223, 209, 0.2); padding: 8px; border-radius: 8px; text-align: center; color: #FFDFD1; font-size: 0.9rem; margin-bottom: 1.5rem;">
                        5X Efficient Learning Methods
                    </div>
                    
                    <div class="form-container" id="enrollment-form-container" style="background: transparent; box-shadow: none; padding: 0;">
                        <form id="enrollmentForm" novalidate>
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="color:var(--text-white); text-align: left; display: block; margin-bottom: 0.5rem;">Choose Class to Boost Score (2026-27) 🔥 <span class="required">*</span></label>
                                
                                <!-- Chips for Class Selection -->
                                <div class="class-chips-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem;">
                                    <button type="button" class="class-chip" data-value="1">1st</button>
                                    <button type="button" class="class-chip" data-value="2">2nd</button>
                                    <button type="button" class="class-chip" data-value="3">3rd</button>
                                    <button type="button" class="class-chip" data-value="4">4th</button>
                                    <button type="button" class="class-chip" data-value="5">5th</button>
                                    <button type="button" class="class-chip" data-value="6">6th</button>
                                    <button type="button" class="class-chip" data-value="7">7th</button>
                                    <button type="button" class="class-chip" data-value="8">8th</button>
                                    <button type="button" class="class-chip" data-value="9">9th</button>
                                    <button type="button" class="class-chip" data-value="10">10th</button>
                                </div>
                                <div class="form-error" id="error-class">Please select a class</div>
                            </div>

                            <div class="form-group" style="margin-top: 1.5rem;">
                                <label for="phone" style="color:var(--text-white); text-align: left; display: block; margin-bottom: 0.5rem;">Phone Number <span class="required">*</span></label>
                                <div class="phone-group">
                                    <span class="phone-prefix" style="background: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.2); height: 50px; display: flex; align-items: center; justify-content: center; width: 60px;">+91</span>
                                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter 10-digit mobile number" maxlength="10" required style="background: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.2); height: 50px; font-size: 1.1rem;">
                                </div>
                                <div class="form-error" id="error-phone">Please enter a valid 10-digit mobile number</div>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" id="student_class" name="student_class" value="">
                            <input type="hidden" id="student_name" name="student_name" value="">
                            <input type="hidden" id="email" name="email" value="">
                            <input type="hidden" id="city" name="city" value="">
                            <input type="hidden" id="utm_source" name="utm_source">
                            <input type="hidden" id="utm_medium" name="utm_medium">
                            <input type="hidden" id="utm_campaign" name="utm_campaign">
                            <input type="hidden" id="utm_content" name="utm_content">

                            <p style="color:var(--text-light); text-align:left; font-size:0.85rem; margin-top: 1rem;">
                                Course Material will be shared via WhatsApp on this Mobile Number
                            </p>

                            <button type="submit" class="btn btn-cta btn-lg form-submit-btn" id="submitBtn" style="width: 100%; margin-top: 1rem; height: 55px; font-size: 1.2rem;">
                                <span class="btn-text">
                                    Enroll Now for ₹19
                                </span>
                                <span class="spinner"></span>
                            </button>

                            <div class="form-trust" style="margin-top: 1.5rem; justify-content: center;">
                                <span class="lock-icon" style="display:inline-flex;align-items:center;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </span>
                                <span style="color:var(--text-light); font-size: 0.9rem;">100% Secure payment via Razorpay</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIVE COUNTER BAR ==================== -->
    <div class="live-counter-bar">
        <div class="container">
            <div class="live-counter-inner">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <div class="live-dot"></div>
                    <span class="live-counter-text">
                        <span class="count-number" id="live-counter">2,947</span> Students Enrolled
                    </span>
                </div>
                <div class="live-counter-avatars">
                    <div class="avatar">P</div>
                    <div class="avatar" style="background:linear-gradient(135deg,#FF6B35,#E55A2B);">M</div>
                    <div class="avatar" style="background:linear-gradient(135deg,#00C851,#00A844);">K</div>
                    <div class="avatar" style="background:linear-gradient(135deg,#6C3CE1,#4A6CF7);">D</div>
                    <div class="avatar more">99+</div>
                </div>
                <span style="color:var(--text-dark-2);font-size:0.9rem;display:flex;align-items:center;gap:4px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FF6B35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"></path></svg>
                    Seats filling fast!
                </span>
            </div>
        </div>
    </div>

    <!-- ==================== URGENCY TIMER ==================== -->
    <div class="urgency-bar" id="urgency-bar">
        <div class="container">
            <div class="urgency-content">
                <span class="urgency-text" style="display:inline-flex;align-items:center;gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Special Launch Offer Ends In:
                </span>
                <div class="urgency-timer" id="urgency-timer">
                    <div class="timer-block">
                        <span class="number" id="timer-hours">23</span>
                        <span class="label">Hours</span>
                    </div>
                    <div class="timer-block">
                        <span class="number" id="timer-minutes">59</span>
                        <span class="label">Mins</span>
                    </div>
                    <div class="timer-block">
                        <span class="number" id="timer-seconds">59</span>
                        <span class="label">Secs</span>
                    </div>
                </div>
                <a href="#enroll" class="btn btn-accent btn-sm">Grab Now →</a>
            </div>
        </div>
    </div>

    <!-- ==================== TWO TEACHER MODEL ==================== -->
    <section class="section section-white" id="two-teacher">
        <div class="container">
            <div class="two-teacher-grid">
                <div class="two-teacher-content reveal">
                    <span class="section-badge" style="display:inline-flex;align-items:center;gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Revolutionizing Learning
                    </span>
                    <h2 class="section-title">The Unique <strong>2-Teacher Model</strong></h2>
                    <p class="section-subtitle" style="margin-left:0; text-align:left;">Why settle for one teacher when you can have two? Our dual-teacher system ensures your child gets both expert instruction and personal attention.</p>
                    
                    <ul class="teacher-roles">
                        <li>
                            <div class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            </div>
                            <div>
                                <h4>Master Teacher</h4>
                                <p>Explains core concepts clearly on a smartboard using visual aids.</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <div>
                                <h4>Personal Mentor</h4>
                                <p>Sits with your child to solve doubts instantly and guide them step-by-step.</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                            </div>
                            <div>
                                <h4>Live Mic & Doubt Clearing</h4>
                                <p>Students can unmute their mic to speak directly and clear doubts live during class!</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="two-teacher-images reveal">
                    <img src="assets/images/master_teacher.png" alt="Master Teacher" class="img-master">
                    <img src="assets/images/mentor.png" alt="Personal Mentor guiding student" class="img-mentor">
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FEATURES SECTION ==================== -->
    <section class="section section-light" id="features">
        <div class="container text-center">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Why Aarambh?
            </span>
            <h2 class="section-title">Everything You Need to <strong>Excel</strong></h2>
            <p class="section-subtitle">A complete learning experience designed to give your child the best possible start.</p>

            <div class="features-slider-wrapper">
                <div class="features-slider" id="features-slider">
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                        </div>
                        <h3>Live Interactive Classes</h3>
                        <p>Engage with expert teachers in real-time. Ask questions, participate in activities, and learn interactively — not just recorded videos!</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <h3>Expert Mentors</h3>
                        <p>Every student gets personal mentorship. Two-teacher system — Subject Teacher for concepts, Mentor Teacher for progress & guidance.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <h3>Daily Practice (DPP)</h3>
                        <p>Daily Practice Problems to reinforce learning. Consistent practice builds strong foundations and exam confidence.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </div>
                        <h3>Vedic Maths</h3>
                        <p>Master lightning-fast calculation techniques. Vedic Maths makes your child a mental math champion — impress everyone!</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon pink">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                        </div>
                        <h3>Public Speaking</h3>
                        <p>Build confidence, communication skills, and stage presence. Your child learns to express ideas fearlessly — a skill for life!</p>
                    </div>
                    <!-- New Specific Feature Cards requested by user -->
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <h3>Weekly Tests</h3>
                        <p>Regular weekly assessments to ensure your child grasps every concept taught during the week and stays on track.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        </div>
                        <h3>Monthly Tests & Analysis</h3>
                        <p>Comprehensive monthly tests followed by in-depth performance analysis to identify strengths and areas of improvement.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <h3>Parent-Teacher Meetings (PTM)</h3>
                        <p>Regular PTMs to discuss progress, get feedback, and ensure parents are always aligned with their child's learning journey.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <h3>Parent Portal Access</h3>
                        <p>Exclusive access to the Parent Portal to track attendance, scores, and mentor feedback anywhere, anytime.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon pink">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                        </div>
                        <h3>Student Portal Access</h3>
                        <p>A dedicated dashboard for students to access study materials, recorded videos, assignments, and test scores easily.</p>
                    </div>
                </div>
                <div class="slider-dots" id="features-dots"></div>
            </div>
        </div>
    </section>

    <!-- ==================== SUBJECTS SECTION ==================== -->
    <section class="section section-dark" id="subjects">
        <div class="container text-center">
            <span class="section-badge" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:var(--primary-light);display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                Subjects Covered
            </span>
            <h2 class="section-title">All This in Just <span style="color:#FF3CAC; text-shadow: 0 4px 15px rgba(255, 60, 172, 0.4);">₹19</span></h2>
            <p class="section-subtitle">Complete access to all subjects and skill programs for 6 full days.</p>

            <div class="included-grid">
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Mathematics</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>English Grammar</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Science</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Social Science</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Vedic Maths</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Public Speaking</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Learn India</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Prompt Engineering (Live Skills)</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Animation Videos for Concepts</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>1:1 Doubt Resolution</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Mentor-Led Study Planning</span>
                </div>
                <div class="included-item reveal">
                    <span class="check">✓</span>
                    <span>Parent-Teacher Communication</span>
                </div>
            </div>
        </div>
    </section>


    <!-- ==================== ENROLLMENT TICKER ==================== -->
    <div class="enrollment-ticker">
        <div class="ticker-track" id="ticker-track">
            <!-- Filled by JS -->
        </div>
    </div>

    <!-- ==================== TESTIMONIALS ==================== -->
    <section class="section section-light" id="testimonials">
        <div class="container text-center">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Student Results
            </span>
            <h2 class="section-title">What Parents & Students Say</h2>
            <p class="section-subtitle">Real results from real students. Join thousands of happy learners!</p>
            
            <div class="testimonials-slider-wrapper">
                <div class="testimonials-slider" id="testimonials-slider">
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"My daughter's confidence in Maths has improved dramatically after joining HeyyGuru. The Vedic Maths sessions are incredible — she now solves problems faster than her classmates!"</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#FF6B35,#FF3CAC);">S</div>
                            <div class="info">
                                <span class="name">Sunita Sharma</span>
                                <span class="class">Parent — Daughter in Class 6</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"I was shy and scared of speaking in front of people. HeyyGuru's Public Speaking classes changed everything. I won my school speech competition last month!"</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#00C851,#00A844);">A</div>
                            <div class="info">
                                <span class="name">Anvita</span>
                                <span class="class">Class 5 — Gold Medalist</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"₹19 for 6 days of live classes? I thought it was a scam! But it's 100% real. My son scored 92% after joining HeyyGuru. Best decision ever. The mentors are so supportive."</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#4A90E2,#73B1EE);">R</div>
                            <div class="info">
                                <span class="name">Rajesh Kumar</span>
                                <span class="class">Parent — Son in Class 8</span>
                            </div>
                        </div>
                    </div>
                    <!-- New Reviews -->
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"The 1:1 mentorship is a game changer. The teacher sat with my child for an extra 20 minutes to clear a science doubt. You don't see this dedication anywhere else."</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#6C3CE1,#4A6CF7);">P</div>
                            <div class="info">
                                <span class="name">Priya Desai</span>
                                <span class="class">Parent — Son in Class 9</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"I never liked Maths, it was so boring. But the animated videos and live classes make it feel like a game. I actually look forward to my classes now!"</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#FFB800,#FF6B35);">K</div>
                            <div class="info">
                                <span class="name">Karan</span>
                                <span class="class">Class 7 Student</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"The AARAMBH program is the best 19 rupees I've ever spent. It gave us a clear picture of how they teach before committing. We enrolled full-time immediately!"</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#00C851,#0B0E1A);">M</div>
                            <div class="info">
                                <span class="name">Manish Verma</span>
                                <span class="class">Parent — Daughter in Class 4</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"My son used to struggle with Science concepts. After 3 days of HeyyGuru live classes, he's explaining biology to me! The interactive visual approach makes a huge difference."</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#FF3CAC,#FF6B35);">A</div>
                            <div class="info">
                                <span class="name">Aisha Patel</span>
                                <span class="class">Parent — Son in Class 7</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="stars">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <p class="quote">"I love how I can ask questions directly to my mentor. The classes are fun and I'm not afraid of math tests anymore!"</p>
                        <div class="testimonial-author">
                            <div class="avatar" style="background:linear-gradient(135deg,#00A844,#00C851);">R</div>
                            <div class="info">
                                <span class="name">Rohan</span>
                                <span class="class">Class 5 Student</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-dots" id="slider-dots"></div>
            </div>
        </div>
    </section>

    <!-- ==================== PRICING SECTION ==================== -->
    <section class="section pricing-section" id="pricing">
        <div class="container text-center">
            <span class="section-badge" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:var(--accent-light);display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Unbelievable Price
            </span>
            <h2 class="section-title" style="color:var(--text-white);">Start Your Journey for Less Than a <em>Samosa</em></h2>
            <p class="section-subtitle" style="color:var(--text-light);">6 days of complete learning experience. Live classes, mentorship, study material — everything included.</p>

            <div class="pricing-card">
                <div class="popular-badge" style="display:inline-flex;align-items:center;gap:4px;justify-content:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"></path></svg>
                    MOST POPULAR
                </div>
                <h3 style="color:var(--text-white);font-size:1.5rem;margin-top:0.5rem;">AARAMBH Pack</h3>
                <div class="pricing-amount">
                    <span class="original">₹999</span>
                    <div class="current" style="color: #FF3CAC; text-shadow: 0 4px 15px rgba(255, 60, 172, 0.4);"><span class="rupee">₹</span>19</div>
                    <span class="duration">for 6 days of full access</span>
                </div>
                <ul class="pricing-features">
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Live Interactive Classes (All Subjects)</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Expert Subject Teachers</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Personal Mentor Teacher</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Vedic Maths + Public Speaking</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Daily Practice Problems (DPP)</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Weekly Test + Progress Report</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> Animation Videos for Concepts</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> 1:1 Doubt Resolution</li>
                    <li><span class="check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00C851" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span> 100% Risk-Free Guarantee</li>
                </ul>
                <a href="#enroll" class="btn btn-cta btn-lg" style="width:100%;">
                    Start AARAMBH Journey — ₹19 →
                </a>
                <div class="form-trust" style="margin-top:1rem;">
                    <span class="lock-icon" style="display:inline-flex;align-items:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </span>
                    <span style="color:var(--text-muted);">Secure payment via Razorpay • UPI, Cards, Net Banking</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ENROLLMENT FORM ==================== -->
    <section class="section section-white form-section" id="enroll">
        <div class="container text-center">
            <span class="section-badge" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:var(--primary-light);display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Enroll Now
            </span>
            <h2 class="section-title">Begin Your <span style="color:var(--primary);">AARAMBH</span> Journey</h2>
            <p class="section-subtitle">Fill in your details below. You'll receive class details on email & WhatsApp after payment.</p>

            <div class="form-container" id="enrollment-form-container">
                <form id="enrollmentForm" novalidate>
                    <div class="form-group">
                        <label for="student_name">Student Name <span class="required">*</span></label>
                        <input type="text" id="student_name" name="student_name" class="form-control" placeholder="Enter student's full name" required>
                        <div class="form-error" id="error-name">Please enter student's name</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                        <div class="form-error" id="error-email">Please enter a valid email address</div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <div class="phone-group">
                            <span class="phone-prefix">+91</span>
                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter 10-digit mobile number" maxlength="10" required>
                        </div>
                        <div class="form-error" id="error-phone">Please enter a valid 10-digit mobile number</div>
                    </div>

                    <div class="form-group">
                        <label for="student_class">Class <span class="required">*</span></label>
                        <select id="student_class" name="student_class" class="form-control" required>
                            <option value="">Select Class</option>
                            <option value="1">Class 1</option>
                            <option value="2">Class 2</option>
                            <option value="3">Class 3</option>
                            <option value="4">Class 4</option>
                            <option value="5">Class 5</option>
                            <option value="6">Class 6</option>
                            <option value="7">Class 7</option>
                            <option value="8">Class 8</option>
                            <option value="9">Class 9</option>
                            <option value="10">Class 10</option>
                        </select>
                        <div class="form-error" id="error-class">Please select a class</div>
                    </div>

                    <div class="form-group">
                        <label for="city">City <span style="color:var(--text-muted);">(Optional)</span></label>
                        <input type="text" id="city" name="city" class="form-control" placeholder="Enter your city">
                    </div>

                    <!-- Hidden UTM fields -->
                    <input type="hidden" id="utm_source" name="utm_source">
                    <input type="hidden" id="utm_medium" name="utm_medium">
                    <input type="hidden" id="utm_campaign" name="utm_campaign">
                    <input type="hidden" id="utm_content" name="utm_content">

                    <button type="submit" class="btn btn-cta btn-lg form-submit-btn" id="submitBtn">
                        <span class="btn-text">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; margin-right:5px;"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                            Pay ₹19 & Start Learning
                        </span>
                        <span class="spinner"></span>
                    </button>

                    <div class="form-trust">
                        <span class="lock-icon" style="display:inline-flex;align-items:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </span>
                        <span>100% Secure • Non-Refundable • Instant Access</span>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- ==================== FAQ SECTION ==================== -->
    <section class="section section-light" id="faq">
        <div class="container text-center">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Common Questions
            </span>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Got questions? We've got answers!</p>

            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question" id="faq-q1">
                        What is AARAMBH?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>AARAMBH is a 6-day trial learning experience by HeyyGuru — India's #1 Smart Learning Platform. For just ₹19, your child gets access to live interactive classes, expert mentors, Vedic Maths, Public Speaking, and all core subjects. It's designed to give you a complete experience before you commit to a full enrollment.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" id="faq-q2">
                        What's included in the ₹19 AARAMBH pack?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>You get 6 days of live interactive classes in Maths, Science, English, Social Science, Vedic Maths, Public Speaking, and Learn India. Plus, daily practice problems (DPP), animation videos, weekly tests, 1:1 doubt sessions, and personal mentor guidance — all included!</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" id="faq-q3">
                        Which classes is this available for?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>AARAMBH is available for students from Class 1 to Class 10, across all boards — CBSE, ICSE, and State Boards.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" id="faq-q4">
                        Is the ₹19 fee refundable?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>The ₹19 fee is non-refundable as it covers the cost of live classes, mentorship, and study materials. However, we guarantee you'll love the experience — that's our 100% satisfaction promise!</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" id="faq-q5">
                        How do I attend the classes?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>After payment, you'll receive your class schedule and joining link via email and WhatsApp. A mentor will also call you to guide you through the process. All classes are conducted live online — you just need a phone or laptop with internet.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" id="faq-q6">
                        What happens after 6 days?
                        <span class="icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>After the 6-day AARAMBH experience, you can choose to continue with HeyyGuru's full programs. There's absolutely no pressure — the decision is yours! Our team will share the available plans with you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FINAL CTA ==================== -->
    <section class="section section-dark text-center" style="padding:4rem 0;">
        <div class="container">
            <h2 style="color:var(--text-white);margin-bottom:0.5rem;">Don't Wait. Start Today.</h2>
            <p style="color:var(--text-light);font-size:1.1rem;margin-bottom:1.5rem;">
                Na loan ka stress, na padhai ka tension — HeyyGuru mein student ka progress.
            </p>
            <a href="#enroll" class="btn btn-cta btn-lg" style="display:inline-flex;align-items:center;gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                Start AARAMBH Journey — ₹19
            </a>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="assets/images/logo.png" alt="HeyyGuru Logo" class="footer-logo-img">
                        <span><span class="logo-text-hey">Heyy</span><span class="logo-text-guru">Guru</span></span>
                    </div>
                    <p>India's #1 Smart Learning Platform for Class 1 to 10. Vedic Maths, Public Speaking, Live Classes & more — crafted for students who dream big.</p>
                    <div class="footer-contact-item">
                        <span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></span>
                        <a href="mailto:academics@heyyguru.in" style="color:var(--text-light);">academics@heyyguru.in</a>
                    </div>
                    <div class="footer-contact-item">
                        <span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></span>
                        <a href="tel:+917676798650" style="color:var(--text-light);">+91 7676798650</a>
                    </div>
                </div>
                <div>
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#subjects">Subjects</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-heading">Programs</h4>
                    <ul class="footer-links">
                        <li><a href="https://heyyguru.in" target="_blank">HeyyGuru Main Site</a></li>
                        <li><a href="#subjects">Vedic Maths</a></li>
                        <li><a href="#subjects">Public Speaking</a></li>
                        <li><a href="#subjects">Learn India</a></li>
                        <li><a href="#enroll">Enroll Now</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> HeyyGuru. All rights reserved.</p>
                <div class="payment-icons">
                    <span>UPI</span>
                    <span>VISA</span>
                    <span>Mastercard</span>
                    <span>RuPay</span>
                    <span>Net Banking</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ==================== STICKY MOBILE CTA ==================== -->
    <div class="sticky-cta" id="sticky-cta">
        <div class="sticky-cta-inner">
            <div class="sticky-cta-price">
                <span class="original">₹999</span>
                <span class="current">₹19</span>
            </div>
            <a href="#enroll" class="btn btn-cta btn-sm">Enroll Now →</a>
        </div>
    </div>

    <!-- ==================== TOAST NOTIFICATION ==================== -->
    <div class="toast" id="enrollment-toast">
        <div class="toast-icon">✓</div>
        <div>
            <div class="toast-text"><strong id="toast-name">Rahul from Delhi</strong> just enrolled!</div>
            <div class="toast-time" id="toast-time">2 minutes ago</div>
        </div>
    </div>

    <!-- ==================== SUCCESS MODAL ==================== -->
    <div class="success-overlay" id="success-overlay">
        <div class="success-card">
            <div class="success-icon" style="color:#00C851;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <h2>Welcome to AARAMBH!</h2>
            <p>Payment successful! Check your email for class schedule and joining details. Our mentor will call you within 24 hours.</p>
            <div style="background:var(--bg-light);border-radius:var(--radius-md);padding:1rem;margin-bottom:1.5rem;">
                <p style="font-size:0.9rem;color:var(--text-dark-2);margin-bottom:0.5rem;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    Confirmation sent to:
                </p>
                <p style="font-weight:600;color:var(--primary);" id="success-email">student@email.com</p>
            </div>
            <a href="https://heyyguru.in" class="btn btn-primary" target="_blank">Visit HeyyGuru →</a>
        </div>
    </div>

    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        window.AARAMBH_CONFIG = {
            razorpayKeyId: '<?php echo addslashes(RAZORPAY_KEY_ID); ?>'
        };
    </script>
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>

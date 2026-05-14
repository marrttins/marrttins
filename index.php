<?php
require_once 'Mat/config.php';

$form_success = '';

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
        $form_success = "Thank you! Your message has been sent successfully.";
        
        // Send Email Notification
        $emailSubject = "New Contact Message: " . $subject;
        $emailBody = "
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Phone:</strong> {$phone}</p>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Message:</strong><br>{$message}</p>
        ";
        sendAdminEmail($emailSubject, $emailBody);
    }
}

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_submit'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $service = $_POST['service_type'] ?? '';
    $timeline = $_POST['timeline'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $details = $_POST['details'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO bookings (name, email, service_type, timeline, budget, details) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $service, $timeline, $budget, $details])) {
        $form_success = "Project booking submitted! I will contact you shortly.";
        
        // Send Email Notification
        $emailSubject = "New Project Booking: " . $service;
        $emailBody = "
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Service:</strong> {$service}</p>
            <p><strong>Timeline:</strong> {$timeline}</p>
            <p><strong>Budget:</strong> {$budget}</p>
            <p><strong>Details:</strong><br>{$details}</p>
        ";
        sendAdminEmail($emailSubject, $emailBody);
    }
}

// Fetch Projects
$projects_stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$db_projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate category counts
$category_counts = [];
foreach ($db_projects as $p) {
    $cat = $p['category'];
    $category_counts[$cat] = ($category_counts[$cat] ?? 0) + 1;
}

// Fetch Partnerships
$partners_stmt = $pdo->query("SELECT * FROM partnerships ORDER BY created_at DESC");
$db_partners = $partners_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Reviews
$reviews_stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$db_reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Booking Options
$booking_categories = $pdo->query("SELECT * FROM service_categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$all_services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
$db_addons = $pdo->query("SELECT * FROM addons")->fetchAll(PDO::FETCH_ASSOC);
$db_timelines = $pdo->query("SELECT * FROM timelines")->fetchAll(PDO::FETCH_ASSOC);

// Organize services by category for JS
$js_services_data = [];
foreach ($booking_categories as $cat) {
    $js_services_data[$cat['slug']] = [
        'title' => $cat['name'],
        'options' => []
    ];
}
foreach ($all_services as $s) {
    foreach ($booking_categories as $cat) {
        if ($s['category_id'] == $cat['id']) {
            $js_services_data[$cat['slug']]['options'][] = [
                'name' => $s['name'],
                'min' => (int)$s['min_price'],
                'max' => (int)$s['max_price']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Ajayi Opeyemi Martins (Marrthings) | Expert Full-Stack Developer & SEO Specialist in Nigeria</title>
    <meta name="description" content="Hire Marrthings - Ajayi Opeyemi Martins, the best Web Developer, SEO Specialist, and Digital Marketer in Nigeria. 8+ years of experience in high-performance websites, AI automation, and Google ranking.">
    <meta name="keywords" content="Marrthings, Ajayi Opeyemi Martins, Best Web Developer in Nigeria, SEO Specialist Nigeria, Full Stack Developer, Digital Marketing Expert, AI Automation Expert Nigeria, Hire Web Developer">
    <meta name="author" content="Ajayi Opeyemi Martins">
    <link rel="canonical" href="https://hire.marrthings.com.ng/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hire.marrthings.com.ng/">
    <meta property="og:title" content="Ajayi Opeyemi Martins (Marrthings) | Digital Excellence Expert">
    <meta property="og:description" content="Transforming ideas into high-performance digital solutions. Web Development, SEO, and AI Automation that drives results.">
    <meta property="og:image" content="https://hire.marrthings.com.ng/assets/images/og-image.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://hire.marrthings.com.ng/">
    <meta property="twitter:title" content="Ajayi Opeyemi Martins (Marrthings) | Digital Excellence Expert">
    <meta property="twitter:description" content="Transforming ideas into high-performance digital solutions. Web Development, SEO, and AI Automation.">
    <meta property="twitter:image" content="https://hire.marrthings.com.ng/assets/images/og-image.png">

    <!-- Schema.org / Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Ajayi Opeyemi Martins",
      "alternateName": "Marrthings",
      "url": "https://hire.marrthings.com.ng",
      "image": "https://hire.marrthings.com.ng/profile.png",
      "sameAs": [
        "https://facebook.com/ajayiope",
        "https://instagram.com/ajayiope",
        "https://linkedin.com/in/ajayiope",
        "https://x.com/ajayiope"
      ],
      "jobTitle": "Full Stack Developer & SEO Specialist",
      "worksFor": {
        "@type": "Organization",
        "name": "MAlvNET"
      },
      "description": "Expert Techprenuer, Web Developer, SEO Specialist, and Digital Marketer based in Nigeria with over 8 years of experience.",
      "knowsAbout": ["Web Development", "SEO", "Digital Marketing", "AI Automation", "Full Stack Development"]
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Service",
      "serviceType": "Web Development & SEO",
      "provider": {
        "@type": "Person",
        "name": "Ajayi Opeyemi Martins"
      },
      "areaServed": "Global",
      "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Digital Services",
        "itemListElement": [
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Custom Web Development"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Search Engine Optimization (SEO)"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "AI Automation Solutions"
            }
          }
        ]
      }
    }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --blue: #1a56db;
            --blue-dark: #0f3ba0;
            --blue-light: rgba(26, 86, 219, 0.1);
            --orange: #f97316;
            --orange-dark: #c2560f;
            --orange-light: rgba(249, 115, 22, 0.1);
            --white: #ffffff;
            --dark: #0d1117;
            --dark-2: #161c26;
            --gray: #6b7280;
            --gray-light: #f3f4f6;
            --gray-border: #e5e7eb;
            --text: #111827;
            --text-muted: #6b7280;
            --radius: 16px;
            --radius-sm: 8px;
            --shadow: 0 4px 24px rgba(26, 86, 219, 0.08);
            --shadow-lg: 0 12px 48px rgba(26, 86, 219, 0.12);
            --font-display: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
            --glass: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
        }

        [data-theme="dark"] {
            --white: #0d1117;
            --dark: #ffffff;
            --dark-2: #161c26;
            --gray-light: #161c26;
            --gray-border: #30363d;
            --text: #c9d1d9;
            --text-muted: #8b949e;
            --glass: rgba(13, 17, 23, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
            --emerald-light: rgba(5, 150, 105, 0.15);
        }

        :root {
            --emerald-light: #ecfdf5;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            font-size: 16px;
            color: var(--text);
            background: var(--white);
            line-height: 1.7;
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
            overflow: hidden; /* Hide scroll during loading */
        }

        /* ── PRELOADER ────────────────────────────────── */
        #preloader {
            position: fixed;
            inset: 0;
            background: var(--white);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s, visibility 0.5s;
        }

        #preloader.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .loader-content {
            text-align: center;
        }

        .loader-logo {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--blue);
            margin-bottom: 1.5rem;
            animation: pulse-logo 2s infinite;
        }

        .loader-logo span { color: var(--orange); }

        .loader-bar {
            width: 200px;
            height: 4px;
            background: var(--gray-border);
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto;
        }

        .loader-bar .progress {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--blue), var(--orange));
            animation: load 2s forwards;
        }

        @keyframes load {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        @keyframes pulse-logo {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        /* ── GRID BACKGROUND ─────────────────────────── */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(var(--gray-border) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.2;
            z-index: -1;
            pointer-events: none;
        }

        /* ── NAV ─────────────────────────────────────────── */
        nav {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            z-index: 1000;
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            border-radius: 50px;
            transition: all .3s;
            box-shadow: var(--shadow);
        }

        nav.scrolled {
            width: 100%;
            top: 0;
            left: 0;
            transform: none;
            border-radius: 0;
            background: var(--white);
            border-bottom: 1px solid var(--gray-border);
        }

        .nav-logo {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--blue);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-logo span {
            color: var(--orange);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: color .2s;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--blue);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .theme-toggle {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .nav-cta {
            background: var(--blue);
            color: #fff !important;
            padding: .5rem 1.2rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }

        .nav-cta:hover {
            background: var(--orange);
            transform: translateY(-1px);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 5px;
            background: none;
            border: none;
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--dark);
            border-radius: 2px;
            transition: all 0.3s;
        }

        .hamburger.open span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        .mobile-nav {
            display: none;
            position: fixed;
            top: 84px;
            /* below pill nav (20px top + 64px height) */
            left: 5%;
            right: 5%;
            background: var(--white);
            border: 1px solid var(--gray-border);
            border-radius: 20px;
            padding: 1.5rem;
            z-index: 999;
            box-shadow: var(--shadow-lg);
            flex-direction: column;
            gap: 0;
            transition: top 0.3s, left 0.3s, right 0.3s, border-radius 0.3s;
        }

        /* When the navbar is in scrolled (full-width) mode */
        .mobile-nav.scrolled {
            top: 64px;
            /* flush below scrolled nav */
            left: 0;
            right: 0;
            border-radius: 0 0 20px 20px;
        }

        .mobile-nav.open {
            display: flex;
            animation: slideDown 0.3s ease-out;
        }

        .mobile-nav .mob-link {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-border);
            font-size: 0.9rem;
            color: var(--text);
            font-weight: 600;
        }

        .mobile-nav .mob-cta {
            margin: 1rem;
            padding: 0.8rem;
            background: var(--blue);
            color: white !important;
            border-radius: 12px;
            text-align: center;
            font-weight: 700;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-nav .mob-link:last-of-type {
            border-bottom: none;
        }

        .mobile-nav .mob-link:hover {
            color: var(--blue);
            background: var(--gray-light);
        }

        /* ── HERO ─────────────────────────────────────────── */
        #hero {
            min-height: 100vh;
            padding: 140px 5% 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .hero-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/hero-bg.png') center/cover no-repeat;
            opacity: 0.1;
            z-index: -1;
        }

        .hero-inner {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .hero-profile {
            position: relative;
            width: 160px;
            height: 160px;
            margin-bottom: 2rem;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .hero-profile img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--white);
            box-shadow: 0 0 40px rgba(26, 86, 219, 0.2);
        }

        .hero-profile .flag {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            border: 2px solid var(--white);
            box-shadow: var(--shadow);
            opacity: 0;
            transform: scale(0.5);
            animation: cycleFlags 9s infinite;
            z-index: 5;
        }

        .hero-profile .flag:nth-child(2) {
            animation-delay: 0s;
        }

        .hero-profile .flag:nth-child(3) {
            animation-delay: 3s;
        }

        .hero-profile .flag:nth-child(4) {
            animation-delay: 6s;
        }

        @keyframes cycleFlags {
            0% {
                opacity: 0;
                transform: scale(0.5) rotate(-15deg);
            }

            5%,
            30% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
                z-index: 10;
            }

            35%,
            100% {
                opacity: 0;
                transform: scale(0.5) rotate(15deg);
                z-index: 1;
            }
        }

        .hero-badges-marquee {
            width: 100%;
            overflow: hidden;
            margin-bottom: 3rem;
            position: relative;
            mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
        }

        .hero-badges-inner {
            display: flex;
            gap: 2.5rem;
            width: max-content;
            animation: scrollLeft 30s linear infinite;
        }

        .badge-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--blue-light);
            color: var(--blue);
            font-size: 0.75rem;
            font-weight: 700;
            padding: .4rem 1rem;
            border-radius: 50px;
            margin-bottom: 2rem;
            border: 1px solid rgba(26, 86, 219, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 10px #22c55e;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.2);
                opacity: 1;
            }

            100% {
                transform: scale(0.95);
                opacity: 0.7;
            }
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 6vw, 4.2rem);
            font-weight: 800;
            line-height: 1;
            color: var(--dark);
            letter-spacing: -2px;
            margin-bottom: 1.5rem;
        }

        .hero-title .accent {
            color: var(--blue);
        }

        .hero-title .typewriter {
            color: var(--orange);
        }

        .hero-desc {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 540px;
            margin-bottom: 2.5rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--blue);
            color: #fff;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all .3s;
            box-shadow: 0 10px 20px rgba(26, 86, 219, 0.2);
        }

        .btn-primary:hover {
            background: var(--orange);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(249, 115, 22, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            border: 2px solid var(--gray-border);
            transition: all .3s;
        }

        .btn-outline:hover {
            border-color: var(--blue);
            color: var(--blue);
        }

        .hero-visual {
            position: relative;
        }

        .hero-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 2;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            -top: 20px;
            -right: 20px;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--blue), var(--orange));
            border-radius: 24px;
            z-index: -1;
            opacity: 0.1;
        }

        /* ── SECTIONS ───────────────────────────────────── */
        section {
            padding: 100px 5%;
            position: relative;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            margin-bottom: 4rem;
            text-align: center;
        }

        .section-label {
            color: var(--orange);
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1rem;
            display: block;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -1px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 900px;
            margin: 4rem auto 0;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem 1rem;
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: var(--blue);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            font-family: var(--font-display);
            font-size: 2rem;
            color: var(--blue);
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .consulting-marquee {
            width: 100vw;
            margin-left: calc(-50vw + 50%);
            background: rgba(26, 86, 219, 0.03);
            padding: 2rem 0;
            margin-top: 4rem;
            overflow: hidden;
            position: relative;
            border-top: 1px solid var(--gray-border);
            border-bottom: 1px solid var(--gray-border);
        }

        .marquee-inner {
            display: flex;
            gap: 2rem;
            animation: scroll 40s linear infinite;
            width: max-content;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .pill {
            background: var(--white);
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--gray-border);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── TOOLS SECTION ────────────────────────────── */
        .tools-grid-container {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            margin: 4rem 0;
            align-items: center;
        }

        .tools-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .tool-card {
            background: var(--white);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--gray-border);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .tool-card:hover {
            border-color: var(--blue);
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .tool-card .t-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .tool-card span {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
        }

        .filter-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 3rem;
        }

        .filter-chip {
            background: rgba(26, 86, 219, 0.05);
            color: var(--text-muted);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }

        .filter-chip:hover {
            background: var(--blue);
            color: #fff;
        }

        /* ── BRANDS MARQUEE ────────────────────────────── */
        .brands-marquee-container {
            width: 100vw;
            margin-left: calc(-50vw + 50%);
            padding: 4rem 0;
            overflow: hidden;
            position: relative;
        }

        .brands-inner {
            display: flex;
            gap: 2rem;
            animation: scroll 30s linear infinite;
            width: max-content;
        }

        .brand-card {
            width: 180px;
            height: 90px;
            background: var(--white);
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-border);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            transition: all 0.3s;
        }

        .brand-card:hover {
            border-color: var(--blue);
            transform: scale(1.05);
            box-shadow: var(--shadow);
        }

        .brand-card span {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.5px;
        }

        /* ── PORTFOLIO ── */
        .portfolio-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .filter-btn {
            background: var(--white);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            border: 1px solid var(--gray-border);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background: #059669;
            color: #fff;
            border-color: #059669;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .project-card {
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .project-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--blue);
        }

        .p-img {
            position: relative;
            aspect-ratio: 16/10;
            background: var(--gray-light);
            overflow: hidden;
        }

        .p-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s;
        }

        .project-card:hover .p-img img {
            transform: scale(1.05);
        }

        .p-tag {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #059669;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .p-preview {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .p-content {
            padding: 1.5rem;
        }

        .p-content h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .p-content p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .p-skills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .p-skill {
            background: var(--gray-light);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .p-link {
            color: #059669;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ── MODAL ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(13, 17, 23, 0.9);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(8px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalFade .3s ease-out;
        }

        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gray-border);
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
        }

        .modal-close {
            background: var(--gray-light);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: background .2s;
        }

        .modal-close:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-hero-img {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-border);
        }

        .modal-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        @media (max-width: 768px) {
            .modal-info-grid {
                grid-template-columns: 1fr;
            }
        }

        .tool-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .tool-pill-sm {
            background: rgba(26, 86, 219, 0.05);
            border: 1px solid rgba(26, 86, 219, 0.1);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--blue);
        }

        /* ── IMPACT & TRUST ── */
        .impact-container {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 60px;
            align-items: center;
            background: var(--white);
            padding: 4rem;
            border-radius: 30px;
            border: 1px solid var(--gray-border);
            margin-top: 4rem;
        }

        @media (max-width: 992px) {
            .impact-container {
                grid-template-columns: 1fr;
                padding: 2rem;
            }
        }

        .map-viz {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: var(--gray-light) url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg') center/contain no-repeat;
            border-radius: 20px;
            overflow: hidden;
            opacity: 0.8;
        }

        .map-marker {
            position: absolute;
            width: 60px;
            height: 60px;
        }

        .marker-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--white);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .marker-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .marker-pulse {
            position: absolute;
            inset: -10px;
            background: rgba(5, 150, 105, 0.2);
            border-radius: 50%;
            z-index: 1;
            animation: markerPulse 2s infinite;
        }

        @keyframes markerPulse {
            0% {
                transform: scale(0.5);
                opacity: 1;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .impact-stats-row {
            display: flex;
            gap: 20px;
            margin-top: 2rem;
        }

        .impact-card-sm {
            background: var(--gray-light);
            padding: 1.5rem;
            border-radius: 16px;
            flex: 1;
            text-align: center;
            border: 1px solid var(--gray-border);
        }

        .impact-card-sm h4 {
            font-size: 1.5rem;
            color: #059669;
            font-family: var(--font-display);
        }

        .impact-card-sm p {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
        }

        /* ── TESTIMONIAL FUN FACT ── */
        .fun-fact-box {
            max-width: 850px;
            margin: 5rem auto 0;
            padding: 2.5rem;
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--gray-border);
            text-align: center;
            transition: transform 0.3s;
        }

        @media (max-width: 600px) {
            .fun-fact-box {
                padding: 1.5rem;
                margin-top: 3rem;
            }
        }

        .fun-fact-box:hover {
            transform: translateY(-5px);
            border-color: #059669;
        }

        .fun-fact-text {
            font-size: 1rem;
            color: var(--dark);
            font-weight: 600;
            line-height: 1.5;
        }

        .fun-fact-stars {
            color: #f59e0b;
            font-size: 1.4rem;
            margin: 1.2rem 0;
            letter-spacing: 2px;
        }

        .verify-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            border: 1px solid #059669;
            color: #059669;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .verify-btn:hover {
            background: #059669;
            color: #fff;
        }

        /* ── ABOUT ME REDESIGN ── */
        .about-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .about-wrapper {
                grid-template-columns: 1fr;
            }
        }

        .profile-img-box {
            width: 150px;
            height: 150px;
            border-radius: 24px;
            overflow: hidden;
            border: 4px solid #059669;
            margin-bottom: 2rem;
        }

        .profile-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            margin-bottom: 24px;
        }

        .skills-cat {
            margin-bottom: 1.5rem;
        }

        .skills-cat h5 {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .skills-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .skill-pill {
            background: var(--emerald-light);
            color: #059669;
            border: 1px solid rgba(5, 150, 105, 0.2);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            border-left: 2px solid var(--emerald-light);
            margin-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 0;
            width: 12px;
            height: 12px;
            background: #059669;
            border-radius: 50%;
            border: 3px solid var(--white);
            box-shadow: 0 0 0 3px var(--emerald-light);
        }

        .timeline-date {
            font-size: 0.75rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 4px;
            display: block;
        }

        .timeline-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .timeline-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .about-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 2rem 0;
        }

        @media (max-width: 480px) {
            .about-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .a-stat-card {
            background: var(--white);
            padding: 1.2rem;
            border-radius: 16px;
            border: 1px solid var(--gray-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .a-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--emerald-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .a-stat-info h4 {
            font-size: 1.1rem;
            color: var(--dark);
        }

        .a-stat-info p {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 700;
        }

        /* ── CONTACT REDESIGN ── */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 1100px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        .contact-form-box {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid var(--gray-border);
        }

        @media (max-width: 600px) {
            .contact-form-box {
                padding: 1.5rem;
            }
        }

        .contact-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .contact-group {
            margin-bottom: 1.5rem;
        }

        .contact-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--gray-border);
            background: var(--white);
            color: var(--dark);
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .contact-input:focus {
            border-color: #059669;
            outline: none;
        }

        .c-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            margin-bottom: 20px;
        }

        .c-info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .c-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--emerald-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #059669;
        }

        .c-info-text h5 {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .c-info-text p {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--dark);
        }

        .c-why-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            text-align: center;
        }

        .c-why-item h4 {
            font-size: 1.2rem;
            color: #059669;
            margin-bottom: 4px;
        }

        .c-why-item p {
            font-size: 0.65rem;
            color: var(--text-muted);
            font-weight: 700;
        }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            border: 1px solid var(--gray-border);
            margin-bottom: 1rem;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #059669;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(5, 150, 105, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0);
            }
        }

        /* ── FOOTER REDESIGN ── */
        .footer-section {
            background: #0f172a;
            color: #fff;
            padding: 5rem 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 4rem;
        }

        @media (max-width: 992px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer-col h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 30px;
            height: 2px;
            background: #059669;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: #059669;
        }

        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            color: #64748b;
            font-size: 0.85rem;
        }

        .footer-legal a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            margin-left: 20px;
        }

        .footer-legal a:hover {
            color: #fff;
        }

        /* ── TOOLS MARQUEE ── */
        .tools-marquee-container {
            overflow: hidden;
            padding: 2rem 0;
            position: relative;
        }

        .tools-row {
            display: flex;
            gap: 20px;
            width: max-content;
        }

        .scroll-left {
            animation: scrollLeft 30s linear infinite;
        }

        .scroll-right {
            animation: scrollRight 30s linear infinite;
        }

        @keyframes scrollLeft {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        @keyframes scrollRight {
            0% {
                transform: translateX(-50%);
            }

            100% {
                transform: translateX(0);
            }
        }

        .tool-card {
            background: var(--white);
            padding: 1rem 2rem;
            border-radius: 12px;
            border: 1px solid var(--gray-border);
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            transition: transform 0.3s;
        }

        .tool-card:hover {
            transform: scale(1.05);
            border-color: #059669;
        }

        .t-icon {
            font-size: 1.2rem;
        }

        /* ── MAP FLIGHT EFFECT ── */
        .plane-effect {
            position: absolute;
            font-size: 1.8rem;
            z-index: 20;
            pointer-events: none;
            animation: globalFlight 15s linear infinite;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes globalFlight {
            0% {
                top: 30%;
                left: 15%;
                transform: rotate(30deg);
            }

            /* USA */
            30% {
                top: 25%;
                left: 45%;
                transform: rotate(10deg);
            }

            /* UK */
            60% {
                top: 60%;
                left: 48%;
                transform: rotate(160deg);
            }

            /* NG */
            90% {
                top: 30%;
                left: 15%;
                transform: rotate(-140deg);
            }

            /* Back to USA */
            100% {
                top: 30%;
                left: 15%;
                transform: rotate(-140deg);
            }
        }

        .flight-path {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* ── BOOKING FLOW ── */
        .booking-section {
            background: #f8fafc;
            padding: 6rem 0;
        }

        .booking-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 30px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--gray-border);
        }

        .booking-header {
            padding: 3rem;
            text-align: center;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        .booking-steps {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 2rem;
            background: #fff;
            position: relative;
        }

        .booking-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 30%;
            right: 30%;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
            transform: translateY(-50%);
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            border: 2px solid var(--gray-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--text-muted);
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }

        .step-circle.active {
            background: #059669;
            border-color: #059669;
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(5, 150, 105, 0.3);
        }

        .step-circle.completed {
            background: var(--emerald-light);
            border-color: #059669;
            color: #059669;
        }

        .booking-body {
            padding: 3rem;
            min-height: 500px;
        }

        @media (max-width: 768px) {
            .booking-body {
                padding: 1.5rem;
            }
        }

        .booking-step-content {
            display: none;
            animation: slideUp 0.4s ease-out;
        }

        .booking-step-content.active {
            display: block;
        }

        .service-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        @media (max-width: 480px) {
            .service-type-grid {
                grid-template-columns: 1fr;
            }
        }

        .type-card {
            padding: 2rem;
            border: 1px solid var(--gray-border);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
        }

        .type-card:hover {
            border-color: #059669;
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .type-card.selected {
            border-color: #059669;
            background: var(--emerald-light);
            box-shadow: 0 0 0 2px #059669;
        }

        .type-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }

        .type-card h4 {
            font-size: 0.95rem;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .type-card p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .sub-service-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 600px) {
            .sub-service-grid {
                grid-template-columns: 1fr;
            }
        }

        .sub-card {
            padding: 1.5rem;
            border: 1px solid var(--gray-border);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
            background: var(--white);
        }

        .sub-card:hover {
            border-color: #059669;
            background: var(--gray-light);
        }

        .sub-card.selected {
            border-color: #059669;
            background: var(--emerald-light);
        }

        .sub-info h4 {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .sub-info p {
            font-size: 0.7rem;
            color: #059669;
            font-weight: 700;
        }

        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 1rem;
        }

        @media (max-width: 600px) {
            .timeline-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .timeline-card {
            padding: 1rem;
            border: 1px solid var(--gray-border);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
            color: var(--dark);
        }

        .timeline-card.selected {
            background: #059669;
            color: #fff;
            border-color: #059669;
        }

        .timeline-card h4 {
            font-size: 0.8rem;
            margin-bottom: 2px;
        }

        .timeline-card p {
            font-size: 0.65rem;
            opacity: 0.8;
        }

        .budget-summary {
            background: var(--gray-light);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--gray-border);
            text-align: center;
            margin-top: 2rem;
            color: var(--dark);
        }

        .budget-amount {
            font-size: 2.2rem;
            font-family: var(--font-display);
            color: #059669;
            margin: 10px 0;
        }

        .booking-footer {
            padding: 2rem 3rem;
            background: var(--gray-light);
            display: flex;
            justify-content: space-between;
            border-top: 1px solid var(--gray-border);
        }

        @media (max-width: 600px) {
            .booking-footer {
                padding: 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* ── SERVICES GRID ─────────────────────────────── */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        /* ── TESTIMONIAL CAROUSEL ── */
        .testimonial-carousel {
            position: relative;
            overflow: hidden;
            margin: 0 -15px;
        }

        .testimonial-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .testimonial-slide {
            flex: 0 0 50%;
            padding: 15px;
            box-sizing: border-box;
        }

        @media (max-width: 992px) {
            .testimonial-slide {
                flex: 0 0 100%;
            }
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 2rem;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--gray-border);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            background: var(--blue);
            transform: scale(1.2);
        }

        .service-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-border);
            transition: all .4s;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-color: var(--blue);
            box-shadow: var(--shadow-lg);
        }

        .service-card .icon {
            width: 60px;
            height: 60px;
            background: var(--blue-light);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: var(--blue);
            transition: all .3s;
        }

        .service-card:hover .icon {
            background: var(--blue);
            color: #fff;
        }

        /* ── PORTFOLIO ───────────────────────────────────── */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 30px;
        }

        .portfolio-item {
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            aspect-ratio: 16/10;
            background: var(--gray-light);
            border: 1px solid var(--gray-border);
        }

        .portfolio-overlay {
            position: absolute;
            inset: 0;
            background: rgba(13, 17, 23, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .4s;
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        .portfolio-item:hover .portfolio-overlay {
            opacity: 1;
        }

        /* ── ABOUT ───────────────────────────────────────── */
        .about-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .about-img {
            width: 100%;
            aspect-ratio: 1;
            background: var(--gray-light);
            border-radius: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--gray-border);
        }

        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── WHATSAPP ────────────────────────────────────── */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #25d366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
            z-index: 1000;
            transition: all .3s;
            text-decoration: none;
        }

        .whatsapp-float:hover {
            transform: scale(1.1) rotate(10deg);
        }

        /* ── ANIMATIONS ──────────────────────────────────── */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── FOOTER ──────────────────────────────────────── */
        footer {
            padding: 60px 5%;
            background: var(--dark-2);
            color: #fff;
            text-align: center;
        }

        .footer-logo {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            display: block;
        }

        .footer-logo span {
            color: var(--orange);
        }

        /* ══════════════════════════════════════════
           COMPREHENSIVE MOBILE RESPONSIVENESS
           ══════════════════════════════════════════ */

        /* ── Tablet: 992px ── */
        @media (max-width: 992px) {
            .mobile-hide {
                display: none;
            }

            section {
                padding: 60px 5%;
            }

            .nav-links {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .nav-cta {
                display: none;
            }

            .hero-inner {
                text-align: center;
            }

            .hero-desc {
                margin: 0 auto 2.5rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .portfolio-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .services-grid {
                grid-template-columns: 1fr 1fr;
            }

            .about-wrapper {
                grid-template-columns: 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Mobile: 768px ── */
        @media (max-width: 768px) {
            nav {
                padding: 0 1.2rem;
            }

            nav.scrolled {
                left: 0;
                width: 100%;
                transform: none;
            }

            .mobile-nav {
                top: 74px;
            }

            #hero {
                padding: 100px 15px 60px;
                min-height: auto;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .hero-inner {
                width: 100%;
                padding: 0;
            }

            .hero-title {
                font-size: clamp(1.6rem, 7vw, 2.5rem);
                line-height: 1.2;
                letter-spacing: -0.5px;
                margin-bottom: 1.2rem;
                width: 100%;
            }

            .hero-badge {
                font-size: 0.6rem;
                padding: 0.3rem 0.6rem;
                margin-bottom: 1.2rem;
                white-space: normal;
                line-height: 1.4;
            }

            .hero-profile {
                width: 110px;
                height: 110px;
                margin-bottom: 1.2rem;
            }

            .section-title {
                font-size: clamp(1.8rem, 6vw, 2.8rem);
            }

            .hero-badges-marquee {
                margin-bottom: 2rem;
            }

            .hero-badges-inner {
                gap: 1.5rem;
                animation-duration: 20s;
            }

            .badge-item {
                font-size: 0.75rem;
            }

            .consulting-marquee {
                padding: 1.2rem 0;
                margin-top: 2.5rem;
            }

            .marquee-inner {
                gap: 1rem;
                animation-duration: 30s;
            }

            .pill {
                padding: 0.6rem 1.2rem;
                font-size: 0.75rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin-top: 2.5rem;
            }

            .stat-card {
                padding: 1.2rem 0.8rem;
                border-radius: 16px;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .stat-card p {
                font-size: 0.75rem;
            }

            .booking-container {
                border-radius: 16px;
            }

            .booking-steps {
                gap: 8px;
                padding: 1.2rem 0.8rem;
                overflow-x: auto;
            }

            .step-circle {
                width: 34px;
                height: 34px;
                font-size: 0.75rem;
            }

            .step-label {
                display: none;
            }

            #booking-form {
                grid-template-columns: 1fr !important;
            }

            #booking-form>div {
                grid-column: span 1 !important;
            }

            /* Contact form inline grids → single column */
            .contact-form-box form div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }

            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .impact-container {
                padding: 1.5rem;
                gap: 30px;
            }

            .section-title[style*="font-size: 3rem"] {
                font-size: clamp(1.8rem, 6vw, 2.5rem) !important;
            }

            .testimonial-carousel {
                margin: 0 -5px;
            }

            .testimonial-slide {
                padding: 5px;
            }
        }

        /* ── Small Mobile: 480px ── */
        @media (max-width: 480px) {
            nav {
                width: 95%;
                padding: 0 1rem;
            }

            .mobile-nav {
                left: 2.5%;
                right: 2.5%;
            }

            #hero {
                padding: 80px 10px 40px;
            }

            .hero-title {
                font-size: 1.5rem;
                line-height: 1.3;
            }

            .hero-profile {
                width: 90px;
                height: 90px;
            }

            .hero-desc {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
                padding: 0 10px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
                width: 100%;
                gap: 0.8rem;
            }

            .hero-buttons a {
                width: 100%;
                max-width: 280px;
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
                margin-top: 2rem;
            }

            .stat-card {
                padding: 0.8rem 0.4rem;
            }

            .stat-card h3 {
                font-size: 1.1rem;
            }

            .stat-card p {
                font-size: 0.65rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .booking-body {
                padding: 1rem;
            }

            .service-type-grid {
                grid-template-columns: 1fr !important;
            }

            .timeline-grid {
                grid-template-columns: 1fr 1fr;
            }

            .booking-steps {
                padding: 0.8rem;
            }

            .booking-footer {
                flex-direction: column;
                gap: 0.8rem;
            }

            .booking-footer button,
            .booking-footer a {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .contact-form-box {
                padding: 1.2rem;
            }

            .c-card {
                padding: 1.2rem;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 0.8rem;
            }

            .footer-legal {
                margin-top: 0;
            }

            .footer-legal a {
                margin: 0 8px;
                font-size: 0.8rem;
            }

            section {
                padding: 50px 4%;
            }

            .section-header {
                margin-bottom: 2.5rem;
            }

            .modal-overlay {
                padding: 10px;
            }

            .modal-container {
                border-radius: 16px;
            }

            .modal-header {
                padding: 1rem 1.2rem;
                flex-direction: column;
                gap: 0.8rem;
                align-items: flex-start;
            }

            .modal-info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .service-card {
                padding: 1.5rem;
            }

            .c-why-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader-content">
            <div class="loader-logo">Marr<span>things</span></div>
            <div class="loader-bar"><div class="progress"></div></div>
        </div>
    </div>

    <div class="grid-bg"></div>

    <?php if ($form_success): ?>
        <div
            style="position: fixed; top: 100px; right: 20px; z-index: 2000; background: #059669; color: white; padding: 1rem 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out;">
            <strong>Success!</strong> <?php echo $form_success; ?>
            <button onclick="this.parentElement.remove()"
                style="margin-left: 15px; background: none; border: none; color: white; cursor: pointer; font-weight: 800;">✕</button>
        </div>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                }

                to {
                    transform: translateX(0);
                }
            }
        </style>
    <?php endif; ?>

    <nav id="navbar">
        <a href="#" class="nav-logo">Marr<span>things</span></a>
        <ul class="nav-links">
            <li><a href="#services">Services</a></li>
            <li><a href="#portfolio">Portfolio</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#testimonials">Reviews</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="nav-actions">
            <button class="theme-toggle" id="theme-btn" title="Toggle Dark Mode">🌙</button>
            <a href="#contact" class="nav-cta">Let's Talk</a>
            <button class="hamburger" id="hamburger-btn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
    <!-- Mobile Nav Dropdown -->
    <div class="mobile-nav" id="mobile-nav">
        <a href="#services" class="mob-link">Services</a>
        <a href="#portfolio" class="mob-link">Portfolio</a>
        <a href="#about" class="mob-link">About</a>
        <a href="#testimonials" class="mob-link">Reviews</a>
        <a href="#contact" class="mob-link">Contact</a>
        <a href="#contact" class="mob-cta">Let's Talk 💬</a>
    </div>

    <section id="hero">
        <div class="hero-bg-image"></div>
        <div class="hero-inner">
            <div class="hero-badge reveal">
                <span class="dot"></span> Currently Available for Projects Today - <span
                    id="availability-date"><?php echo date('F j, Y'); ?></span>
            </div>

            <h1 class="hero-title reveal">
                Transforming Ideas Into <span class="accent">Digital Excellence</span><br class="mobile-hide">
                <span style="font-size: 0.6em; color: var(--text-muted); font-weight: 500;">with</span> <span
                    class="typewriter" id="typewriter"></span>
            </h1>

            <div class="hero-profile reveal">
                <img src="profile.png" alt="Martins">
                <div class="flag">🇳🇬</div>
                <div class="flag">🇺🇸</div>
                <div class="flag">🇬🇧</div>
            </div>

            <p class="hero-desc reveal">
                I'm <strong>Ajayi Opeyemi Martins</strong> A.K.A <strong>Marrthings</strong>, a dedicated full-stack
                developer and SEO specialist. I help businesses grow through strategic web development, SEO
                optimization, and data-driven digital solutions.
            </p>

            <div class="hero-badges-marquee reveal">
                <div class="hero-badges-inner">
                    <div class="badge-item">😎 Best Website Developer in Nigeria</div>
                    <div class="badge-item">🎯 Best SEO Specialist in Nigeria</div>
                    <div class="badge-item">🚀 Best Digital Marketing Expert In Nigeria</div>
                    <div class="badge-item">💡 Creative Problem Solver</div>
                    <div class="badge-item">💡 Best Digital Marketing in Nigeria</div>
                    <div class="badge-item">💡 Best AI Automation expert in Nigeria</div>
                    <!-- Duplicate for infinite loop -->
                    <div class="badge-item">😎 Best Website Developer in Nigeria</div>
                    <div class="badge-item">🎯 Best SEO Specialist in Nigeria</div>
                    <div class="badge-item">🚀 Best Digital Marketing Expert In Nigeria</div>
                    <div class="badge-item">💡 Creative Problem Solver</div>
                    <div class="badge-item">💡 Best Digital Marketing in Nigeria</div>
                    <div class="badge-item">💡 Best AI Automation expert in Nigeria</div>
                </div>
            </div>

            <div class="hero-buttons reveal">
                <a href="#booking" class="btn-primary">Let's Work Together ➔</a>
                <a href="#portfolio" class="btn-outline">View My Work</a>
            </div>

            <div class="stats-grid reveal">
                <div class="stat-card">
                    <h3>100+</h3>
                    <p>Projects Completed</p>
                </div>
                <div class="stat-card">
                    <h3>50+</h3>
                    <p>Happy Clients</p>
                </div>
                <div class="stat-card">
                    <h3>8+</h3>
                    <p>Years Experience</p>
                </div>
                <div class="stat-card">
                    <h3>99%</h3>
                    <p>Client Satisfaction</p>
                </div>
            </div>

            <div class="consulting-marquee reveal">
                <div class="marquee-inner">
                    <div class="pill">Performance Monitoring</div>
                    <div class="pill">Analytics & Tracking Setup</div>
                    <div class="pill">Digital Strategy Consulting</div>
                    <div class="pill">Growth Consulting</div>
                    <div class="pill">Startup Technical Advisory</div>
                    <div class="pill">Product Strategy & Planning</div>
                    <!-- Duplicate for infinite effect -->
                    <div class="pill">Performance Monitoring</div>
                    <div class="pill">Analytics & Tracking Setup</div>
                    <div class="pill">Digital Strategy Consulting</div>
                    <div class="pill">Growth Consulting</div>
                    <div class="pill">Startup Technical Advisory</div>
                    <div class="pill">Product Strategy & Planning</div>
                </div>
            </div>
        </div>
    </section>

    <section id="services">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Solutions</span>
                <h2 class="section-title">Services that <span style="color: var(--blue)">Drive Growth</span></h2>
                <p
                    style="margin-top: 1.5rem; color: var(--text-muted); max-width: 700px; margin-left: auto; margin-right: auto; font-size: 0.95rem; line-height: 1.6;">
                    Complete service catalogue with comprehensive digital solutions tailored to your business goals.
                    Click any category to explore services, tools, and pricing.
                </p>
            </div>
            <div class="services-grid">
                <div class="service-card reveal">
                    <div class="icon">🌐</div>
                    <h3>Web & Product Development</h3>
                    <p>Custom websites and web applications built for performance and conversion</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">📈</div>
                    <h3>SEO & Growth</h3>
                    <p>Data-driven SEO strategies that improve rankings and drive organic traffic</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">📢</div>
                    <h3>Digital Marketing & Advertising</h3>
                    <p>Strategic campaigns that maximize ROI and drive qualified leads</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">💳</div>
                    <h3>E-commerce & Payments</h3>
                    <p>Complete e-commerce solutions with secure payment integration</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">🤖</div>
                    <h3>Automation & Integrations</h3>
                    <p>Streamline operations with smart automation and seamless integrations</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">🏗️</div>
                    <h3>Business & Consulting</h3>
                    <p>Strategic guidance for digital transformation and growth</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">🎨</div>
                    <h3>Branding & Experience</h3>
                    <p>Create memorable brand identities that resonate with your audience</p>
                </div>
                <div class="service-card reveal">
                    <div class="icon">🧩</div>
                    <h3>Specialized Solutions</h3>
                    <p>Custom platforms for fintech, logistics, education, and more</p>
                </div>
            </div>

            <div class="stats-grid reveal" style="margin-top: 5rem;">
                <div class="stat-card">
                    <h3>50+</h3>
                    <p>Services Offered</p>
                </div>
                <div class="stat-card">
                    <h3>30+</h3>
                    <p>Tools & Platforms</p>
                </div>
                <div class="stat-card">
                    <h3>8+</h3>
                    <p>Service Categories</p>
                </div>
                <div class="stat-card">
                    <h3>100%</h3>
                    <p>Custom Solutions</p>
                </div>
            </div>
        </div>
    </section>

    <section id="tools" style="background: var(--gray-light);">
        <div class="container">
            <div class="section-header reveal">
                <div
                    style="background: var(--white); display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid var(--gray-border); margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--blue);">
                    Tools & Technologies</div>
                <h2 class="section-title">Trusted Tools & <span style="color: var(--blue)">Technologies</span> Used For
                    Projects</h2>
                <p style="margin-top: 1rem; color: var(--text-muted);">I work with industry-leading tools to deliver
                    exceptional results for your projects.</p>
            </div>

            <div class="tools-marquee-container reveal">
                <!-- Row 1: Scroll Left -->
                <div class="tools-row scroll-left" style="margin-bottom: 20px;">
                    <div class="tool-card">
                        <div class="t-icon">🛒</div><span>WooCommerce</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🧱</div><span>Elementor</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🕸️</div><span>Webflow</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🛍️</div><span>Shopify</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">📸</div><span>Instagram</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">⚛️</div><span>React</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">▲</div><span>Next.js</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🐘</div><span>PHP</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🎵</div><span>TikTok</span>
                    </div>
                    <!-- Duplicate for infinite -->
                    <div class="tool-card">
                        <div class="t-icon">🛒</div><span>WooCommerce</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🧱</div><span>Elementor</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🕸️</div><span>Webflow</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🛍️</div><span>Shopify</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">📸</div><span>Instagram</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">⚛️</div><span>React</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">▲</div><span>Next.js</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🐘</div><span>PHP</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🎵</div><span>TikTok</span>
                    </div>
                </div>

                <!-- Row 2: Scroll Right -->
                <div class="tools-row scroll-right">
                    <div class="tool-card">
                        <div class="t-icon">💳</div><span>Paystack</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">💳</div><span>Stripe</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">📧</div><span>Klaviyo</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🤝</div><span>HubSpot</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🐒</div><span>Mailchimp</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">💼</div><span>LinkedIn</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🎥</div><span>YouTube</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🔍</div><span>Google Ads</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">👤</div><span>Meta Ads</span>
                    </div>
                    <!-- Duplicate for infinite -->
                    <div class="tool-card">
                        <div class="t-icon">💳</div><span>Paystack</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">💳</div><span>Stripe</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">📧</div><span>Klaviyo</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🤝</div><span>HubSpot</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🐒</div><span>Mailchimp</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">💼</div><span>LinkedIn</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🎥</div><span>YouTube</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">🔍</div><span>Google Ads</span>
                    </div>
                    <div class="tool-card">
                        <div class="t-icon">👤</div><span>Meta Ads</span>
                    </div>
                </div>
            </div>

            <div class="filter-chips reveal">
                <div class="filter-chip">WordPress & CMS</div>
                <div class="filter-chip">Development</div>
                <div class="filter-chip">SEO & Analytics</div>
                <div class="filter-chip">Digital Marketing</div>
                <div class="filter-chip">Payment Gateways</div>
                <div class="filter-chip">Automation</div>
                <div class="filter-chip">Content & Social Media</div>
            </div>
        </div>
    </section>

    <section id="partnerships">
        <div class="container">
            <div class="section-header reveal">
                <div
                    style="background: var(--blue-light); display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid rgba(26,86,219,0.1); margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--blue);">
                    Trusted Partnerships</div>
                <h2 class="section-title">Brands That <span style="color: var(--blue)">Trust Us</span></h2>
                <p
                    style="margin-top: 1rem; color: var(--text-muted); max-width: 800px; margin-left: auto; margin-right: auto;">
                    Proud to have partnered with amazing businesses across various industries, from startups to
                    established enterprises, delivering exceptional digital solutions.
                </p>
            </div>

            <div class="brands-marquee-container reveal">
                <div class="brands-inner">
                    <?php if (empty($db_partners)): ?>
                        <!-- Fallback to demo brands if none in DB -->
                        <div class="brand-card"><span>I-KOOK</span></div>
                        <div class="brand-card"><span>KISARA</span></div>
                        <div class="brand-card"><span>LABELSON</span></div>
                        <div class="brand-card"><span>BAVE</span></div>
                        <div class="brand-card"><span>HUKO</span></div>
                        <div class="brand-card"><span>BALLERIE</span></div>
                        <div class="brand-card"><span>TECHFLOW</span></div>
                        <div class="brand-card"><span>ECOSTORE</span></div>
                    <?php else: ?>
                        <?php
                        // Duplicate twice for infinite loop effect
                        for ($i = 0; $i < 2; $i++):
                            foreach ($db_partners as $partner): ?>
                                <div class="brand-card">
                                    <?php if ($partner['logo_url']): ?>
                                        <img src="<?php echo $partner['logo_url']; ?>"
                                            alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                            style="max-height: 40px; transition: 0.3s;">
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($partner['name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="portfolio">
        <div class="container">
            <div class="section-header reveal">
                <div
                    style="background: #ecfdf5; display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid #d1fae5; margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: #059669;">
                    Portfolio</div>
                <h2 class="section-title">Featured <span style="color: #059669">Projects</span></h2>
                <p
                    style="margin-top: 1rem; color: var(--text-muted); max-width: 600px; margin-left: auto; margin-right: auto;">
                    Real projects I've delivered for clients across multiple industries. Click any project to preview it
                    live.
                </p>
            </div>

            <div class="portfolio-filters reveal">
                <button class="filter-btn active" data-filter="all">All (<?php echo count($db_projects); ?>)</button>
                <?php 
                foreach ($categories as $cat) {
                    if (isset($category_counts[$cat])) {
                        echo '<button class="filter-btn" data-filter="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . ' (' . $category_counts[$cat] . ')</button>';
                    }
                }
                ?>
            </div>

            <div class="portfolio-grid" id="portfolioGrid">
                <?php if (empty($db_projects)): ?>
                    <!-- Fallback if no projects in DB yet -->
                    <div class="project-card reveal project-item" data-category="Banking & Fintech" onclick="openModal('cre8hive')">
                        <div class="p-img">
                            <img src="assets/images/p1.png" alt="Cre8hive">
                            <span class="p-tag" style="background: #eef2ff; color: var(--blue);">Banking & Fintech</span>
                            <div class="p-preview">👁️ Preview</div>
                        </div>
                        <div class="p-content">
                            <h3>The Cre8hive App</h3>
                            <p>Fintech platform for creative professionals and business management.</p>
                            <div class="p-skills"><span class="p-skill">Fintech</span></div>
                            <span class="p-link">Click to View Details ➔</span>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($db_projects as $index => $p): ?>
                        <div class="project-card reveal project-item" 
                             data-category="<?php echo htmlspecialchars($p['category']); ?>"
                             onclick="openModal('<?php echo $p['id']; ?>')"
                             style="<?php echo $index >= 6 ? 'display: none;' : ''; ?>">
                            <div class="p-img">
                                <img src="<?php echo !empty($p['image_url']) ? $p['image_url'] : 'assets/images/p1.png'; ?>"
                                    alt="<?php echo htmlspecialchars($p['title']); ?>">
                                <span class="p-tag"><?php echo htmlspecialchars($p['category']); ?></span>
                                <div class="p-preview">👁️ Preview</div>
                            </div>
                            <div class="p-content">
                                <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                                <p><?php echo substr(htmlspecialchars($p['description'] ?? ''), 0, 80) . '...'; ?></p>
                                <div class="p-skills">
                                    <?php
                                    $tools = explode(',', $p['tools'] ?? '');
                                    foreach ($tools as $t) {
                                        if (trim($t)) echo '<span class="p-skill">' . htmlspecialchars(trim($t)) . '</span> ';
                                    }
                                    ?>
                                </div>
                                <span class="p-link">Click to View Details ➔</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="reveal" id="loadMoreSection" style="text-align: center; margin-top: 4rem; <?php echo count($db_projects) <= 6 ? 'display: none;' : ''; ?>">
                <p id="projectCountText" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; font-weight: 600;">
                    Showing <span id="visibleCount"><?php echo min(6, count($db_projects)); ?></span> of <?php echo count($db_projects); ?> projects
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <button id="loadMoreBtn" class="btn-primary"
                        style="background: #059669; border-color: #059669; padding: 0.8rem 1.8rem; cursor: pointer; border: none; font-family: inherit;">Load More Projects ➔</button>
                    <a href="#booking" class="btn-outline"
                        style="padding: 0.8rem 1.8rem; border-color: #059669; color: #059669; text-decoration: none;">Discuss Your Project ➔</a>
                </div>
            </div>
        </div>
    </section>

    <section id="trust-proof">
        <div class="container">
            <div class="section-header reveal">
                <div
                    style="background: var(--white); display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid var(--gray-border); margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: var(--blue);">
                    Trust & Proof</div>
                <h2 class="section-title">Trusted by <span style="color: #059669">Businesses Worldwide</span></h2>
                <p style="margin-top: 1rem; color: var(--text-muted);">Join 50+ satisfied clients who have transformed
                    their digital presence with Marrthings.</p>
            </div>

            <div class="stats-grid reveal">
                <div class="stat-card">
                    <div
                        style="background: #ecfdf5; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #059669;">
                        🎖️</div>
                    <h3 style="color: #059669;">100+</h3>
                    <p>Projects Completed</p>
                </div>
                <div class="stat-card">
                    <div
                        style="background: #ecfdf5; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #059669;">
                        🏢</div>
                    <h3 style="color: #059669;">15+</h3>
                    <p>Industries Served</p>
                </div>
                <div class="stat-card">
                    <div
                        style="background: #ecfdf5; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #059669;">
                        ⭐</div>
                    <h3 style="color: #059669;">4.9/5</h3>
                    <p>Average Rating</p>
                </div>
                <div class="stat-card">
                    <div
                        style="background: #ecfdf5; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #059669;">
                        🕒</div>
                    <h3 style="color: #059669;">8+</h3>
                    <p>Years Experience</p>
                </div>
            </div>

            <div class="impact-container reveal">
                <div>
                    <h3
                        style="font-family: var(--font-display); font-size: 1.8rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px;">
                        🌐 Global Impact</h3>
                    <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem;">
                        From Nigeria 🇳🇬 to the world, I've worked with clients across multiple continents, delivering
                        solutions that drive real business results regardless of location.
                    </p>
                    <div class="impact-stats-row">
                        <div class="impact-card-sm">
                            <h4>10+</h4>
                            <p>Countries</p>
                        </div>
                        <div class="impact-card-sm">
                            <h4>3</h4>
                            <p>Continents</p>
                        </div>
                    </div>
                </div>
                <div class="map-viz">
                    <!-- Flight Effect -->
                    <div class="plane-effect">✈️</div>

                    <svg class="flight-path" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M15,30 Q30,20 45,25 Q48,40 48,60 Q30,45 15,30" fill="none"
                            stroke="var(--emerald-light)" stroke-width="0.5" stroke-dasharray="2 2" opacity="0.5" />
                    </svg>

                    <!-- USA Marker -->
                    <div class="map-marker" style="top: 30%; left: 15%;">
                        <div class="marker-circle"><img src="profile.png" alt="USA"></div>
                        <div class="marker-pulse"></div>
                        <span
                            style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 0.6rem; font-weight: 800; color: var(--dark); white-space: nowrap;">🇺🇸
                            USA</span>
                    </div>
                    <!-- UK Marker -->
                    <div class="map-marker" style="top: 25%; left: 45%;">
                        <div class="marker-circle" style="border-color: #ef4444;"><img src="profile.png" alt="UK"></div>
                        <div class="marker-pulse" style="background: rgba(239, 68, 68, 0.2);"></div>
                        <span
                            style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 0.6rem; font-weight: 800; color: var(--dark); white-space: nowrap;">🇬🇧
                            UK</span>
                    </div>
                    <!-- Nigeria Marker -->
                    <div class="map-marker" style="top: 60%; left: 48%;">
                        <div class="marker-circle" style="border-color: #059669;"><img src="profile.png" alt="NG"></div>
                        <div class="marker-pulse" style="background: rgba(5, 150, 105, 0.2);"></div>
                        <span
                            style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 0.6rem; font-weight: 800; color: var(--dark); white-space: nowrap;">🇳🇬
                            Nigeria</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Structure -->
    <div class="modal-overlay" id="projectModal">
        <div class="modal-container">
            <div class="modal-header">
                <div>
                    <span id="modalCategory"
                        style="color: #059669; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Category</span>
                    <h2 id="modalTitle" style="font-family: var(--font-display); font-size: 1.5rem; margin-top: 4px;">
                        Project Title</h2>
                </div>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <a href="#" id="modalLiveLink" target="_blank" class="btn-primary"
                        style="padding: 0.6rem 1.2rem; font-size: 0.85rem;">View Live Site</a>
                    <button class="modal-close" onclick="closeModal()">✕</button>
                </div>
            </div>
            <div class="modal-body">
                <img id="modalImg" src="" alt="Project Image" class="modal-hero-img">
                <div class="modal-info-grid">
                    <div>
                        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">🌐 Project
                            Overview</h3>
                        <p id="modalDesc" style="color: var(--text-muted); line-height: 1.6; font-size: 0.95rem;">
                            Description here...</p>
                    </div>
                    <div>
                        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">🛠️ Tools Used
                        </h3>
                        <div class="tool-pills" id="modalTools">
                            <!-- Pills will be injected here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="testimonials" style="background: var(--gray-light);">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Reviews</span>
                <h2 class="section-title">What Our <span style="color: var(--blue)">Clients Say</span></h2>
                <p>Real results from real businesses. Here's what my clients have to say about working with me & my
                    company.

                </p>
            </div>
            <div class="testimonial-carousel reveal">
                <div class="testimonial-track" id="testimonialTrack">
                    <?php if (empty($db_reviews)): ?>
                        <!-- Fallback demo reviews -->
                        <div class="testimonial-slide">
                            <div class="service-card">
                                <div style="color: var(--orange); margin-bottom: 1rem;">★★★★★</div>
                                <p style="font-style: italic; margin-bottom: 1.5rem;">"Marrthings transformed our online
                                    presence. The new site is not only beautiful but it's incredibly fast."</p>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div
                                        style="width: 40px; height: 40px; border-radius: 50%; background: var(--blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                        JS</div>
                                    <div>
                                        <h4 style="font-size: 0.9rem;">John Smith</h4>
                                        <p style="font-size: 0.7rem; color: var(--text-muted);">CEO, TechFlow</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($db_reviews as $r): ?>
                            <div class="testimonial-slide">
                                <div class="service-card">
                                    <div style="color: var(--orange); margin-bottom: 1rem;">
                                        <?php echo str_repeat('★', $r['rating']); ?>
                                    </div>
                                    <p style="font-style: italic; margin-bottom: 1.5rem;">
                                        "<?php echo htmlspecialchars($r['content']); ?>"</p>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: var(--blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; overflow: hidden;">
                                            <?php if (!empty($r['image_url'])): ?>
                                                <img src="<?php echo $r['image_url']; ?>"
                                                    alt="<?php echo htmlspecialchars($r['name']); ?>"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <?php
                                                $names = explode(' ', $r['name']);
                                                echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 style="font-size: 0.9rem;"><?php echo htmlspecialchars($r['name']); ?></h4>
                                            <p style="font-size: 0.7rem; color: var(--text-muted);">
                                                <?php echo htmlspecialchars($r['role']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="carousel-dots" id="carouselDots"></div>
        </div>

        <!-- Fun Fact Box -->
        <div class="fun-fact-box reveal">
            <p class="fun-fact-text">🎉 <strong>Fun fact:</strong> Marrthings is one of the <span
                    style="color: #059669;">highest rated</span> on Google for web development, SEO & digital
                marketing!</p>
            <div class="fun-fact-stars">★★★★★ <span
                    style="color: var(--dark); font-size: 1rem; font-weight: 800;">4.95/5</span></div>
            <a href="https://maps.app.goo.gl/6B1GsrpreRecba7s5" target="_blank" class="verify-btn">
                <img src="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_128dp.png"
                    alt="Google" style="width: 18px;">
                Verify Rating on Google
            </a>
        </div>
        </div>
    </section>

    <section id="about" style="background: #f8fafc; padding: 8rem 0;">
        <div class="container">
            <div class="about-wrapper">
                <!-- Left Column -->
                <div class="reveal">
                    <div
                        style="background: #ecfdf5; display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid #d1fae5; margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: #059669;">
                        About Me</div>
                    <h2 class="section-title" style="font-size: 3rem; margin-bottom: 1.5rem;">Hi, I'm <span
                            style="color: #059669">Ajayi Opeyemi Martins</span></h2>

                    <div class="profile-img-box">
                        <img src="profile.png" alt="Ajayi Opeyemi Martins">
                    </div>

                    <p
                        style="color: var(--text-muted); line-height: 1.8; max-width: 550px; margin-bottom: 2rem; font-size: 1rem;">
                        I'm a passionate <strong>Techpreneur, Web Developer, SEO Specialist</strong>, and
                        <strong>Digital Marketer</strong> based in Nigeria, with over 8 years of experience helping
                        businesses establish and grow their online presence.
                        <br><br>
                        As the founder of <strong>MAlvNET</strong>, I've had the privilege of working with businesses
                        across various industries - from startups to established enterprises. My approach combines
                        technical expertise with strategic thinking to deliver solutions that don't just look good, but
                        actually drive results.
                        <br><br>
                        I believe in the power of data-driven decisions, clean code, and user-centered design. Every
                        project I take on is an opportunity to create something meaningful that helps businesses connect
                        with their customers and achieve their goals.
                        <br><br>
                        When I'm not coding or optimizing campaigns, you'll find me exploring new technologies,
                        contributing to the developer community, or mentoring aspiring digital professionals.
                    </p>

                    <div style="display: flex; gap: 15px; margin-bottom: 3rem;">
                        <a href="https://facebook.com/ajayiope" target="_blank" aria-label="Follow Marrthings on Facebook"
                            style="width: 45px; height: 45px; border-radius: 50%; background: #1877F2; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; font-size: 1.2rem;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(24,119,242,0.3)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com/malvnet" target="_blank" aria-label="Follow Marrthings on Instagram"
                            style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; font-size: 1.2rem;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(220,39,67,0.3)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com/in/ajayiope" target="_blank" aria-label="Follow Marrthings on LinkedIn"
                            style="width: 45px; height: 45px; border-radius: 50%; background: #0077b5; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; font-size: 1.2rem;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,119,181,0.3)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://x.com/amarrthings" target="_blank" aria-label="Follow Marrthings on X (Twitter)"
                            style="width: 45px; height: 45px; border-radius: 50%; background: #000000; color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; font-size: 1.2rem;"
                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.3)'"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                    </div>

                    <div class="about-stats-grid">
                        <div class="a-stat-card">
                            <div class="a-stat-icon">🎖️</div>
                            <div class="a-stat-info">
                                <h4>100+</h4>
                                <p>Projects</p>
                            </div>
                        </div>
                        <div class="a-stat-card">
                            <div class="a-stat-icon">👥</div>
                            <div class="a-stat-info">
                                <h4>50+</h4>
                                <p>Clients</p>
                            </div>
                        </div>
                        <div class="a-stat-card">
                            <div class="a-stat-icon">🕒</div>
                            <div class="a-stat-info">
                                <h4>8+ Years</h4>
                                <p>Experience</p>
                            </div>
                        </div>
                        <div class="a-stat-card">
                            <div class="a-stat-icon">❤️</div>
                            <div class="a-stat-info">
                                <h4>99%</h4>
                                <p>Satisfaction</p>
                            </div>
                        </div>
                    </div>

                    <div
                        style="background: #fff; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--gray-border); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">Serving clients in:
                            <span style="color: var(--dark); margin-left: 10px;">NG GB US</span>
                        </div>
                        <div
                            style="background: #ecfdf5; color: #059669; padding: 8px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                            🏆 Best Developer in Nigeria 🏆
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <a href="#booking" class="btn-primary"
                            style="background: #059669; border-color: #059669; padding: 1rem 2.5rem; flex: 1; text-align: center;">Hire
                            Me</a>
                        <a href="https://wa.me/2349022961144" class="btn-outline"
                            style="border-color: #059669; color: #059669; padding: 1rem 2.5rem; flex: 1; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span style="font-size: 1.2rem;">💬</span> WhatsApp Me
                        </a>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="reveal">
                    <div class="about-card">
                        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-display);">Skills & Expertise</h3>

                        <div class="skills-cat">
                            <h5>Web Development</h5>
                            <div class="skills-pills">
                                <span class="skill-pill">WordPress</span>
                                <span class="skill-pill">Shopify</span>
                                <span class="skill-pill">Wix</span>
                                <span class="skill-pill">React</span>
                                <span class="skill-pill">PHP</span>
                                <span class="skill-pill">JavaScript</span>
                            </div>
                        </div>

                        <div class="skills-cat">
                            <h5>Design</h5>
                            <div class="skills-pills">
                                <span class="skill-pill">Canva</span>
                                <span class="skill-pill">Photoshop</span>
                                <span class="skill-pill">Figma</span>
                                <span class="skill-pill">UI/UX</span>
                            </div>
                        </div>

                        <div class="skills-cat">
                            <h5>SEO & Marketing</h5>
                            <div class="skills-pills">
                                <span class="skill-pill">Google Analytics</span>
                                <span class="skill-pill">SEMrush</span>
                                <span class="skill-pill">Ahrefs</span>
                                <span class="skill-pill">Meta Ads</span>
                                <span class="skill-pill">Google Ads</span>
                            </div>
                        </div>

                        <div class="skills-cat" style="margin-bottom: 0;">
                            <h5>Tools</h5>
                            <div class="skills-pills">
                                <span class="skill-pill">cPanel</span>
                                <span class="skill-pill">Cloudflare</span>
                                <span class="skill-pill">GitHub</span>
                                <span class="skill-pill">VS Code</span>
                                <span class="skill-pill">Elementor</span>
                            </div>
                        </div>
                    </div>

                    <div class="about-card" style="margin-bottom: 0;">
                        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-display);">Professional Journey</h3>

                        <div class="timeline-item">
                            <span class="timeline-date">2016</span>
                            <h4 class="timeline-title">Started Web Development</h4>
                            <p class="timeline-desc">Began my journey into web development, learning HTML, CSS, and
                                JavaScript.</p>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date">2018</span>
                            <h4 class="timeline-title">WordPress Specialist</h4>
                            <p class="timeline-desc">Mastered WordPress development and started building custom themes
                                and plugins.</p>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date">2019</span>
                            <h4 class="timeline-title">Founded Marrthings</h4>
                            <p class="timeline-desc">Launched Marrthings as a high-performance digital presence
                                combining development with technical marketing.</p>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date">2021</span>
                            <h4 class="timeline-title">SEO & Marketing Focus</h4>
                            <p class="timeline-desc">Expanded services to include comprehensive SEO and performance
                                digital marketing solutions.</p>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date">2023</span>
                            <h4 class="timeline-title">100+ Projects Milestone</h4>
                            <p class="timeline-desc">Celebrated completing 100+ successful projects for clients
                                worldwide with 99% satisfaction.</p>
                        </div>

                        <div class="timeline-item" style="margin-bottom: 0;">
                            <span class="timeline-date">Present</span>
                            <h4 class="timeline-title">Continuous Innovation</h4>
                            <p class="timeline-desc">Embracing AI tools and modern technologies to deliver cutting-edge
                                solutions for global brands.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="booking" class="booking-section">
        <div class="container">
            <div class="section-header reveal">
                <div
                    style="background: #fff; display: inline-block; padding: 0.4rem 1.2rem; border-radius: 50px; border: 1px solid var(--gray-border); margin-bottom: 1.5rem; font-size: 0.75rem; font-weight: 800; color: #059669;">
                    Start Your Project</div>
                <h2 class="section-title">Book Your <span style="color: #059669">Service</span></h2>
            </div>

            <div class="booking-container reveal">
                <div class="booking-steps">
                    <div class="step-circle active" data-step="1">1</div>
                    <div class="step-circle" data-step="2">2</div>
                    <div class="step-circle" data-step="3">3</div>
                    <div class="step-circle" data-step="4">4</div>
                </div>

                <div class="booking-body">
                    <!-- Step 1: Service Type -->
                    <div class="booking-step-content active" id="step1">
                        <div
                            style="background: #ecfdf5; padding: 0.8rem; border-radius: 10px; border: 1px solid #d1fae5; margin-bottom: 2rem; text-align: center; font-size: 0.85rem; color: #065f46;">
                            ✨ Select the services you need below, then add optional extras to get your quote
                        </div>
                        <h3 style="margin-bottom: 0.5rem;">Step 1: Select Service Type</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">What type of
                            service are you looking for?</p>

                        <div class="service-type-grid">
                            <?php foreach ($booking_categories as $cat): ?>
                            <div class="type-card" onclick="selectType('<?php echo $cat['slug']; ?>', this)">
                                <span class="type-icon"><?php echo $cat['icon']; ?></span>
                                <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                                <p><?php echo $cat['slug'] == 'web' ? 'Websites & Web Apps' : 'Custom Solutions'; // Dynamic enough for now ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Specific Services -->
                    <div class="booking-step-content" id="step2">
                        <div
                            style="background: #ecfdf5; padding: 0.8rem; border-radius: 10px; border: 1px solid #d1fae5; margin-bottom: 2rem; text-align: center; font-size: 0.85rem; color: #065f46;">
                            ✨ Choose the specific service that best matches your project needs
                        </div>
                        <h3 style="margin-bottom: 0.5rem;">Step 2: Select Your Service</h3>
                        <p id="step2-subtitle"
                            style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">Choose the
                            specific service you need</p>

                        <div class="sub-service-grid" id="sub-service-container">
                            <!-- Dynamic content based on selection -->
                        </div>
                    </div>

                    <!-- Step 3: Add-ons & Timeline -->
                    <div class="booking-step-content" id="step3">
                        <div
                            style="background: #ecfdf5; padding: 0.8rem; border-radius: 10px; border: 1px solid #d1fae5; margin-bottom: 2rem; text-align: center; font-size: 0.85rem; color: #065f46;">
                            ✨ Add optional extras to enhance your project, then set your timeline
                        </div>
                        <h3 style="margin-bottom: 1.5rem;">Step 3: Add-ons & Timeline</h3>

                        <p style="font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem;">Optional Add-on Services
                        </p>
                        <div class="sub-service-grid" style="margin-bottom: 2rem;">
                            <?php foreach ($db_addons as $addon): ?>
                            <div class="sub-card" onclick="toggleAddon('<?php echo $addon['id']; ?>', <?php echo $addon['min_price']; ?>, this)">
                                <div class="sub-info">
                                    <h4><?php echo htmlspecialchars($addon['name']); ?></h4>
                                    <p>+<?php echo $addon['price_text']; ?></p>
                                </div>
                                <input type="checkbox" style="accent-color: #059669;">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <p style="font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem;">⏱️ Project Timeline</p>
                        <div class="timeline-grid">
                            <?php foreach ($db_timelines as $time): ?>
                            <div class="timeline-card <?php echo $time['is_default'] ? 'selected' : ''; ?>" onclick="selectTimeline('<?php echo strtolower($time['name']); ?>', this)">
                                <h4><?php echo htmlspecialchars($time['name']); ?></h4>
                                <p><?php echo htmlspecialchars($time['duration']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="budget-summary">
                            <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">Estimated Project
                                Budget</p>
                            <div class="budget-amount" id="display-budget">₦150,000 - ₦300,000</div>
                            <p style="font-size: 0.7rem; color: var(--text-muted);">Final quote confirmed after project
                                review.</p>
                        </div>
                    </div>

                    <!-- Step 4: Final Details -->
                    <div class="booking-step-content" id="step4">
                        <div
                            style="background: #ecfdf5; padding: 0.8rem; border-radius: 10px; border: 1px solid #d1fae5; margin-bottom: 2rem; text-align: center; font-size: 0.85rem; color: #065f46;">
                            ✨ Fill in your contact details so we can reach you with your quote
                        </div>
                        <h3 style="margin-bottom: 0.5rem;">Step 4: Your Details</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">Provide your
                            contact information</p>

                        <div
                            style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">Your Estimated
                                    Budget</p>
                                <h4 style="color: #059669; font-size: 1.2rem;" id="final-budget">₦225,000 - ₦450,000
                                </h4>
                            </div>
                            <div style="text-align: right;">
                                <p id="final-service-type" style="font-size: 0.8rem; font-weight: 600;">Web Development
                                </p>
                                <span
                                    style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800;">Growth</span>
                            </div>
                        </div>

                        <form id="booking-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div style="grid-column: span 1;"><input type="text" placeholder="Your Name *" required
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                            </div>
                            <div style="grid-column: span 1;"><input type="email" placeholder="Email Address *" required
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                            </div>
                            <div style="grid-column: span 1;"><input type="tel" placeholder="Phone Number *" required
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                            </div>
                            <div style="grid-column: span 1;">
                                <select required id="booking-country"
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <option value="">Select country</option>
                                    <option value="Nigeria" selected>Nigeria</option>
                                    <option value="United States">United States</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="India">India</option>
                                    <option value="China">China</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div style="grid-column: span 2;">
                                <select required
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <option value="">How did you hear about us?</option>
                                    <option value="Google">Google</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Referral">Referral</option>
                                </select>
                            </div>
                            <div style="grid-column: span 2;">
                                <textarea placeholder="Brief description of your project..."
                                    style="width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px; height: 100px;"></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="booking-footer">
                    <button class="btn-outline" id="prevBtn" onclick="prevStep()"
                        style="display: none; border-color: #e2e8f0; color: #64748b;">← Back</button>
                    <div></div> <!-- Spacer -->
                    <button class="btn-primary" id="nextBtn" onclick="nextStep()"
                        style="background: #059669; border-color: #059669;">Continue ➔</button>
                    <button class="btn-primary" id="submitBtn" onclick="submitBooking()"
                        style="display: none; background: #059669; border-color: #059669;">Submit via WhatsApp
                        ➔</button>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" style="background: #f8fafc; padding: 8rem 0;">
        <div class="container">
            <div class="contact-grid">
                <!-- Left Column: Form -->
                <div class="reveal">
                    <div class="contact-form-box">
                        <form id="main-contact-form" method="POST">
                            <div class="contact-group">
                                <label class="contact-label">📧 Full Name</label>
                                <input type="text" name="name" class="contact-input" placeholder="Your name" required>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="contact-group">
                                    <label class="contact-label">✉️ Email</label>
                                    <input type="email" name="email" class="contact-input" placeholder="your@email.com"
                                        required>
                                </div>
                                <div class="contact-group">
                                    <label class="contact-label">📞 Phone *</label>
                                    <input type="tel" name="phone" class="contact-input" placeholder="+234..." required>
                                </div>
                            </div>

                            <div class="contact-group">
                                <label class="contact-label">🌐 Country</label>
                                <select class="contact-input" required id="contact-country">
                                    <option value="">Select country</option>
                                    <option value="Nigeria" selected>Nigeria</option>
                                    <option value="United States">United States</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="India">India</option>
                                    <option value="China">China</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="contact-group">
                                <label class="contact-label">📢 How did you hear about us?</label>
                                <select class="contact-input" required>
                                    <option value="">Select an option</option>
                                    <option value="Google">Google Search</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Referral">Referral</option>
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="contact-group">
                                    <label class="contact-label">💬 Service Interested In</label>
                                    <select class="contact-input" name="subject" required>
                                        <option value="">Select a service</option>
                                        <option value="Web Development">Web Development</option>
                                        <option value="SEO">SEO Optimization</option>
                                        <option value="Branding">Branding</option>
                                    </select>
                                </div>
                                <div class="contact-group">
                                    <label class="contact-label">💰 Budget Range</label>
                                    <select class="contact-input" name="budget_range" required>
                                        <option value="">$200 - $500</option>
                                        <option value="$500 - $2000">$500 - $2,000</option>
                                        <option value="$2000+">$2,000+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="contact-group">
                                <label class="contact-label">Your Message</label>
                                <textarea class="contact-input" name="message" style="height: 120px;"
                                    placeholder="Tell me about your project..."></textarea>
                            </div>

                            <input type="hidden" name="contact_submit" value="1">
                            <button type="submit" class="btn-primary"
                                style="width: 100%; background: #059669; border-color: #059669; justify-content: center; padding: 1rem;">
                                Send Message 🚀
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Info Cards -->
                <div class="reveal">
                    <div class="c-card">
                        <h3 style="margin-bottom: 2rem; font-family: var(--font-display);">Quick Contact</h3>

                        <div class="c-info-item">
                            <div class="c-info-icon">✉️</div>
                            <div class="c-info-text">
                                <h5>Email</h5>
                                <p>hello@marrthings.com.ng</p>
                            </div>
                        </div>

                        <div class="c-info-item">
                            <div class="c-info-icon">📅</div>
                            <div class="c-info-text">
                                <h5>Bookings</h5>
                                <p>bookings@marrthings.com.ng</p>
                            </div>
                        </div>

                        <div class="c-info-item">
                            <div class="c-info-icon">📞</div>
                            <div class="c-info-text">
                                <h5>Phone / WhatsApp</h5>
                                <p>+234 902 296 1144</p>
                            </div>
                        </div>

                        <div class="c-info-item">
                            <div class="c-info-icon">📍</div>
                            <div class="c-info-text">
                                <h5>Location</h5>
                                <p>Lagos, Nigeria <span style="font-size: 0.7rem; font-weight: 500;">(Working
                                        Globally)</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="c-card">
                        <h3 style="margin-bottom: 1.5rem; font-family: var(--font-display);">Why Work With Me?</h3>
                        <div class="c-why-grid">
                            <div class="c-why-item">
                                <h4>24h</h4>
                                <p>Response Time</p>
                            </div>
                            <div class="c-why-item">
                                <h4>50+</h4>
                                <p>Happy Clients</p>
                            </div>
                            <div class="c-why-item">
                                <h4>99%</h4>
                                <p>Satisfaction</p>
                            </div>
                            <div class="c-why-item">
                                <h4>100+</h4>
                                <p>Projects</p>
                            </div>
                        </div>
                    </div>

                    <div class="c-card" style="margin-bottom: 0;">
                        <div class="status-pill">
                            <div class="status-dot"></div>
                            <div style="font-size: 0.8rem; font-weight: 700;">Currently Available for Projects Today -
                                <span id="availability-date"><?php echo date('F j, Y'); ?></span>
                            </div>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.5rem;">I'm currently
                            accepting new projects and would love to hear about yours. Let's discuss how I can help you
                            achieve your goals.</p>
                        <a href="https://wa.me/2349022961144" class="btn-primary"
                            style="width: 100%; background: #059669; border-color: #059669; justify-content: center; gap: 8px;">
                            💬 Let's Talk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-section">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: Brand -->
                <div class="footer-col">
                    <a href="#" class="footer-logo"
                        style="margin-bottom: 1.5rem; display: inline-block;">Marr<span>things</span></a>
                    <p style="color: #94a3b8; line-height: 1.6; font-size: 0.9rem; max-width: 300px;">
                        Helping businesses and personal brands establish a premium digital presence through technical
                        excellence and strategic growth solutions.
                    </p>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#portfolio">Portfolio</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#testimonials">Reviews</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <!-- Col 3: Services -->
                <div class="footer-col">
                    <h4>Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="#">Web Development</a></li>
                        <li><a href="#">SEO Optimization</a></li>
                        <li><a href="#">Digital Marketing</a></li>
                        <li><a href="#">Automation</a></li>
                        <li><a href="#">E-commerce</a></li>
                        <li><a href="#">Branding</a></li>
                    </ul>
                </div>

                <!-- Col 4: Connect -->
                <div class="footer-col">
                    <h4>Connect</h4>
                    <ul class="footer-links">
                        <li><a href="#">WhatsApp</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">LinkedIn</a></li>
                        <li><a href="#">Twitter / X</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© 2026 Marrthings. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/2349022961144" class="whatsapp-float" target="_blank">
        <svg style="width: 32px; height: 32px;" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12.012 2c-5.508 0-9.987 4.479-9.987 9.987 0 1.757.463 3.409 1.271 4.846l-1.352 4.938 5.056-1.327c1.411.769 3.012 1.205 4.712 1.205 5.508 0 9.987-4.479 9.987-9.987s-4.479-9.987-9.987-9.987zm0 17.561c-1.576 0-3.05-.431-4.322-1.184l-.31-.184-3.2 0.84 0.852-3.111-.202-.321c-.752-1.196-1.15-2.585-1.15-4.014 0-4.183 3.404-7.587 7.587-7.587 4.183 0 7.587 3.404 7.587 7.587s-3.404 7.587-7.587 7.587z" />
        </svg>
    </a>

    <script>
        const projectsData = <?php 
            if (empty($db_projects)) {
                echo json_encode([[
                    'id' => 'cre8hive',
                    'title' => 'The Cre8hive App',
                    'category' => 'Banking & Fintech',
                    'description' => 'Fintech platform for creative professionals and business management.',
                    'image_url' => 'assets/images/p1.png',
                    'live_link' => '#',
                    'tools' => 'Fintech'
                ]]);
            } else {
                echo json_encode($db_projects);
            }
        ?>;

        function openModal(id) {
            const project = projectsData.find(p => p.id == id);
            if (!project) return;

            document.getElementById('modalTitle').textContent = project.title;
            document.getElementById('modalCategory').textContent = project.category;
            document.getElementById('modalDesc').textContent = project.description;
            document.getElementById('modalImg').src = project.image_url || 'assets/images/p1.png';
            document.getElementById('modalLiveLink').href = project.live_link || '#';

            const toolsContainer = document.getElementById('modalTools');
            toolsContainer.innerHTML = '';
            if (project.tools) {
                project.tools.split(',').forEach(tool => {
                    const pill = document.createElement('div');
                    pill.className = 'tool-pill-sm';
                    pill.textContent = tool.trim();
                    toolsContainer.appendChild(pill);
                });
            }

            document.getElementById('projectModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('projectModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        // Close modal on background click
        document.getElementById('projectModal').addEventListener('click', (e) => {
            if (e.target.id === 'projectModal') closeModal();
        });

        // Typewriter Effect
        const words = ["Experiences", "Solutions", "Growth", "Future"];
        let i = 0;
        let j = 0;
        let currentWord = "";
        let isDeleting = false;
        const typewriter = document.getElementById("typewriter");

        function type() {
            currentWord = words[i];
            if (isDeleting) {
                typewriter.textContent = currentWord.substring(0, j - 1);
                j--;
                if (j === 0) {
                    isDeleting = false;
                    i = (i + 1) % words.length;
                }
            } else {
                typewriter.textContent = currentWord.substring(0, j + 1);
                j++;
                if (j === currentWord.length) {
                    isDeleting = true;
                    setTimeout(type, 1500);
                    return;
                }
            }
            setTimeout(type, isDeleting ? 100 : 200);
        }
        type();

        // Scroll Reveal
        const reveal = () => {
            const reveals = document.querySelectorAll(".reveal");
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    el.classList.add("active");
                }
            });
        };
        window.addEventListener("scroll", reveal);
        reveal();

        // Navbar Scroll + sync mobile menu position
        const nav = document.getElementById("navbar");
        const mobileNavEl = document.getElementById('mobile-nav');
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                nav.classList.add("scrolled");
                mobileNavEl.classList.add("scrolled");
            } else {
                nav.classList.remove("scrolled");
                mobileNavEl.classList.remove("scrolled");
            }
        });

        // Theme Toggle
        const themeBtn = document.getElementById("theme-btn");
        themeBtn.addEventListener("click", () => {
            const currentTheme = document.documentElement.getAttribute("data-theme");
            const newTheme = currentTheme === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", newTheme);
            themeBtn.textContent = newTheme === "dark" ? "☀️" : "🌙";
        });

        // Hamburger Menu Toggle
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileNav = document.getElementById('mobile-nav');
        hamburgerBtn.addEventListener('click', () => {
            hamburgerBtn.classList.toggle('open');
            mobileNav.classList.toggle('open');
        });
        // Close mobile nav when a link is clicked
        document.querySelectorAll('.mob-link, .mob-cta').forEach(link => {
            link.addEventListener('click', () => {
                hamburgerBtn.classList.remove('open');
                mobileNav.classList.remove('open');
            });
        });

        // Booking Logic
        let currentBookingStep = 1;
        let selectedBookingType = '';
        let selectedBookingService = null;
        let bookingAddons = [];
        let bookingTimeline = 'flexible';
        let bookingBaseMin = 150000;
        let bookingBaseMax = 300000;

        const bookingServicesData = <?php echo json_encode($js_services_data); ?>;

        function selectType(type, element) {
            selectedBookingType = type;
            document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');

            const data = bookingServicesData[type];
            document.getElementById('step2-subtitle').innerText = `Choose the specific ${data.title} service you need`;
            const container = document.getElementById('sub-service-container');
            container.innerHTML = '';
            data.options.forEach(opt => {
                const div = document.createElement('div');
                div.className = 'sub-card';
                div.innerHTML = `<div class="sub-info"><h4>${opt.name}</h4><p>₦${opt.min.toLocaleString()} - ₦${opt.max.toLocaleString()}</p></div>`;
                div.onclick = () => selectService(opt, div);
                container.appendChild(div);
            });
        }

        function selectService(opt, element) {
            selectedBookingService = opt;
            document.querySelectorAll('.sub-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            bookingBaseMin = opt.min;
            bookingBaseMax = opt.max;
            updateBudgetDisplay();
        }

        function toggleAddon(id, price, element) {
            element.classList.toggle('selected');
            const cb = element.querySelector('input');
            cb.checked = !cb.checked;
            if (cb.checked) bookingAddons.push({ id, price });
            else bookingAddons = bookingAddons.filter(a => a.id !== id);
            updateBudgetDisplay();
        }

        function selectTimeline(t, element) {
            bookingTimeline = t;
            document.querySelectorAll('.timeline-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
        }

        function updateBudgetDisplay() {
            let addMin = bookingAddons.reduce((sum, a) => sum + a.price, 0);
            let totalMin = bookingBaseMin + addMin;
            let totalMax = bookingBaseMax + (addMin * 1.5);
            const budgetStr = `₦${totalMin.toLocaleString()} - ₦${totalMax.toLocaleString()}`;
            document.getElementById('display-budget').innerText = budgetStr;
            document.getElementById('final-budget').innerText = budgetStr;
        }

        function nextStep() {
            if (currentBookingStep === 1 && !selectedBookingType) return alert('Please select a service type');
            if (currentBookingStep === 2 && !selectedBookingService) return alert('Please select a specific service');

            if (currentBookingStep < 4) {
                document.getElementById(`step${currentBookingStep}`).classList.remove('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.remove('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.add('completed');
                currentBookingStep++;
                document.getElementById(`step${currentBookingStep}`).classList.add('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.add('active');

                document.getElementById('prevBtn').style.display = 'block';
                if (currentBookingStep === 4) {
                    document.getElementById('nextBtn').style.display = 'none';
                    document.getElementById('submitBtn').style.display = 'block';
                    document.getElementById('final-service-type').innerText = selectedBookingService.name;
                }
            }
        }

        function prevStep() {
            if (currentBookingStep > 1) {
                document.getElementById(`step${currentBookingStep}`).classList.remove('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.remove('active');
                currentBookingStep--;
                document.getElementById(`step${currentBookingStep}`).classList.add('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.add('active');
                document.querySelector(`.step-circle[data-step="${currentBookingStep}"]`).classList.remove('completed');

                document.getElementById('nextBtn').style.display = 'block';
                document.getElementById('submitBtn').style.display = 'none';
                if (currentBookingStep === 1) document.getElementById('prevBtn').style.display = 'none';
            }
        }

        function submitBooking() {
            const name = document.querySelector('#step4 input[placeholder*="Name"]').value;
            if (!name) return alert('Please enter your name');
            const budget = document.getElementById('final-budget').innerText;
            const msg = `Hello Martins! I'd like to book a project.\n\nProject: ${selectedBookingService.name}\nBudget: ${budget}\nTimeline: ${bookingTimeline}\nName: ${name}`;
            window.open(`https://wa.me/2349022961144?text=${encodeURIComponent(msg)}`);
        }

        /* ── TESTIMONIAL CAROUSEL LOGIC ── */
        const track = document.getElementById('testimonialTrack');
        const dotsContainer = document.getElementById('carouselDots');
        const slides = Array.from(track.children);
        let currentIndex = 0;
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;
        let animationID;
        let autoSlideInterval;

        function updateCarousel() {
            const isMobile = window.innerWidth <= 992;
            const itemsPerView = isMobile ? 1 : 2;
            const slideWidth = 100 / itemsPerView;
            currentTranslate = currentIndex * -slideWidth;
            prevTranslate = currentTranslate;
            setSliderPosition();

            Array.from(dotsContainer.children).forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }

        function setSliderPosition() {
            track.style.transform = `translateX(${currentTranslate}%)`;
        }

        function setupCarousel() {
            const isMobile = window.innerWidth <= 992;
            const itemsPerView = isMobile ? 1 : 2;
            const dotCount = slides.length - (itemsPerView - 1);

            dotsContainer.innerHTML = '';
            for (let i = 0; i < dotCount; i++) {
                const dot = document.createElement('div');
                dot.className = `dot ${i === 0 ? 'active' : ''}`;
                dot.onclick = () => {
                    currentIndex = i;
                    updateCarousel();
                    startAutoSlide(); // Reset timer on click
                };
                dotsContainer.appendChild(dot);
            }
            currentIndex = 0;
            updateCarousel();
            startAutoSlide();
        }

        function startAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(() => {
                const isMobile = window.innerWidth <= 992;
                const itemsPerView = isMobile ? 1 : 2;
                const maxIndex = slides.length - itemsPerView;

                if (currentIndex < maxIndex) {
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
                updateCarousel();
            }, 7000); // 7s auto-slide
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Dragging Logic
        track.addEventListener('touchstart', () => { touchStart(event); stopAutoSlide(); });
        track.addEventListener('touchend', () => { touchEnd(); startAutoSlide(); });
        track.addEventListener('touchmove', touchMove);
        track.addEventListener('mousedown', (e) => { touchStart(e); stopAutoSlide(); });
        track.addEventListener('mouseup', () => { touchEnd(); startAutoSlide(); });
        track.addEventListener('mousemove', touchMove);
        track.addEventListener('mouseleave', () => { touchEnd(); startAutoSlide(); });

        // Pause on hover
        track.addEventListener('mouseenter', stopAutoSlide);
        track.addEventListener('mouseleave', startAutoSlide);

        function getPositionX(event) {
            return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
        }

        function touchStart(event) {
            isDragging = true;
            startPos = getPositionX(event);
            animationID = requestAnimationFrame(animation);
            track.style.cursor = 'grabbing';
            track.style.transition = 'none';
        }

        function touchMove(event) {
            if (isDragging) {
                const currentPosition = getPositionX(event);
                const diff = ((currentPosition - startPos) / window.innerWidth) * 100;
                currentTranslate = prevTranslate + diff;
            }
        }

        function touchEnd() {
            isDragging = false;
            cancelAnimationFrame(animationID);
            track.style.cursor = 'grab';
            track.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';

            const isMobile = window.innerWidth <= 992;
            const itemsPerView = isMobile ? 1 : 2;
            const slideWidth = 100 / itemsPerView;
            const movedBy = currentTranslate - prevTranslate;

            if (movedBy < -5 && currentIndex < slides.length - itemsPerView) currentIndex += 1;
            if (movedBy > 5 && currentIndex > 0) currentIndex -= 1;

            updateCarousel();
        }

        function animation() {
            setSliderPosition();
            if (isDragging) requestAnimationFrame(animation);
        }

        window.addEventListener('resize', setupCarousel);
        setupCarousel();

        // Portfolio Filtering & Load More
        const portfolioGrid = document.getElementById('portfolioGrid');
        const projectItems = document.querySelectorAll('.project-item');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadMoreSection = document.getElementById('loadMoreSection');
        const visibleCountSpan = document.getElementById('visibleCount');

        let currentFilter = 'all';
        let itemsToShow = 6;

        function updatePortfolio() {
            let visibleItems = [];
            projectItems.forEach(item => {
                const category = item.getAttribute('data-category');
                if (currentFilter === 'all' || category === currentFilter) {
                    visibleItems.push(item);
                } else {
                    item.style.display = 'none';
                    item.classList.remove('active');
                }
            });

            // Show first N items of the visible set
            const itemsToDisplay = visibleItems.slice(0, itemsToShow);
            visibleItems.forEach(item => {
                if (itemsToDisplay.includes(item)) {
                    item.style.display = 'block';
                    // Trigger reveal after a short delay if it's already in viewport or next scroll
                    setTimeout(() => item.classList.add('active'), 50);
                } else {
                    item.style.display = 'none';
                    item.classList.remove('active');
                }
            });

            // Update count text
            visibleCountSpan.innerText = itemsToDisplay.length;
            
            // Show/Hide Load More button section
            if (itemsToDisplay.length < visibleItems.length) {
                loadMoreSection.style.display = 'block';
            } else {
                loadMoreSection.style.display = 'none';
            }
        }

        filterBtns.forEach(btn => {
            btn.onclick = () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                itemsToShow = 6; // Reset count on filter change
                updatePortfolio();
            };
        });

        if (loadMoreBtn) {
            loadMoreBtn.onclick = () => {
                itemsToShow += 6;
                updatePortfolio();
            };
        }

        // Dynamic Date for Availability
        function updateAvailabilityDate() {
            const dateElement = document.getElementById('availability-date');
            if (dateElement) {
                const options = { month: 'long', day: 'numeric', year: 'numeric' };
                dateElement.innerText = new Date().toLocaleDateString('en-US', options);
            }
        }
        updateAvailabilityDate();
    </script>
    <script>
        // Preloader Logic
        window.addEventListener('load', () => {
            const preloader = document.getElementById('preloader');
            setTimeout(() => {
                preloader.classList.add('fade-out');
                document.body.style.overflow = 'auto'; // Re-enable scroll
            }, 2000); // Wait for animation
        });
    </script>
</body>

</html>
<?php
// index.php

require 'config.php';

// Fetch products from the database
$stmt = $pdo->query('SELECT * FROM products');
$products = $stmt->fetchAll();

// Initialize cart count
session_start();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Cookie Handling
$cookie_name = "user";
$cookie_value = "FitFusionUser";
$cookie_expiry = time() + (86400 * 30); // 30 days

// Create or modify cookie
if (!isset($_COOKIE[$cookie_name])) {
    setcookie($cookie_name, $cookie_value, $cookie_expiry, "/");
} else {
    // Modify cookie value
    $cookie_value = "OtherUser";
    setcookie($cookie_name, $cookie_value, $cookie_expiry, "/");
}

// Delete cookie (example)
if (isset($_GET['delete_cookie'])) {
    setcookie($cookie_name, "", time() - 3600, "/");
    header("Location: index.php"); // Refresh the page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitFusion - Black & Neon Design</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Google Fonts for Poppins font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #000; /* Pure black background */
            color: #fff; /* White text */
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(0, 0, 0, 0.9); /* Semi-transparent black */
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            z-index: 100;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Subtle border */
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 40px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            position: relative;
            padding-bottom: 5px;
            transition: all 0.3s;
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #00ffcc, #ff00cc); /* Neon gradient */
            transition: width 0.3s;
        }

        nav a:hover::after {
            width: 100%;
        }

        .header-icons {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .cart-icon {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff00cc; /* Neon accent */
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(0, 255, 204, 0.1); /* Neon glow */
            top: -100px;
            right: -100px;
            filter: blur(100px);
            animation: float 15s infinite alternate;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 0, 204, 0.1); /* Neon glow */
            bottom: -50px;
            left: -50px;
            filter: blur(80px);
            animation: float 10s infinite alternate-reverse;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .hero-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            position: relative;
            z-index: 2;
            height: 100%;
            align-items: center;
        }

        .hero-text {
            width: 100%;
            max-width: 800px;
            z-index: 10;
            padding-left: 2rem;
        }

        .hero-text h1 span {
            display: block;
            font-size: 6rem;
            margin-bottom: -15px;
            text-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
            line-height: 1;
        }

        .elevate {
            color: #00ffcc;
            font-size: 6.5rem !important;
        }

        .your {
            color: #30b4ff;
            font-size: 5.8rem !important;
        }

        .fitness {
            color: #5a8eff;
            font-size: 6.2rem !important;
        }

        .journey {
            color: #ff00cc;
            font-size: 7rem !important;
            text-shadow: 0 0 20px rgba(255, 0, 204, 0.8) !important;
        }

        .hero-text p {
            font-size: 1.4rem;
            line-height: 1.7;
            margin: 30px 0 40px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease 0.5s;
        }

        .btn {
            display: inline-block;
            padding: 18px 50px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
            cursor: pointer;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            z-index: -1;
            transition: all 0.5s;
        }

        .btn:hover::before {
            transform: scale(1.1);
        }

        .btn-primary {
            color: #000;
            box-shadow: 0 10px 30px rgba(0, 255, 204, 0.3);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease 0.7s;
        }

        .glove-container {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 60%;
            display: flex;
            justify-content: flex-end;
        }

        .boxing-glove {
            width: 140%;
            max-width: 1000px;
            height: auto;
            filter: drop-shadow(0 0 30px rgba(0, 255, 204, 0.6));
            transform-origin: center center;
        }

        .glow-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: radial-gradient(
                circle at 60% 50%,
                rgba(0, 255, 204, 0.3) 0%,
                rgba(255, 0, 204, 0.3) 50%,
                rgba(0, 0, 0, 0) 80%
            );
            z-index: 1;
            opacity: 0;
        }

        /* Updated animation for the glove */
        @keyframes gloveMove {
            0% { 
                opacity: 0; 
                transform: translateX(200px) rotate(-10deg); /* Starts off-screen right */
            }
            30% { 
                opacity: 1; 
                transform: translateX(50px) rotate(-5deg); /* Moves closer */
            }
            45% { 
                transform: translateX(-20px) rotate(-15deg); /* Slight overshoot left */
            }
            60% { 
                transform: translateX(0) rotate(-5deg); /* Returns to center */
            }
            100% { 
                transform: translateX(20px) rotate(0deg); /* Final position: shifted right */
            }
        }

        /* Updated text shake effect */
        @keyframes textShake {
            0% { transform: translateX(0); opacity: 1; }
            40% { transform: translateX(0); opacity: 1; }
            45% { transform: translateX(-20px) rotate(-1deg); opacity: 1; }
            50% { transform: translateX(15px) rotate(1deg); opacity: 1; }
            55% { transform: translateX(-10px) rotate(-0.5deg); opacity: 1; }
            60% { transform: translateX(5px) rotate(0.5deg); opacity: 1; }
            65% { transform: translateX(-3px); opacity: 1; }
            70% { transform: translateX(0); opacity: 1; }
        }

        @keyframes glowPulse {
            0% { opacity: 0; }
            40% { opacity: 0; }
            45% { opacity: 0.8; }
            65% { opacity: 0.2; }
            100% { opacity: 0; }
        }

        @keyframes contentReveal {
            0% { opacity: 0; transform: translateY(20px); }
            60% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .animated .hero-text h1 {
            opacity: 1;
            animation: textShake 3.5s forwards;
        }

        .animated .hero-text p {
            animation: contentReveal 4s forwards;
        }

        .animated .hero-text .btn {
            animation: contentReveal 4.5s forwards;
        }

        .animated .boxing-glove {
            animation: gloveMove 4s forwards cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .animated .glow-effect {
            animation: glowPulse 4s forwards;
        }

        /* Featured Products */
        .featured-products {
            padding: 120px 0;
            background: #000;
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #00ffcc, #ff00cc);
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .product-card {
            background: #111;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.5s;
            position: relative;
            transform: translateY(0);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .product-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.3);
        }

        .product-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.1), rgba(255, 0, 204, 0.1));
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .product-card:hover::after {
            opacity: 1;
        }

        .product-image {
            height: 250px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #222;
        }

        .product-image img {
            max-width: 80%;
            max-height: 80%;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .product-info {
            padding: 25px;
        }

        .product-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .product-category {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .product-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .current-price {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .old-price {
            font-size: 1rem;
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.4);
        }

        .product-action {
            display: flex;
            gap: 10px;
        }

        .product-action .btn {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(0, 255, 204, 0.5);
            color: #fff;
        }

        .btn-outline:hover {
            border-color: #00ffcc;
            background: rgba(0, 255, 204, 0.1);
        }

        /* Footer */
        footer {
            background: #000;
            padding: 100px 0 30px;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        footer::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.05), rgba(255, 0, 204, 0.05));
            border-radius: 50%;
            top: -250px;
            right: -250px;
            filter: blur(100px);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 50px;
            margin-bottom: 50px;
        }

        .footer-logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 20px;
            display: inline-block;
        }

        .footer-about p {
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .social-icon:hover {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            transform: translateY(-5px);
        }

        .footer-heading {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, #00ffcc, #ff00cc);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: #00ffcc;
            padding-left: 5px;
        }

        .footer-contact p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-content, .featured-grid, .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            .hero-text h1 span {
                font-size: 4.5rem;
            }
            .elevate { font-size: 5rem !important; }
            .your { font-size: 4.5rem !important; }
            .fitness { font-size: 4.8rem !important; }
            .journey { font-size: 5.5rem !important; }
            .boxing-glove {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .hero-content, .featured-grid, .footer-grid {
                grid-template-columns: 1fr;
            }
            nav ul {
                display: none;
            }
            .hero {
                height: auto;
                padding: 150px 0 100px;
            }
            .hero-text {
                padding-left: 0;
                text-align: center;
                margin-bottom: 40px;
            }
            .hero-text h1 span {
                font-size: 3.5rem;
            }
            .elevate { font-size: 4rem !important; }
            .your { font-size: 3.5rem !important; }
            .fitness { font-size: 3.8rem !important; }
            .journey { font-size: 4.5rem !important; }
            .hero-text p {
                margin-left: auto;
                margin-right: auto;
            }
            .glove-container {
                position: relative;
                top: auto;
                right: auto;
                transform: none;
                width: 100%;
                justify-content: center;
                margin-top: 40px;
            }
            .boxing-glove {
                width: 80%;
                max-width: 500px;
            }
        }

        /* Cookie notice styling */
        .cookie-notice {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(17, 17, 17, 0.95);
            border: 1px solid rgba(0, 255, 204, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 15px 25px;
            border-radius: 10px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .cookie-notice-content {
            flex: 1;
        }

        .cookie-notice-close {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 20px;
        }

        .cookie-notice-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="#" class="logo">FitFusion</a>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="workouts.php">Workouts</a></li>
                        <li><a href="nutrition.php">Nutrition</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </nav>
                <div class="header-icons">
                    <div>🔍</div>
                    <div>👤</div>
                    <div>❤️</div>
                    <div class="cart-icon">
                        🛒
                        <span class="cart-count"><?= $cart_count ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>
                        <span class="elevate">ELEVATE</span>
                        <span class="your">YOUR</span>
                        <span class="fitness">FITNESS</span>
                        <span class="journey">JOURNEY</span>
                    </h1>
                    <p>Discover premium equipment and innovative workout gear designed to push your limits and transform your training experience.</p>
                    <a href="#" class="btn btn-primary">EXPLORE COLLECTION</a>
                </div>
                <div class="hero-image">
                    <div class="glove-container">
                        <img src="images/image1.png" alt="Boxing Glove" class="boxing-glove" onerror="this.onerror=null; this.src='https://via.placeholder.com/1000x800?text=Boxing+Glove';">
                    </div>
                    <div class="glow-effect"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <div class="section-title">
                <h2>TRENDING NOW</h2>
            </div>
            <div class="featured-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge">NEW</span>
                        <img src="images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/300x250?text=<?= htmlspecialchars($product['name']) ?>';">
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($product['category']) ?></div>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <span class="current-price">₹<?= htmlspecialchars($product['price']) ?></span>
                            <?php if (isset($product['old_price']) && $product['old_price']): ?>
                            <span class="old-price">₹<?= htmlspecialchars($product['old_price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-action">
                            <form action="add_to_cart.php" method="POST" style="display: inline; flex: 1;">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Add to Cart</button>
                            </form>
                            <a href="product_details.php?id=<?= $product['id'] ?>" class="btn btn-outline">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">FitFusion</div>
                    <p>We're on a mission to transform fitness through innovative equipment and a supportive community. Join us in revolutionizing how people train, recover, and achieve their goals.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon">📘</a>
                        <a href="#" class="social-icon">📸</a>
                        <a href="#" class="social-icon">📱</a>
                        <a href="#" class="social-icon">📺</a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Shop</h3>
                    <ul>
                        <li><a href="#">Cardio Equipment</a></li>
                        <li><a href="#">Strength Training</a></li>
                        <li><a href="#">Accessories</a></li>
                        <li><a href="#">Wearable Tech</a></li>
                        <li><a href="#">Nutrition</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Support</h3>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Shipping</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Size Guide</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-heading">Contact</h3>
                    <p>📍 123 Fitness Ave, Muscle City</p>
                    <p>📱 +1 (555) 123-4567</p>
                    <p>📧 info@fitfusion.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 FitFusion. All rights reserved. Designed with 💪 for fitness enthusiasts.</p>
            </div>
        </div>
    </footer>

    <!-- Cookie Notice -->
    <div class="cookie-notice" id="cookieNotice">
        <div class="cookie-notice-content">
            <p>This website uses cookies to enhance your experience. By continuing to browse, you agree to our <a href="privacy.php">Privacy Policy</a>.</p>
        </div>
        <button class="cookie-notice-close" onclick="closeCookieNotice()">Accept</button>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Trigger animation with slight delay
        setTimeout(() => {
            document.querySelector('.hero-container').classList.add('animated');
        }, 500);
        
        // Optional: Loop the animation every 10 seconds for continuous effect
        setInterval(() => {
            document.querySelector('.hero-container').classList.remove('animated');
            setTimeout(() => {
                document.querySelector('.hero-container').classList.add('animated');
            }, 300);
        }, 10000);
    });

    // Function to close cookie notice
    function closeCookieNotice() {
        document.getElementById('cookieNotice').style.display = 'none';
        localStorage.setItem('cookieAccepted', 'true');
    }
    </script>
</body>
</html>
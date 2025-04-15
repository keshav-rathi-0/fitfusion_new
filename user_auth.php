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
    $cookie_value = $_COOKIE[$cookie_name];
}

// Delete cookie (example)
if (isset($_GET['delete_cookie'])) {
    setcookie($cookie_name, "", time() - 3600, "/");
    header("Location: index.php");
    exit;
}

// Wishlist functionality
if (isset($_GET['add_to_wishlist'])) {
    $product_id = $_GET['add_to_wishlist'];
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    if (!in_array($product_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $product_id;
    }
    header("Location: index.php");
    exit;
}

// Search functionality
$search_results = [];
if (isset($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $stmt = $pdo->prepare('SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR description LIKE ?');
    $stmt->execute([$search_term, $search_term, $search_term]);
    $search_results = $stmt->fetchAll();
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
    <!-- Add Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            background: #000;
            color: #fff;
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
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            z-index: 1000;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        header.scrolled {
            padding: 10px 0;
            box-shadow: 0 5px 20px rgba(0, 255, 204, 0.1);
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logo:hover {
            transform: scale(1.05);
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
            background: linear-gradient(90deg, #00ffcc, #ff00cc);
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

        .header-icon {
            color: #fff;
            font-size: 1.2rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .header-icon:hover {
            color: #00ffcc;
            transform: translateY(-3px);
        }

        .cart-icon, .wishlist-icon {
            position: relative;
        }

        .cart-count, .wishlist-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff00cc;
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

        .wishlist-count {
            background: #30b4ff;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            width: 200px;
            transition: all 0.3s;
        }

        .search-container.expanded {
            width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 8px 15px;
            border-radius: 30px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
            transition: all 0.3s;
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 10px rgba(0, 255, 204, 0.3);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(17, 17, 17, 0.95);
            border: 1px solid rgba(0, 255, 204, 0.3);
            border-radius: 10px;
            padding: 10px;
            margin-top: 5px;
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .search-result-item {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .search-result-item:hover {
            background: rgba(0, 255, 204, 0.1);
        }

        .search-result-item:last-child {
            border-bottom: none;
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
            background: rgba(0, 255, 204, 0.1);
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
            background: rgba(255, 0, 204, 0.1);
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
            border: none;
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
                transform: translateX(200px) rotate(-10deg);
            }
            30% { 
                opacity: 1; 
                transform: translateX(50px) rotate(-5deg);
            }
            45% { 
                transform: translateX(-20px) rotate(-15deg);
            }
            60% { 
                transform: translateX(0) rotate(-5deg);
            }
            100% { 
                transform: translateX(20px) rotate(0deg);
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
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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
            object-fit: contain;
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

        .wishlist-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 2;
        }

        .wishlist-btn:hover {
            background: rgba(255, 0, 204, 0.7);
            color: #fff;
        }

        .wishlist-btn.active {
            background: rgba(255, 0, 204, 0.9);
            color: #fff;
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

        /* Newsletter Section */
        .newsletter {
            padding: 80px 0;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.1), rgba(255, 0, 204, 0.1));
            position: relative;
            overflow: hidden;
        }

        .newsletter::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(0, 255, 204, 0.05);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            filter: blur(80px);
        }

        .newsletter-container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .newsletter h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .newsletter p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .newsletter-form {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
        }

        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border-radius: 50px 0 0 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            outline: none;
            font-size: 1rem;
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .newsletter-btn {
            padding: 15px 30px;
            border-radius: 0 50px 50px 0;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .newsletter-btn:hover {
            transform: translateX(5px);
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
            color: #fff;
            text-decoration: none;
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

        /* Quick View Modal */
        .quick-view-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .quick-view-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .quick-view-content {
            background: #111;
            border-radius: 20px;
            width: 90%;
            max-width: 1000px;
            padding: 30px;
            position: relative;
            border: 1px solid rgba(0, 255, 204, 0.3);
            box-shadow: 0 0 30px rgba(0, 255, 204, 0.2);
        }

        .close-quick-view {
            position: absolute;
            top: 20px;
            right: 20px;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-quick-view:hover {
            color: #ff00cc;
            transform: rotate(90deg);
        }

        .quick-view-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .quick-view-image {
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #222;
            border-radius: 15px;
            overflow: hidden;
        }

        .quick-view-image img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .quick-view-info h3 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .quick-view-price {
            font-size: 1.8rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .quick-view-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .quick-view-actions {
            display: flex;
            gap: 15px;
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
            transform: translateY(150%);
            transition: transform 0.5s ease-out;
        }

        .cookie-notice.show {
            transform: translateY(0);
        }

        .cookie-notice-content {
            flex: 1;
            color: rgba(255, 255, 255, 0.8);
        }

        .cookie-notice-content a {
            color: #00ffcc;
            text-decoration: none;
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

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .hero-text h1 span {
                font-size: 5rem;
            }
            .elevate { font-size: 5.5rem !important; }
            .your { font-size: 4.8rem !important; }
            .fitness { font-size: 5.2rem !important; }
            .journey { font-size: 6rem !important; }
        }

        @media (max-width: 992px) {
            .hero-content, .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            .featured-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
            .quick-view-grid {
                grid-template-columns: 1fr;
            }
            .quick-view-image {
                height: 300px;
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
            .newsletter-form {
                flex-direction: column;
            }
            .newsletter-input {
                border-radius: 50px;
                margin-bottom: 10px;
            }
            .newsletter-btn {
                border-radius: 50px;
            }
        }

        @media (max-width: 576px) {
            .header-icons {
                gap: 10px;
            }
            .search-container {
                width: 150px;
            }
            .search-container.expanded {
                width: 200px;
            }
            .hero-text h1 span {
                font-size: 2.5rem;
            }
            .elevate { font-size: 3rem !important; }
            .your { font-size: 2.5rem !important; }
            .fitness { font-size: 2.8rem !important; }
            .journey { font-size: 3.5rem !important; }
            .hero-text p {
                font-size: 1.1rem;
            }
            .product-action {
                flex-direction: column;
            }
            .quick-view-content {
                padding: 20px;
            }
            .quick-view-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="mainHeader">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">FitFusion</a>
                <nav>
                    <ul>
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="workouts.php">Workouts</a></li>
                        <li><a href="nutrition.php">Nutrition</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </nav>
                <div class="header-icons">
                    <div class="search-container">
                        <form action="index.php" method="GET">
                            <input type="text" name="search" class="search-input" placeholder="Search..." autocomplete="off">
                        </form>
                        <?php if (!empty($search_results)): ?>
                        <div class="search-results">
                            <?php foreach ($search_results as $product): ?>
                            <a href="product_details.php?id=<?= $product['id'] ?>" class="search-result-item">
                                <?= htmlspecialchars($product['name']) ?> - ₹<?= htmlspecialchars($product['price']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="header-icon"><i class="fas fa-user"></i></div>
                    <div class="header-icon wishlist-icon">
                        <i class="fas fa-heart"></i>
                        <span class="wishlist-count"><?= isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0 ?></span>
                    </div>
                    <div class="header-icon cart-icon">
                        <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
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
                    <a href="shop.php" class="btn btn-primary">EXPLORE COLLECTION</a>
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
                <div class="product-card" data-id="<?= $product['id'] ?>">
                    <div class="product-image">
                        <span class="product-badge">NEW</span>
                        <button class="wishlist-btn <?= (isset($_SESSION['wishlist']) && in_array($product['id'], $_SESSION['wishlist'])) ? 'active' : '' ?>" 
                                onclick="window.location.href='index.php?add_to_wishlist=<?= $product['id'] ?>'">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/300x250?text=<?= htmlspecialchars($product['name']) ?>';">
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

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-container">
                <h3>JOIN OUR FITNESS COMMUNITY</h3>
                <p>Subscribe to our newsletter for exclusive offers, workout tips, and the latest fitness trends.</p>
                <form class="newsletter-form" action="subscribe.php" method="POST">
                    <input type="email" name="email" class="newsletter-input" placeholder="Your email address" required>
                    <button type="submit" class="newsletter-btn">SUBSCRIBE</button>
                </form>
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
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Shop</h3>
                    <ul>
                        <li><a href="shop.php?category=cardio">Cardio Equipment</a></li>
                        <li><a href="shop.php?category=strength">Strength Training</a></li>
                        <li><a href="shop.php?category=accessories">Accessories</a></li>
                        <li><a href="shop.php?category=tech">Wearable Tech</a></li>
                        <li><a href="shop.php?category=nutrition">Nutrition</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-heading">Support</h3>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                        <li><a href="shipping.php">Shipping</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="size_guide.php">Size Guide</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-heading">Contact</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Fitness Ave, Muscle City</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@fitfusion.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 FitFusion. All rights reserved. Designed with <i class="fas fa-heart" style="color: #ff00cc;"></i> for fitness enthusiasts.</p>
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

    <!-- Back to Top Button -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Trigger animation with slight delay
        setTimeout(() => {
            document.querySelector('.hero-container').classList.add('animated');
        }, 500);
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Show cookie notice if not accepted
        if (!localStorage.getItem('cookieAccepted')) {
            setTimeout(() => {
                document.getElementById('cookieNotice').classList.add('show');
            }, 2000);
        }

        // Back to top button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });

    // Function to close cookie notice
    function closeCookieNotice() {
        document.getElementById('cookieNotice').classList.remove('show');
        localStorage.setItem('cookieAccepted', 'true');
    }
    </script>
</body>
</html>
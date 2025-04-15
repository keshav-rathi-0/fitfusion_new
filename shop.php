<?php
// shop.php
require 'config.php';

// Fetch products from the database
$stmt = $pdo->query('SELECT * FROM products');
$products = $stmt->fetchAll();

// Initialize cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Filter categories
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

// Get all categories
$cat_stmt = $pdo->query('SELECT DISTINCT category FROM products');
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// If filter is applied, filter products
if ($category_filter) {
    $filtered_stmt = $pdo->prepare('SELECT * FROM products WHERE category = ?');
    $filtered_stmt->execute([$category_filter]);
    $products = $filtered_stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - FitFusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            max-width: 1400px;
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

        /* Shop Hero */
        .shop-hero {
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/shop-hero.jpg');
            background-size: cover;
            background-position: center;
            margin-top: 70px;
            position: relative;
        }

        .shop-hero::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(0, 255, 204, 0.1); /* Neon glow */
            top: -50px;
            right: -50px;
            filter: blur(80px);
            z-index: 1;
        }

        .shop-hero::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 0, 204, 0.1); /* Neon glow */
            bottom: -50px;
            left: -50px;
            filter: blur(60px);
            z-index: 1;
        }

        .shop-hero h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            z-index: 2;
            position: relative;
        }

        .shop-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.8);
            z-index: 2;
            position: relative;
        }

        /* Shop Content */
        .shop-content {
            padding: 80px 0;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
        }

        .filter-categories {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-category {
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .filter-category:hover, 
        .filter-category.active {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
        }

        .filter-sort select {
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 30px;
            color: #fff;
            outline: none;
            cursor: pointer;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .product-card {
            background: #111; /* Dark gray background */
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.5s;
            position: relative;
            transform: translateY(0);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); /* Strong shadow */
        }

        .product-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.3); /* Neon shadow */
        }

        .product-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.1), rgba(255, 0, 204, 0.1)); /* Neon overlay */
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
            background: #222; /* Darker gray */
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
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            color: #000; /* Black text */
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
            color: #fff; /* White text */
        }

        .product-category {
            color: rgba(255, 255, 255, 0.6); /* Light gray text */
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
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .old-price {
            font-size: 1rem;
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.4); /* Light gray */
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

        .btn {
            display: inline-block;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            z-index: -1;
            transition: all 0.5s;
        }

        .btn:hover::before {
            transform: scale(1.1);
        }

        .btn-primary {
            color: #000; /* Black text */
            box-shadow: 0 10px 30px rgba(0, 255, 204, 0.3); /* Neon shadow */
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(0, 255, 204, 0.5); /* Neon border */
            color: #fff; /* White text */
        }

        .btn-outline:hover {
            border-color: #00ffcc; /* Neon border */
            background: rgba(0, 255, 204, 0.1); /* Neon overlay */
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 60px;
            gap: 10px;
        }

        .page-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-item:hover,
        .page-item.active {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
        }

        /* Footer */
        footer {
            background: #000; /* Black background */
            padding: 100px 0 30px;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.1); /* Subtle border */
        }

        footer::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.05), rgba(255, 0, 204, 0.05)); /* Neon glow */
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
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 20px;
            display: inline-block;
        }

        .footer-about p {
            color: rgba(255, 255, 255, 0.6); /* Light gray text */
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
            background: rgba(255, 255, 255, 0.1); /* Light overlay */
            transition: all 0.3s;
        }

        .social-icon:hover {
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
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
            background: linear-gradient(90deg, #00ffcc, #ff00cc); /* Neon gradient */
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6); /* Light gray text */
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: #00ffcc; /* Neon accent */
            padding-left: 5px;
        }

        .footer-contact p {
            color: rgba(255, 255, 255, 0.6); /* Light gray text */
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1); /* Subtle border */
            padding-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.4); /* Light gray text */
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            .products-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            nav ul {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="logo">FitFusion</a>
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

    <!-- Shop Hero -->
    <section class="shop-hero">
        <h1>FITNESS GEAR SHOP</h1>
        <p>Discover premium equipment engineered for peak performance</p>
    </section>

    <!-- Shop Content -->
    <section class="shop-content">
        <div class="container">
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-categories">
                    <a href="shop.php" class="filter-category <?= !$category_filter ? 'active' : '' ?>">All</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="shop.php?category=<?= urlencode($category) ?>" 
                           class="filter-category <?= $category_filter === $category ? 'active' : '' ?>">
                            <?= htmlspecialchars($category) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="filter-sort">
                    <select name="sort" id="sort">
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge">NEW</span>
                        <img src="images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
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
                            <a href="product_details.php?id=<?= $product['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-item active">1</div>
                <div class="page-item">2</div>
                <div class="page-item">3</div>
                <div class="page-item">></div>
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
                    <p><i class="fas fa-map-marker-alt"></i> 123 Fitness Ave, Muscle City</p>
                    <p><i class="fas fa-phone-alt"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@fitfusion.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 FitFusion. All rights reserved. Designed with 💪 for fitness enthusiasts.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sort functionality
        document.getElementById('sort').addEventListener('change', function() {
            // Handle sorting functionality here
            console.log('Sort by: ' + this.value);
            // You would typically reload the page with a sort parameter here
        });
    </script>
</body>
</html>
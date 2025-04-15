<?php
// product_details.php

require 'config.php';

$product_id = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die('Product not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - FitFusion</title>
    <style>
        /* Your existing CSS styles */
    </style>
</head>
<body>
    <header>
        <!-- Header content (same as index.php) -->
    </header>

    <main>
        <div class="container">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p>Price: ₹<?= htmlspecialchars($product['price']) ?></p>
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn btn-primary">Add to Cart</button>
            </form>
        </div>
    </main>

    <footer>
        <!-- Footer content (same as index.php) -->
    </footer>
</body>
</html>
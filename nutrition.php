<?php
// nutrition.php - Complete nutrition solution in one file
require 'config.php';

// Initialize session and cart count
session_start();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Database Functions
function getNutritionProducts($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE category = 'Nutrition' LIMIT 6");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function getNutritionPlans($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM nutrition_plans LIMIT 3");
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($plans)) {
            return [
                [
                    'id' => 1,
                    'title' => 'Muscle Building Meal Plan',
                    'description' => 'High protein diet designed for maximum muscle growth',
                    'image' => 'images/muscle_gain.webp',
                    'duration' => '4 weeks',
                    'difficulty' => 'Intermediate'
                ],
                [
                    'id' => 2,
                    'title' => 'Weight Loss Nutrition',
                    'description' => 'Calorie-controlled plan for sustainable fat loss',
                    'image' => 'images/weight_loss.jpg',
                    'duration' => '8 weeks',
                    'difficulty' => 'Beginner'
                ],
                [
                    'id' => 3,
                    'title' => 'Athletic Performance',
                    'description' => 'Optimized nutrition timing for peak athletic performance',
                    'image' => 'images/athletes.jpg',
                    'duration' => '6 weeks',
                    'difficulty' => 'Advanced'
                ]
            ];
        }
        return $plans;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function getNutritionTips() {
    return [
        [
            'title' => 'Hydration is Key',
            'content' => 'Drink at least 3-4 liters of water daily to optimize metabolism and recovery.',
            'icon' => 'fa-tint'
        ],
        [
            'title' => 'Protein Timing',
            'content' => 'Consume 20-40g of protein every 3-4 hours to maximize muscle protein synthesis.',
            'icon' => 'fa-dumbbell'
        ],
        [
            'title' => 'Pre-Workout Fuel',
            'content' => 'Eat a balanced meal with carbs and protein 1-2 hours before training for energy.',
            'icon' => 'fa-bolt'
        ]
    ];
}

// Macro Calculator Function
function calculateMacros($weight, $height, $age, $gender, $activity, $goal) {
    // Validate inputs
    if ($weight <= 0 || $height <= 0 || $age <= 0) {
        return ['error' => 'Invalid input values'];
    }
    
    // Calculate BMR
    if (strtolower($gender) === 'male') {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }
    
    // Activity multipliers
    $activityMultipliers = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9
    ];
    
    $activityLevel = $activityMultipliers[$activity] ?? 1.2;
    $tdee = $bmr * $activityLevel;
    
    // Adjust calories based on goal
    switch (strtolower($goal)) {
        case 'lose': $calories = $tdee * 0.85; break;
        case 'lose_aggressive': $calories = $tdee * 0.75; break;
        case 'gain': $calories = $tdee * 1.10; break;
        case 'gain_aggressive': $calories = $tdee * 1.20; break;
        default: $calories = $tdee;
    }
    
    // Calculate macros
    switch (strtolower($goal)) {
        case 'lose':
        case 'lose_aggressive':
            $protein = $weight * 2.2;
            $fats = ($calories * 0.25) / 9;
            $carbs = ($calories - ($protein * 4) - ($fats * 9)) / 4;
            break;
        case 'gain':
        case 'gain_aggressive':
            $protein = $weight * 1.8;
            $fats = ($calories * 0.25) / 9;
            $carbs = ($calories - ($protein * 4) - ($fats * 9)) / 4;
            break;
        default: // maintain
            $protein = $weight * 1.8;
            $fats = ($calories * 0.3) / 9;
            $carbs = ($calories - ($protein * 4) - ($fats * 9)) / 4;
    }
    
    return [
        'calories' => round($calories),
        'protein' => round($protein),
        'carbs' => round($carbs),
        'fats' => round($fats),
        'tdee' => round($tdee),
        'goal' => $goal  
    ];
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_product':
            if (isset($_GET['id'])) {
                $productId = (int)$_GET['id'];
                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($product) {
                        echo json_encode([
                            'success' => true,
                            'html' => renderProductDetails($product)
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Product not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
            }
            exit;
            
        case 'get_plan':
            if (isset($_GET['id'])) {
                $planId = (int)$_GET['id'];
                try {
                    $stmt = $pdo->prepare("SELECT * FROM nutrition_plans WHERE id = ?");
                    $stmt->execute([$planId]);
                    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($plan) {
                        echo json_encode([
                            'success' => true,
                            'html' => renderPlanDetails($plan)
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Plan not found']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
            }
            exit;
            
        case 'add_to_cart':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login']);
                exit;
            }
            
            if (isset($_POST['product_id'])) {
                $productId = (int)$_POST['product_id'];
                
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
                
                echo json_encode([
                    'success' => true,
                    'cart_count' => count($_SESSION['cart'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No product specified']);
            }
            exit;
    }
}

// Helper functions for rendering
function renderProductDetails($product) {
    ob_start(); ?>
    <div class="row">
        <div class="col-md-6">
            <img src="images/<?= htmlspecialchars($product['image_url'] ?? 'default-product.jpg') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <div class="mb-3"><span class="badge bg-primary"><?= htmlspecialchars($product['category']) ?></span></div>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <div class="d-flex align-items-center mb-3">
                <h4 class="mb-0">₹<?= htmlspecialchars($product['price']) ?></h4>
                <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                    <small class="text-muted ms-2"><del>₹<?= htmlspecialchars($product['old_price']) ?></del></small>
                <?php endif; ?>
            </div>
            <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                <p class="text-success"><i class="fas fa-check-circle"></i> In Stock (<?= $product['stock'] ?> available)</p>
            <?php else: ?>
                <p class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</p>
            <?php endif; ?>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderPlanDetails($plan) {
    ob_start(); ?>
    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($plan['image'] ?? 'images/default-plan.jpg') ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($plan['title']) ?>">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($plan['title']) ?></h2>
            <div class="d-flex gap-3 mb-3">
                <span class="badge bg-primary"><i class="fas fa-calendar-alt me-1"></i> <?= htmlspecialchars($plan['duration']) ?></span>
                <span class="badge bg-secondary"><i class="fas fa-bolt me-1"></i> <?= htmlspecialchars($plan['difficulty']) ?></span>
            </div>
            <p><?= htmlspecialchars($plan['description']) ?></p>
            <div class="mt-4">
                <h5>Plan Details</h5>
                <div class="plan-content"><?= htmlspecialchars($plan['content'] ?? 'Detailed plan content goes here...') ?></div>
            </div>
            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
        </div>
    </div>
    <?php return ob_get_clean();
}

// Process macro calculator form if submitted
$macroResults = null;
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate_macros'])) {
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $activity = filter_input(INPUT_POST, 'activity', FILTER_SANITIZE_STRING);
    $goal = filter_input(INPUT_POST, 'goal', FILTER_SANITIZE_STRING);
    
    // Validate inputs
    if (!$weight || $weight <= 0) $formErrors['weight'] = 'Please enter a valid weight';
    if (!$height || $height <= 0) $formErrors['height'] = 'Please enter a valid height';
    if (!$age || $age <= 0) $formErrors['age'] = 'Please enter a valid age';
    if (!$gender || !in_array(strtolower($gender), ['male', 'female'])) $formErrors['gender'] = 'Please select a valid gender';
    if (!$activity) $formErrors['activity'] = 'Please select an activity level';
    if (!$goal) $formErrors['goal'] = 'Please select a goal';
    
    if (empty($formErrors)) {
        $macroResults = calculateMacros($weight, $height, $age, $gender, $activity, $goal);
    }
}

// Get data
$nutritionProducts = getNutritionProducts($pdo);
$nutritionPlans = getNutritionPlans($pdo);
$nutritionTips = getNutritionTips();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition - FitFusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .form-select {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
    padding: 12px 15px !important;
    border-radius: 10px !important;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2300ffcc' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
}

.form-select:focus {
    outline: none;
    border-color: #00ffcc;
    box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
}

.floating-label {
    position: relative;
    margin-bottom: 20px;
}

.floating-label select {
    width: 100%;
}

.floating-label label {
    position: absolute;
    top: -10px;
    left: 15px;
    background: #111;
    padding: 0 5px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    z-index: 1;
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

        /* Nutrition Hero */
        .nutrition-hero {
            height: 80vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
           background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.7)), url('https://via.placeholder.com/1200x800');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .nutrition-hero::before {
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

        .nutrition-hero::after {
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

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-text h1 {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-text p {
            font-size: 1.2rem;
            line-height: 1.7;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.8); /* Light gray text */
        }

        /* Nutrition Section Styles */
        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
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
            background: linear-gradient(90deg, #00ffcc, #ff00cc); /* Neon gradient */
        }

        .section-title p {
            max-width: 600px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Nutrition Plans Section */
        .nutrition-plans {
            padding: 120px 0 80px;
            position: relative;
        }

        .plan-card {
            background: #111;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.5s;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.2);
        }

        .plan-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.05), rgba(255, 0, 204, 0.05));
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .plan-card:hover::after {
            opacity: 1;
        }

        .plan-image {
            height: 200px;
            overflow: hidden;
        }

        .plan-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .plan-card:hover .plan-image img {
            transform: scale(1.1);
        }

        .plan-details {
            padding: 25px;
        }

        .plan-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .plan-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .plan-meta span {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .plan-description {
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
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

        /* Macro Calculator Section */
        .macro-calculator {
            padding: 80px 0;
            background: #111;
            position: relative;
            overflow: hidden;
        }

        .macro-calculator::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(0, 255, 204, 0.05);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            filter: blur(100px);
        }

        .macro-calculator::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 0, 204, 0.05);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            filter: blur(100px);
        }

        .calculator-form {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #00ffcc;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            transition: all 0.3s;
        }

        .form-select:focus {
            outline: none;
            border-color: #00ffcc;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
        }

        /* Macro Results Section */
        .macro-results {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .result-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }

        .result-circle::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(0, 255, 204, 0.2), rgba(255, 0, 204, 0.2));
            z-index: -1;
        }

        .result-circle h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .result-circle span {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .macro-details {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            text-align: center;
        }

        .macro-item {
            flex: 1;
            padding: 15px;
        }

        .macro-item h4 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .macro-item span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .protein-color {
            color: #00ffcc;
        }

        .carbs-color {
            color: #ff00cc;
        }

        .fats-color {
            color: #ffcc00;
        }

        /* Products Section */
        .nutrition-products {
            padding: 100px 0;
            position: relative;
        }

        .product-grid {
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .product-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.3);
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

        /* Blog Section */
        .nutrition-blog {
            padding: 80px 0;
            position: relative;
        }

        .blog-card {
            background: #111;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.5s;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.2);
        }

        .blog-image {
            height: 200px;
            overflow: hidden;
        }

        .blog-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .blog-card:hover .blog-image img {
            transform: scale(1.1);
        }

        .blog-content {
            padding: 25px;
        }

        .blog-date {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
            display: block;
        }

        .blog-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .blog-excerpt {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
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

        /* Nutrition Tips */
        .nutrition-tips {
            padding: 80px 0;
        }

        .tips-slider {
            padding: 40px 0;
            position: relative;
        }

        .tip-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            height: 400px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            margin: 0 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.5s;
        }

        .tip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #00ffcc, #ff00cc);
        }

        .tip-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.2);
        }

        .tip-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .tip-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.tip-content {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.7;
    flex-grow: 1;
}

.tip-link {
    margin-top: 20px;
    display: inline-block;
    color: #00ffcc;
    font-weight: 600;
    position: relative;
    transition: all 0.3s;
}

.tip-link:hover {
    color: #ff00cc;
}

.tip-link::after {
    content: '→';
    margin-left: 5px;
    transition: all 0.3s;
}

.tip-link:hover::after {
    margin-left: 10px;
}

/* Responsive Styles */
@media (max-width: 1200px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .hero-text h1 {
        font-size: 3rem;
    }
    
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    .result-circle {
        width: 120px;
        height: 120px;
    }
    
    .result-circle h3 {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .nutrition-hero {
        height: 70vh;
    }
    
    .section-title h2 {
        font-size: 2.5rem;
    }
    
    .product-grid {
        grid-template-columns: 1fr;
    }
    
    nav ul {
        gap: 20px;
    }
    
    .macro-details {
        flex-direction: column;
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .hero-text h1 {
        font-size: 2rem;
    }
    
    .section-title h2 {
        font-size: 2rem;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
    }
    
    .calculator-form {
        padding: 20px;
    }
    
    nav ul {
        display: none;
    }
}

/* Mobile Menu */
.mobile-menu-toggle {
    display: none;
    cursor: pointer;
    font-size: 24px;
    color: #fff;
}

@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block;
    }
    
    nav ul {
        display: none;
        position: absolute;
        top: 80px;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.95);
        flex-direction: column;
        gap: 0;
        padding: 20px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    nav ul.active {
        display: flex;
    }
    
    nav ul li {
        width: 100%;
    }
    
    nav ul li a {
        display: block;
        padding: 15px 30px;
    }
}

/* Animations */
.animate-fade-up {
    animation: fadeUp 1s ease forwards;
}

.animate-fade-in {
    animation: fadeIn 1s ease forwards;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Delay classes */
.delay-1 {
    animation-delay: 0.2s;
}

.delay-2 {
    animation-delay: 0.4s;
}

.delay-3 {
    animation-delay: 0.6s;
}

.delay-4 {
    animation-delay: 0.8s;
}

/* Additional hover effects */
.hover-glow:hover {
    box-shadow: 0 0 20px rgba(0, 255, 204, 0.5);
}
.progress-ring {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        
        .progress-ring__circle {
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        
        .progress-ring__circle--track {
            stroke: rgba(255, 255, 255, 0.1);
        }
        
        .progress-ring__circle--protein {
            stroke: #00ffcc;
            stroke-dasharray: 0 100;
            animation: protein-fill 1s forwards;
        }
        
        .progress-ring__circle--carbs {
            stroke: #ff00cc;
            stroke-dasharray: 0 100;
            animation: carbs-fill 1s forwards;
        }
        
        .progress-ring__circle--fats {
            stroke: #ffcc00;
            stroke-dasharray: 0 100;
            animation: fats-fill 1s forwards;
        }
        
        @keyframes protein-fill {
            to { stroke-dasharray: <?= isset($macroResults['protein']) ? ($macroResults['protein'] / 300 * 100) : 0 ?> 100; }
        }
        
        @keyframes carbs-fill {
            to { stroke-dasharray: <?= isset($macroResults['carbs']) ? ($macroResults['carbs'] / 400 * 100) : 0 ?> 100; }
        }
        
        @keyframes fats-fill {
            to { stroke-dasharray: <?= isset($macroResults['fats']) ? ($macroResults['fats'] / 150 * 100) : 0 ?> 100; }
        }
        
        .tooltip-inner {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before,
        .bs-tooltip-top .tooltip-arrow::before {
            border-top-color: rgba(255, 255, 255, 0.2);
        }
        
        .modal-content {
            background: #111;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label input,
        .floating-label select {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            padding: 15px 20px !important;
            border-radius: 10px !important;
        }
        
        .floating-label label {
            position: absolute;
            top: -10px;
            left: 15px;
            background: #111;
            padding: 0 5px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-check-input:checked {
            background-color: #00ffcc;
            border-color: #00ffcc;
        }
        
        .form-check-label {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .error-message {
            color: #ff3366;
            font-size: 0.8rem;
            margin-top: 5px;
        }
<!-- After the closing </style> tag -->
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
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?= $cart_count ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Nutrition Hero Section -->
    <section class="nutrition-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="animate__animated animate__fadeInDown">OPTIMIZE YOUR NUTRITION</h1>
                    <p class="animate__animated animate__fadeInUp delay-1">Discover meal plans, supplements, and expert advice to fuel your fitness journey and maximize your results.</p>
                    <a href="#macro-calculator" class="btn btn-primary animate__animated animate__fadeInUp delay-2">Calculate Your Macros</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Nutrition Plans Section -->
    <section class="nutrition-plans">
        <div class="container">
            <div class="section-title">
                <h2>MEAL PLANS</h2>
                <p>Customized nutrition plans designed for your specific fitness goals</p>
            </div>
            <div class="row">
                <?php foreach ($nutritionPlans as $plan): ?>
                <div class="col-md-4 animate__animated animate__fadeInUp">
                    <div class="plan-card">
                        <div class="plan-image">
                            <img src="<?= $plan['image'] ?? 'images/default-plan.jpg' ?>" alt="<?= htmlspecialchars($plan['title']) ?>">
                        </div>
                        <div class="plan-details">
                            <h3 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h3>
                            <div class="plan-meta">
                                <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($plan['duration']) ?></span>
                                <span><i class="fas fa-bolt"></i> <?= htmlspecialchars($plan['difficulty']) ?></span>
                            </div>
                            <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                            <button class="btn btn-outline view-plan-btn" data-plan-id="<?= $plan['id'] ?>">View Plan</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Macro Calculator Section -->
<section class="macro-calculator" id="macro-calculator">
    <div class="container">
        <div class="section-title">
            <h2>MACRO CALCULATOR</h2>
            <p>Calculate your ideal macronutrient intake based on your goals</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="calculator-form">
                    <form method="POST">
                        <div class="row g-3">
                            <!-- Personal Info -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" name="weight" required min="30" max="200" value="<?= $_POST['weight'] ?? '' ?>">
                                    <?php if (isset($formErrors['weight'])): ?>
                                        <div class="error-message"><?= $formErrors['weight'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Height (cm)</label>
                                    <input type="number" class="form-control" name="height" required min="100" max="250" value="<?= $_POST['height'] ?? '' ?>">
                                    <?php if (isset($formErrors['height'])): ?>
                                        <div class="error-message"><?= $formErrors['height'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="age" required min="12" max="120" value="<?= $_POST['age'] ?? '' ?>">
                                    <?php if (isset($formErrors['age'])): ?>
                                        <div class="error-message"><?= $formErrors['age'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                    <?php if (isset($formErrors['gender'])): ?>
                                        <div class="error-message"><?= $formErrors['gender'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-12">
    <h5 class="mt-3 mb-2" style="color: #00ffcc;">Activity Level</h5>
    <div class="floating-label">
        <select class="form-select" id="activity" name="activity" required>
            <option value="">Select activity level</option>
            <option value="sedentary" <?= (isset($_POST['activity']) && $_POST['activity'] === 'sedentary') ? 'selected' : '' ?>>Sedentary (Little or no exercise)</option>
            <option value="light" <?= (isset($_POST['activity']) && $_POST['activity'] === 'light') ? 'selected' : '' ?>>Lightly Active (Light exercise 1-3 days/week)</option>
            <option value="moderate" <?= (isset($_POST['activity']) && $_POST['activity'] === 'moderate') ? 'selected' : '' ?>>Moderately Active (Moderate exercise 3-5 days/week)</option>
            <option value="active" <?= (isset($_POST['activity']) && $_POST['activity'] === 'active') ? 'selected' : '' ?>>Very Active (Hard exercise 6-7 days/week)</option>
            <option value="very_active" <?= (isset($_POST['activity']) && $_POST['activity'] === 'very_active') ? 'selected' : '' ?>>Extremely Active (Very hard exercise & physical job)</option>
        </select>
        <label>Activity Level</label>
        <?php if (isset($formErrors['activity'])): ?>
            <div class="error-message"><?= $formErrors['activity'] ?></div>
        <?php endif; ?>
    </div>
</div>

                            
<!-- Goals -->
<div class="col-12">
    <h5 class="mt-3 mb-2" style="color: #00ffcc;">Your Goal</h5>
    <div class="floating-label">
        <select class="form-select" id="goal" name="goal" required>
            <option value="">Select your goal</option>
            <option value="lose_aggressive" <?= (isset($_POST['goal']) && $_POST['goal'] === 'lose_aggressive') ? 'selected' : '' ?>>Lose Weight Fast (Aggressive fat loss - 20% deficit)</option>
            <option value="lose" <?= (isset($_POST['goal']) && $_POST['goal'] === 'lose') ? 'selected' : '' ?>>Lose Weight (Steady fat loss - 15% deficit)</option>
            <option value="maintain" <?= (isset($_POST['goal']) && $_POST['goal'] === 'maintain') ? 'selected' : '' ?>>Maintain Weight (Stay at current weight)</option>
            <option value="gain" <?= (isset($_POST['goal']) && $_POST['goal'] === 'gain') ? 'selected' : '' ?>>Gain Muscle (Lean muscle growth - 10% surplus)</option>
            <option value="gain_aggressive" <?= (isset($_POST['goal']) && $_POST['goal'] === 'gain_aggressive') ? 'selected' : '' ?>>Gain Muscle Fast (Maximum muscle growth - 20% surplus)</option>
        </select>
        <label>Goal</label>
        <?php if (isset($formErrors['goal'])): ?>
            <div class="error-message"><?= $formErrors['goal'] ?></div>
        <?php endif; ?>
    </div>
</div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" name="calculate_macros" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calculator me-2"></i> Calculate
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($macroResults && !isset($macroResults['error'])): ?>
                    <div class="macro-results mt-5 animate__animated animate__fadeIn">
                        <div class="text-center mb-4">
                            <h3>Your Macros</h3>
                            <p class="text-muted">Daily targets based on your inputs</p>
                        </div>
                        
                        <div class="result-circle">
                            <h3><?= $macroResults['calories'] ?></h3>
                            <span>CALORIES/DAY</span>
                            <small class="text-muted">TDEE: <?= $macroResults['tdee'] ?></small>
                        </div>
                        
                        <div class="macro-details">
                            <div class="macro-item protein-color">
                                <h4><?= $macroResults['protein'] ?>g</h4>
                                <span>PROTEIN</span>
                                <small><?= round($macroResults['protein'] * 4 / $macroResults['calories'] * 100) ?>%</small>
                            </div>
                            <div class="macro-item carbs-color">
                                <h4><?= $macroResults['carbs'] ?>g</h4>
                                <span>CARBS</span>
                                <small><?= round($macroResults['carbs'] * 4 / $macroResults['calories'] * 100) ?>%</small>
                            </div>
                            <div class="macro-item fats-color">
                                <h4><?= $macroResults['fats'] ?>g</h4>
                                <span>FATS</span>
                                <small><?= round($macroResults['fats'] * 9 / $macroResults['calories'] * 100) ?>%</small>
                            </div>
                        </div>
                        
                        <div class="recommendations mt-4">
                            <h5 class="text-center">Recommendations</h5>
                            <?php if (strpos($macroResults['goal'], 'lose') !== false): ?>
                                <ul>
                                    <li>Eat <?= round($macroResults['protein'] / 4) ?> protein servings daily</li>
                                    <li>Focus on lean proteins and vegetables</li>
                                    <li>Limit processed carbs and sugars</li>
                                </ul>
                            <?php elseif (strpos($macroResults['goal'], 'gain') !== false): ?>
                                <ul>
                                    <li>Consume protein every 3-4 hours</li>
                                    <li>Time carbs around workouts</li>
                                    <li>Include healthy fats with meals</li>
                                </ul>
                            <?php else: ?>
                                <ul>
                                    <li>Maintain balanced macros</li>
                                    <li>Adjust portions based on activity</li>
                                    <li>Focus on whole foods</li>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button class="btn btn-outline me-2" id="save-macros">
                                <i class="fas fa-save me-2"></i> Save
                            </button>
                            <button class="btn btn-outline" id="print-macros">
                                <i class="fas fa-print me-2"></i> Print
                            </button>
                        </div>
                    </div>
                    <!-- Meal Planning Tips -->
<div class="meal-tips p-4" style="background: rgba(0, 0, 0, 0.3); border-radius: 10px;">
    <h5 class="text-center mb-3" style="color: #00ffcc;">RECOMMENDATIONS</h5>
    
    <?php if (isset($macroResults['goal'])): ?>
        <?php if (strpos($macroResults['goal'], 'lose') !== false): ?>
            <div class="tip-item">
                <h6><i class="fas fa-weight me-2" style="color: #00ccff;"></i> Weight Loss Strategy</h6>
                <ul>
                    <li>Eat <strong>3-4 meals</strong> daily with controlled portions</li>
                    <li>Prioritize <strong>lean proteins</strong> (chicken, fish, tofu)</li>
                    <li>Fill half your plate with <strong>vegetables</strong></li>
                    <li>Choose <strong>complex carbs</strong> (oats, quinoa, sweet potatoes)</li>
                    <li>Include <strong>healthy fats</strong> (avocado, nuts, olive oil)</li>
                    <li>Stay hydrated with <strong>3-4 liters</strong> of water daily</li>
                </ul>
            </div>
        <?php elseif (strpos($macroResults['goal'], 'gain') !== false): ?>
            <div class="tip-item">
                <h6><i class="fas fa-dumbbell me-2" style="color: #00ccff;"></i> Muscle Gain Strategy</h6>
                <ul>
                    <li>Eat <strong>4-6 meals/snacks</strong> throughout the day</li>
                    <li>Consume <strong>protein</strong> every 3-4 hours (30-40g per meal)</li>
                    <li>Time <strong>carbs</strong> around workouts for energy</li>
                    <li>Include <strong>healthy fats</strong> with meals</li>
                    <li>Post-workout: <strong>protein + carbs</strong> for recovery</li>
                    <li>Increase calories gradually if weight stalls</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="tip-item">
                <h6><i class="fas fa-balance-scale me-2" style="color: #00ccff;"></i> Maintenance Strategy</h6>
                <ul>
                    <li>Eat <strong>3-4 balanced meals</strong> daily</li>
                    <li>Maintain consistent <strong>protein intake</strong></li>
                    <li>Adjust portions based on activity level</li>
                    <li>Focus on <strong>nutrient-dense foods</strong></li>
                    <li>Allow for <strong>flexibility</strong> in your diet</li>
                    <li>Monitor weight weekly and adjust as needed</li>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="tip-item mt-3">
        <h6><i class="fas fa-utensils me-2" style="color: #00ccff;"></i> Sample Daily Meal Plan</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="meal-time p-2">
                    <strong>Breakfast</strong><br>
                    <?= round($macroResults['protein'] * 0.25) ?>g protein<br>
                    <?= round($macroResults['carbs'] * 0.25) ?>g carbs<br>
                    <?= round($macroResults['fats'] * 0.25) ?>g fats
                </div>
            </div>
            <div class="col-md-4">
                <div class="meal-time p-2">
                    <strong>Lunch</strong><br>
                    <?= round($macroResults['protein'] * 0.35) ?>g protein<br>
                    <?= round($macroResults['carbs'] * 0.35) ?>g carbs<br>
                    <?= round($macroResults['fats'] * 0.35) ?>g fats
                </div>
            </div>
            <div class="col-md-4">
                <div class="meal-time p-2">
                    <strong>Dinner</strong><br>
                    <?= round($macroResults['protein'] * 0.3) ?>g protein<br>
                    <?= round($macroResults['carbs'] * 0.3) ?>g carbs<br>
                    <?= round($macroResults['fats'] * 0.3) ?>g fats
                </div>
            </div>
        </div>
        <div class="text-center mt-2">
            <small class="text-muted">Remaining macros can be used for snacks</small>
        </div>
    </div>
</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Nutrition Tips Section -->
    <section class="nutrition-tips">
        <div class="container">
            <div class="section-title">
                <h2>NUTRITION TIPS</h2>
                <p>Expert advice to optimize your diet and performance</p>
            </div>
            <div class="row">
                <?php foreach ($nutritionTips as $tip): ?>
                <div class="col-md-4 animate__animated animate__fadeInUp">
                    <div class="tip-card">
                        <i class="fas <?= $tip['icon'] ?? 'fa-utensils' ?> tip-icon"></i>
                        <h4 class="tip-title"><?= htmlspecialchars($tip['title']) ?></h4>
                        <p class="tip-content"><?= htmlspecialchars($tip['content']) ?></p>
                        <a href="#" class="tip-link">Learn more</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Nutrition Products Section -->
    <section class="nutrition-products">
        <div class="container">
            <div class="section-title">
                <h2>NUTRITION PRODUCTS</h2>
                <p>Premium supplements to support your fitness goals</p>
            </div>
            <div class="product-grid">
                <?php foreach ($nutritionProducts as $product): ?>
                <div class="product-card animate__animated animate__fadeInUp">
                    <div class="product-image">
                        <?php if (isset($product['stock']) && $product['stock'] <= 0): ?>
                            <div class="product-badge">Out of Stock</div>
                        <?php elseif (isset($product['stock']) && $product['stock'] < 10): ?>
                            <div class="product-badge">Low Stock</div>
                        <?php endif; ?>
                        <img src="images/<?= $product['image_url'] ?? 'default-product.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($product['category']) ?></div>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= isset($product['description']) ? substr(htmlspecialchars($product['description']), 0, 80) . '...' : '' ?></p>
                        <div class="product-price">
                            <span class="current-price">₹<?= htmlspecialchars($product['price']) ?></span>
                            <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <span class="old-price">₹<?= htmlspecialchars($product['old_price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-action">
                            <button class="btn btn-primary add-to-cart" data-product-id="<?= $product['id'] ?>" <?= isset($product['stock']) && $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                            <button class="btn btn-outline view-product" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-eye me-2"></i>Details
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Modals -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Product Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productModalBody">
                    Loading product details...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="modalAddToCart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="planModalTitle">Meal Plan Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="planModalBody">
                    Loading plan details...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadPlan">Download Plan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <a href="#" class="footer-logo">FitFusion</a>
                    <p>Your complete fitness solution with workout plans, nutrition guidance, and premium supplements.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="workouts.php">Workouts</a></li>
                        <li><a href="nutrition.php">Nutrition</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4 class="footer-heading">Support</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4 class="footer-heading">Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Fitness St, Health City</p>
                    <p><i class="fas fa-envelope"></i> info@fitfusion.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> FitFusion. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            $('.mobile-menu-toggle').click(function() {
                $('nav ul').toggleClass('active');
            });
            
            // View product details
            // Update the view product details function
$('.view-product').click(function() {
    const productId = $(this).data('product-id');
    $('#productModal').modal('show');
    
    $.ajax({
        url: 'nutrition.php?action=get_product&id=' + productId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Add detailed nutrition info to the modal
                const html = response.html + `
                    <div class="nutrition-facts mt-4 p-3" style="background: rgba(0,0,0,0.3); border-radius: 10px;">
                        <h5 style="color: #00ffcc;">Nutrition Facts</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Serving Size:</strong> 1 scoop (30g)</p>
                                <p><strong>Calories:</strong> 120</p>
                                <p><strong>Protein:</strong> 24g</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Carbs:</strong> 3g</p>
                                <p><strong>Fats:</strong> 1g</p>
                                <p><strong>Sugar:</strong> 1g</p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h6>Ingredients</h6>
                            <p>Whey Protein Concentrate, Natural Flavors, Sunflower Lecithin, Stevia</p>
                        </div>
                        <div class="mt-2">
                            <h6>Usage</h6>
                            <p>Mix 1 scoop with 8-10 oz of water or milk. Consume post-workout or between meals.</p>
                        </div>
                    </div>
                `;
                $('#productModalBody').html(html);
                $('#productModalTitle').text($('#productModalBody').find('h2').text());
                $('#modalAddToCart').data('product-id', productId);
            } else {
                $('#productModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#productModalBody').html('<div class="alert alert-danger">Could not load product details.</div>');
        }
    });
});
            
            // View plan details
            $('.view-plan-btn').click(function() {
                const planId = $(this).data('plan-id');
                $('#planModal').modal('show');
                
                $.ajax({
                    url: 'nutrition.php?action=get_plan&id=' + planId,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#planModalBody').html(response.html);
                            $('#planModalTitle').text($('#planModalBody').find('h2').text());
                        } else {
                            $('#planModalBody').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#planModalBody').html('<div class="alert alert-danger">Could not load plan details.</div>');
                    }
                });
            });
            
            // Add to cart
            $('.add-to-cart, #modalAddToCart').click(function(e) {
                e.preventDefault();
                const productId = $(this).data('product-id');
                
                $.ajax({
                    url: 'nutrition.php?action=add_to_cart',
                    type: 'POST',
                    data: { product_id: productId },
                    success: function(response) {
                        if (response.success) {
                            $('.cart-count').text(response.cart_count);
                            alert('Product added to cart!');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error adding to cart.');
                    }
                });
            });
            
            // Save macros
            $('#save-macros').click(function() {
                alert('Macros saved to your profile! (Implementation would save to database)');
            });
            
            // Print macros
            $('#print-macros').click(function() {
                window.print();
            });
            
            // Download plan
            $('#downloadPlan').click(function() {
                const planId = $('#planModalBody').find('input[name="plan_id"]').val();
                alert('Downloading plan ID: ' + planId);
            });
        });
    </script>
</body>
</html>
<?php
// workouts.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

session_start();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Initialize filters with default values
$category = $_GET['category'] ?? 'all';
$difficulty = $_GET['difficulty'] ?? 'all';
$duration = $_GET['duration'] ?? 'all';

// Initialize variables
$workouts = [];
$categories = [];

try {
    // Build the base query
    $query = 'SELECT w.*, t.name AS trainer, 
             GROUP_CONCAT(eq.name SEPARATOR ", ") AS equipment
             FROM workouts w
             JOIN trainers t ON w.trainer_id = t.id
             LEFT JOIN workout_equipment we ON w.id = we.workout_id
             LEFT JOIN equipment eq ON we.equipment_id = eq.id
             WHERE 1=1';
    
    $params = [];

    // Add filters to query
    if ($category != 'all') {
        $query .= ' AND w.category = ?';
        $params[] = $category;
    }

    if ($difficulty != 'all') {
        $query .= ' AND w.difficulty = ?';
        $params[] = $difficulty;
    }

    if ($duration != 'all') {
        if ($duration == 'short') {
            $query .= ' AND w.duration <= 15';
        } elseif ($duration == 'medium') {
            $query .= ' AND w.duration > 15 AND w.duration <= 30';
        } elseif ($duration == 'long') {
            $query .= ' AND w.duration > 30';
        }
    }

    $query .= ' GROUP BY w.id';

    // Debug: Check the query before preparing
    // error_log("Final query: " . $query);

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $workouts = $stmt->fetchAll();

    // Get unique categories for filter dropdown
    $categoriesQuery = $pdo->query("SELECT DISTINCT category FROM workouts");
    $categories = $categoriesQuery->fetchAll(PDO::FETCH_COLUMN);

} catch (\PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Fallback to sample data
    $workouts = [
        [
            'id' => 1,
            'name' => 'HIIT Cardio Blast',
            'category' => 'cardio',
            'difficulty' => 'intermediate',
            'duration' => 20,
            'calories' => 300,
            'description' => 'High-intensity interval training workout that burns calories and improves cardiovascular health.',
            'image_url' => 'hiit-cardio.jpg',
            'trainer' => 'Alex Morgan',
            'equipment' => 'None'
        ],
        [
            'id' => 2,
            'name' => 'Core Power Yoga',
            'category' => 'yoga',
            'difficulty' => 'beginner',
            'duration' => 30,
            'calories' => 180,
            'description' => 'Strengthen your core and improve flexibility with this dynamic yoga sequence.',
            'image_url' => 'core-yoga.jpg',
            'trainer' => 'Maya Singh',
            'equipment' => 'Yoga mat'
        ],
        [
            'id' => 3,
            'name' => 'Ultimate Strength Circuit',
            'category' => 'strength',
            'difficulty' => 'advanced',
            'duration' => 45,
            'calories' => 400,
            'description' => 'Build muscle and increase strength with this comprehensive full-body workout.',
            'image_url' => 'strength-circuit.jpg',
            'trainer' => 'Chris Evans',
            'equipment' => 'Dumbbells, Kettlebells, Resistance bands'
        ],
        [
            'id' => 4,
            'name' => 'Pilates Core Focus',
            'category' => 'pilates',
            'difficulty' => 'intermediate',
            'duration' => 25,
            'calories' => 220,
            'description' => 'Target your core with this Pilates routine designed to strengthen and tone your midsection.',
            'image_url' => 'pilates-core.jpg',
            'trainer' => 'Emma Roberts',
            'equipment' => 'Pilates mat'
        ],
        [
            'id' => 5,
            'name' => 'Boxing Cardio Knockout',
            'category' => 'cardio',
            'difficulty' => 'advanced',
            'duration' => 35,
            'calories' => 450,
            'description' => 'Burn major calories with this high-energy boxing-inspired cardio workout.',
            'image_url' => 'boxing-cardio.jpg',
            'trainer' => 'Mike Tyson',
            'equipment' => 'Boxing gloves (optional)'
        ],
        [
            'id' => 6,
            'name' => 'Gentle Morning Stretch',
            'category' => 'stretch',
            'difficulty' => 'beginner',
            'duration' => 15,
            'calories' => 100,
            'description' => 'Start your day right with this gentle full-body stretch routine to improve mobility.',
            'image_url' => 'morning-stretch.jpg',
            'trainer' => 'Sarah Johnson',
            'equipment' => 'None'
        ],
        [
            'id' => 7,
            'name' => 'Kettlebell Power',
            'category' => 'strength',
            'difficulty' => 'intermediate',
            'duration' => 30,
            'calories' => 350,
            'description' => 'Build functional strength and power with this kettlebell-focused workout.',
            'image_url' => 'kettlebell-power.jpg',
            'trainer' => 'Steve Rogers',
            'equipment' => 'Kettlebells'
        ],
        [
            'id' => 8,
            'name' => 'Body Weight HIIT',
            'category' => 'cardio',
            'difficulty' => 'beginner',
            'duration' => 20,
            'calories' => 250,
            'description' => 'No equipment needed for this effective high-intensity interval training session.',
            'image_url' => 'bodyweight-hiit.jpg',
            'trainer' => 'Natasha Carter',
            'equipment' => 'None'
        ],
        [
            'id' => 9,
            'name' => 'Advanced Calisthenics',
            'category' => 'strength',
            'difficulty' => 'advanced',
            'duration' => 40,
            'calories' => 380,
            'description' => 'Take your body weight training to the next level with these advanced moves.',
            'image_url' => 'advanced-calisthenics.jpg',
            'trainer' => 'Bruce Wayne',
            'equipment' => 'Pull-up bar'
        ]
    ];
    
    $categories = array_unique(array_column($workouts, 'category'));
}

// Apply filters to sample data if database failed
if (isset($e)) {
    $filteredWorkouts = [];
    foreach ($workouts as $workout) {
        $matches = true;
        
        // Category filter
        if ($category != 'all' && $workout['category'] != $category) {
            $matches = false;
        }
        
        // Difficulty filter
        if ($difficulty != 'all' && $workout['difficulty'] != $difficulty) {
            $matches = false;
        }
        
        // Duration filter
        if ($duration != 'all') {
            if ($duration == 'short' && $workout['duration'] > 15) {
                $matches = false;
            } elseif ($duration == 'medium' && ($workout['duration'] <= 15 || $workout['duration'] > 30)) {
                $matches = false;
            } elseif ($duration == 'long' && $workout['duration'] <= 30) {
                $matches = false;
            }
        }
        
        if ($matches) {
            $filteredWorkouts[] = $workout;
        }
    }
    $workouts = $filteredWorkouts;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workouts - FitFusion</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .form-select {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.form-select:focus {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
    border-color: #00ffcc !important;
    box-shadow: 0 0 0 0.25rem rgba(0, 255, 204, 0.25) !important;
}

/* Dropdown options styling */
.form-select option {
    background-color: #111 !important;
    color: #fff !important;
}

/* For Firefox */
@-moz-document url-prefix() {
    .form-select {
        color: #fff !important;
    }
    .form-select option {
        background-color: #111 !important;
    }
}

/* For IE */
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
    .form-select {
        color: #fff !important;
    }
    .form-select option {
        background-color: #111 !important;
    }
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

        /* Workout Hero */
        .workout-hero {
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 80px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('images/workout-bg.jpg'); /* Dark overlay with image */
            background-size: cover;
            background-position: center;
            position: relative;
            text-align: center;
            overflow: hidden;
        }

        .workout-hero::before {
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

        .workout-hero::after {
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
            max-width: 800px;
            padding: 0 20px;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #00ffcc, #ff00cc); /* Neon gradient */
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-content p {
            font-size: 1.2rem;
            line-height: 1.7;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.8); /* Light gray text */
        }

        /* Filter Section */
        .filter-section {
            background: #111;
            padding: 30px 0;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .filter-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .filter-select {
            background: #222;
            color: #fff;
            border: 1px solid rgba(0, 255, 204, 0.3);
            padding: 10px 15px;
            border-radius: 50px;
            font-size: 0.9rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #00ffcc;
            box-shadow: 0 0 0 2px rgba(0, 255, 204, 0.2);
        }

        .filter-btn {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            align-self: flex-end;
            margin-top: 24px;
        }

        .filter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.3);
        }

        /* Workouts Grid */
        .workouts-section {
            padding: 80px 0;
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

        .workouts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .workout-card {
            background: linear-gradient(145deg, #111, #1a1a1a);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.4s, box-shadow 0.4s;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .workout-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 255, 204, 0.2);
            border-color: rgba(0, 255, 204, 0.2);
        }

        .workout-image {
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .workout-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .workout-card:hover .workout-image img {
            transform: scale(1.1);
        }

        .workout-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            z-index: 2;
        }

        .beginner {
            background: linear-gradient(135deg, #00ffcc, #00ccff);
            color: #000;
        }

        .intermediate {
            background: linear-gradient(135deg, #ccff00, #ffcc00);
            color: #000;
        }

        .advanced {
            background: linear-gradient(135deg, #ff00cc, #ff3300);
            color: #000;
        }

        .workout-details {
            padding: 25px;
        }

        .workout-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff, #ccc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .workout-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .meta-icon {
            color: #00ffcc;
        }

        .workout-description {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;

        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        }



        .workout-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .trainer {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trainer-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: #000;
        }

        .trainer-name {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .view-workout {
            background: transparent;
            border: 1px solid rgba(0, 255, 204, 0.5);
            color: #00ffcc;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .view-workout:hover {
            background: rgba(0, 255, 204, 0.1);
            transform: translateY(-2px);
        }

        .workout-detail-modal .modal-content {
            background: #111;
            color: #fff;
            border: 1px solid rgba(0, 255, 204, 0.3);
            border-radius: 15px;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }

        .modal-body {
            padding: 20px;
        }

        .workout-modal-image {
            width: 100%;
            height: 250px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .workout-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .modal-meta-icon {
            color: #00ffcc;
            font-size: 1.1rem;
        }

        .modal-description {
            margin-bottom: 20px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
        }

        .workout-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-start-workout {
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-start-workout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.3);
        }

        .btn-save-workout {
            background: transparent;
            border: 1px solid rgba(0, 255, 204, 0.5);
            color: #00ffcc;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save-workout:hover {
            background: rgba(0, 255, 204, 0.1);
        }

        .btn-close {
            color: #fff;
            opacity: 0.8;
        }

        .exercise-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .exercise-item {
            padding: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exercise-name {
            font-weight: 600;
            color: #fff;
        }

        .exercise-duration {
            color: rgba(0, 255, 204, 0.8);
            font-size: 0.9rem;
            font-weight: 600;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .workouts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 3rem;
            }
            .workouts-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            nav ul {
                gap: 20px;
            }
        }

        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                width: 100%;
            }
            nav ul {
                display: none;
            }
            .workout-meta {
                flex-wrap: wrap;
                gap: 10px;
            }
        }

        /* Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        /* No results style */
        .no-results {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin: 30px auto;
            max-width: 600px;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: rgba(0, 255, 204, 0.7);
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #fff;
        }

        .no-results p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
        }

        .reset-filters {
            display: inline-block;
            background: linear-gradient(135deg, #00ffcc, #ff00cc);
            color: #000;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .reset-filters:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.3);
        }
        /* Workout Steps Styles */
    .workout-steps {
        margin-top: 20px;
    }

    .exercise-title {
        font-weight: 700;
        color: #00ffcc;
    }

    .exercise-timer {
        font-family: monospace;
        font-size: 1.2rem;
        font-weight: 700;
        color: #fff;
        background: rgba(0, 0, 0, 0.3);
        padding: 4px 12px;
        border-radius: 20px;
    }

    .exercise-instructions {
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.6;
        margin-top: 10px;
    }

    .btn-previous:not(:disabled):hover,
    .btn-pause:hover,
    .btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .workout-progress-container {
        padding: 10px 0;
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
                        <li><a href="workouts.php" class="active">Workouts</a></li>
                        <li><a href="nutrition.php">Nutrition</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </nav>
                <div class="header-icons">
                    <a href="profile.php" class="header-icon"><i class="fas fa-user"></i></a>
                    <a href="cart.php" class="header-icon cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Workout Hero Section -->
    <section class="workout-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Perfect Workout</h1>
                <p>Discover hundreds of workouts designed by professional trainers to help you reach your fitness goals. Filter by category, difficulty, and duration to find the perfect match for your needs.</p>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form action="workouts.php" method="GET" class="filter-container">
                <div class="filter-group">
                    <label class="filter-label">Category</label>
                    <select name="category" class="filter-select">
                        <option value="all" <?= $category == 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category == $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($cat)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Difficulty</label>
                    <select name="difficulty" class="filter-select">
                        <option value="all" <?= $difficulty == 'all' ? 'selected' : '' ?>>All Levels</option>
                        <option value="beginner" <?= $difficulty == 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= $difficulty == 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= $difficulty == 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Duration</label>
                    <select name="duration" class="filter-select">
                        <option value="all" <?= $duration == 'all' ? 'selected' : '' ?>>Any Duration</option>
                        <option value="short" <?= $duration == 'short' ? 'selected' : '' ?>>Short (≤ 15 min)</option>
                        <option value="medium" <?= $duration == 'medium' ? 'selected' : '' ?>>Medium (16-30 min)</option>
                        <option value="long" <?= $duration == 'long' ? 'selected' : '' ?>>Long (> 30 min)</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Apply Filters</button>
                <?php if ($category != 'all' || $difficulty != 'all' || $duration != 'all'): ?>
                <a href="workouts.php" class="filter-btn" style="background: #333; margin-left: 10px;">Reset Filters</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- Workouts Grid Section -->
    <section class="workouts-section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Workouts</h2>
                <?php if ($category != 'all' || $difficulty != 'all' || $duration != 'all'): ?>
                <p class="text-muted">Showing <?= count($workouts) ?> workout(s) matching your filters</p>
                <?php endif; ?>
            </div>

            <?php if (count($workouts) > 0): ?>
            <div class="workouts-grid">
                <?php foreach ($workouts as $workout): ?>
                <div class="workout-card">
                    <div class="workout-image">
                        <img src="images/<?= htmlspecialchars($workout['image_url']) ?>" alt="<?= htmlspecialchars($workout['name']) ?>">
                        <div class="workout-badge <?= htmlspecialchars($workout['difficulty']) ?>">
                            <?= ucfirst($workout['difficulty']) ?>
                        </div>
                    </div>
                    <div class="workout-details">
                        <h3 class="workout-title"><?= htmlspecialchars($workout['name']) ?></h3>
                        <div class="workout-meta">
                            <div class="meta-item">
                                <i class="fas fa-tag meta-icon"></i>
                                <span><?= ucfirst($workout['category']) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock meta-icon"></i>
                                <span><?= $workout['duration'] ?> min</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-fire meta-icon"></i>
                                <span><?= $workout['calories'] ?> cal</span>
                            </div>
                        </div>
                        <p class="workout-description"><?= htmlspecialchars($workout['description']) ?></p>
                        <div class="workout-footer">
                            <div class="trainer">
                                <div class="trainer-avatar">
                                    <?= substr($workout['trainer'], 0, 1) ?>
                                </div>
                                <span class="trainer-name"><?= htmlspecialchars($workout['trainer']) ?></span>
                            </div>
                            <button class="view-workout" data-bs-toggle="modal" data-bs-target="#workoutModal<?= $workout['id'] ?>">View Details</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No workouts found</h3>
                <p>Try adjusting your filters to find the perfect workout for you.</p>
                <a href="workouts.php" class="reset-filters">Reset Filters</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Workout Detail Modals -->
<?php foreach ($workouts as $workout): ?>
<div class="modal fade workout-detail-modal" id="workoutModal<?= $workout['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= htmlspecialchars($workout['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="workout-modal-image">
                    <img src="images/<?= htmlspecialchars($workout['image_url']) ?>" alt="<?= htmlspecialchars($workout['name']) ?>">
                </div>
                <div class="modal-meta">
                    <!-- ... (keep existing meta content) ... -->
                </div>
                <div class="modal-description">
                    <p><?= htmlspecialchars($workout['description']) ?></p>
                </div>
                
                <!-- Exercise List -->
                <h4>Workout Plan</h4>
                <ul class="exercise-list" id="exercise-list-<?= $workout['id'] ?>">
                    <?php 
                    // Generate exercise data
                    $exercises = [
                        ['name' => 'Warm-up', 'duration' => 300, 'instructions' => 'Basic warm-up exercises'],
                        ['name' => 'Main Workout', 'duration' => ceil($workout['duration'] * 0.7) * 60, 'instructions' => 'Primary workout sequence'],
                        ['name' => 'Cool Down', 'duration' => 300, 'instructions' => 'Stretching and recovery']
                    ];
                    ?>
                    <?php foreach ($exercises as $exercise): ?>
                    <li class="exercise-item">
                        <span class="exercise-name"><?= htmlspecialchars($exercise['name']) ?></span>
                        <span class="exercise-duration"><?= gmdate("i:s", $exercise['duration']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Hidden div to store exercise data for JavaScript -->
                <div id="exercise-data-<?= $workout['id'] ?>" data-exercises='<?= json_encode($exercises) ?>' style="display: none;"></div>
                
                <!-- Workout Steps (initially hidden) -->
                <div id="workout-steps-<?= $workout['id'] ?>" class="workout-steps" style="display: none;">
                    <h4 class="mt-4 mb-3">Let's Get Started!</h4>
                    <div class="workout-progress-container mb-4">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #00ffcc, #ff00cc);" id="workout-progress-<?= $workout['id'] ?>"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">0%</small>
                            <small class="text-muted">100%</small>
                        </div>
                    </div>
                    
                    <div class="current-exercise p-3 mb-4" style="background: rgba(0, 255, 204, 0.1); border-radius: 10px; border: 1px solid rgba(0, 255, 204, 0.3);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="exercise-title mb-0" id="current-exercise-title-<?= $workout['id'] ?>">Warm-up</h5>
                            <div class="exercise-timer" id="exercise-timer-<?= $workout['id'] ?>">05:00</div>
                        </div>
                        <div id="exercise-instructions-<?= $workout['id'] ?>" class="exercise-instructions">
                            Basic warm-up exercises
                        </div>
                    </div>
                    
                    <div class="workout-controls d-flex justify-content-between">
                        <button class="btn-previous" id="previous-step-<?= $workout['id'] ?>" disabled style="background: transparent; border: 1px solid rgba(255, 255, 255, 0.3); color: rgba(255, 255, 255, 0.5); padding: 10px 20px; border-radius: 50px; font-weight: 600; cursor: pointer;">Previous</button>
                        <button class="btn-pause" id="pause-workout-<?= $workout['id'] ?>" style="background: transparent; border: 1px solid rgba(255, 255, 255, 0.5); color: #fff; padding: 10px 20px; border-radius: 50px; font-weight: 600; cursor: pointer;">Pause</button>
                        <button class="btn-next" id="next-step-<?= $workout['id'] ?>" style="background: rgba(0, 255, 204, 0.2); border: 1px solid rgba(0, 255, 204, 0.5); color: #00ffcc; padding: 10px 20px; border-radius: 50px; font-weight: 600; cursor: pointer;">Next</button>
                    </div>
                </div>

                <div class="workout-actions" id="workout-actions-<?= $workout['id'] ?>">
                    <button class="btn-start-workout" data-workout-id="<?= $workout['id'] ?>">Start Workout</button>
                    <button class="btn-save-workout">Save for Later</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">FitFusion</div>
                    <p>FitFusion is your all-in-one fitness platform offering premium workout programs, nutrition guidance, and fitness equipment to help you achieve your goals.</p>
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
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4 class="footer-heading">Support</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="shipping.php">Shipping Policy</a></li>
                        <li><a href="returns.php">Returns Policy</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4 class="footer-heading">Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Fitness Street, Exercise City</p>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> info@fitfusion.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> FitFusion. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all start workout buttons
    const startWorkoutButtons = document.querySelectorAll('.btn-start-workout');
    
    // Add click event to all start workout buttons
    startWorkoutButtons.forEach(button => {
        button.addEventListener('click', function() {
            const workoutId = this.getAttribute('data-workout-id');
            const exerciseData = document.getElementById(`exercise-data-${workoutId}`).dataset.exercises;
            const exercises = JSON.parse(exerciseData);
            startWorkout(workoutId, exercises);
        });
    });
    
    function startWorkout(workoutId, exercises) {
        // Hide workout actions and show workout steps
        document.getElementById(`workout-actions-${workoutId}`).style.display = 'none';
        document.getElementById(`workout-steps-${workoutId}`).style.display = 'block';
        
        // Initialize workout variables
        let currentExerciseIndex = 0;
        let timer = null;
        let remainingTime = exercises[currentExerciseIndex].duration;
        let isPaused = false;
        
        // Set up initial exercise
        updateExerciseDisplay(workoutId, currentExerciseIndex, exercises);
        startTimer(workoutId, exercises);
        
        // Set up event listeners for workout controls
        document.getElementById(`next-step-${workoutId}`).addEventListener('click', function() {
            if (currentExerciseIndex < exercises.length - 1) {
                currentExerciseIndex++;
                remainingTime = exercises[currentExerciseIndex].duration;
                updateExerciseDisplay(workoutId, currentExerciseIndex, exercises);
                
                // Enable previous button after first exercise
                document.getElementById(`previous-step-${workoutId}`).disabled = false;
                
                // Disable next button on last exercise
                if (currentExerciseIndex === exercises.length - 1) {
                    this.innerHTML = 'Finish';
                }
            } else {
                // Workout completed
                clearInterval(timer);
                document.getElementById(`workout-steps-${workoutId}`).innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-4" style="font-size: 3rem; color: #00ffcc;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="mb-3">Great Work!</h3>
                        <p class="mb-4">You've completed the workout. How do you feel?</p>
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">😓 Exhausted</button>
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">😊 Good</button>
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">💪 Energized</button>
                        </div>
                        <button class="mt-3" style="background: linear-gradient(135deg, #00ffcc, #ff00cc); color: #000; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; cursor: pointer;" data-bs-dismiss="modal">Done</button>
                    </div>
                `;
            }
        });
        
        document.getElementById(`previous-step-${workoutId}`).addEventListener('click', function() {
            if (currentExerciseIndex > 0) {
                currentExerciseIndex--;
                remainingTime = exercises[currentExerciseIndex].duration;
                updateExerciseDisplay(workoutId, currentExerciseIndex, exercises);
                
                // Disable previous button on first exercise
                if (currentExerciseIndex === 0) {
                    this.disabled = true;
                }
                
                // Change next button text back if needed
                document.getElementById(`next-step-${workoutId}`).innerHTML = 'Next';
            }
        });
        
        document.getElementById(`pause-workout-${workoutId}`).addEventListener('click', function() {
            if (isPaused) {
                // Resume timer
                isPaused = false;
                this.textContent = 'Pause';
                startTimer(workoutId, exercises);
            } else {
                // Pause timer
                isPaused = true;
                this.textContent = 'Resume';
                clearInterval(timer);
            }
        });
        
        function updateExerciseDisplay(workoutId, index, exercises) {
            const exercise = exercises[index];
            
            // Update exercise title and instructions
            document.getElementById(`current-exercise-title-${workoutId}`).textContent = exercise.name;
            document.getElementById(`exercise-instructions-${workoutId}`).textContent = exercise.instructions;
            
            // Update timer display
            updateTimerDisplay(workoutId, remainingTime);
            
            // Update progress bar
            const progressPercentage = (index / (exercises.length - 1)) * 100;
            document.getElementById(`workout-progress-${workoutId}`).style.width = `${progressPercentage}%`;
            
            // Clear existing timer and start new one
            clearInterval(timer);
            if (!isPaused) {
                startTimer(workoutId, exercises);
            }
        }
        
        function startTimer(workoutId, exercises) {
            timer = setInterval(() => {
                remainingTime--;
                
                // Update timer display
                updateTimerDisplay(workoutId, remainingTime);
                
                // Check if time is up
                if (remainingTime <= 0) {
                    clearInterval(timer);
                    
                    // Auto-advance to next exercise if not the last one
                    if (currentExerciseIndex < exercises.length - 1) {
                        setTimeout(() => {
                            document.getElementById(`next-step-${workoutId}`).click();
                        }, 1000);
                    }
                }
            }, 1000);
        }
        
        function updateTimerDisplay(workoutId, seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            document.getElementById(`exercise-timer-${workoutId}`).textContent = 
                `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    }
});
</script>
    
    // Add click event to all start workout buttons
    startWorkoutButtons.forEach(button => {
        button.addEventListener('click', function() {
            const workoutId = this.getAttribute('data-workout-id');
            startWorkout(workoutId);
        });
    });
    
    function startWorkout(workoutId) {
        // Hide workout actions and show workout steps
        document.getElementById(`workout-actions-${workoutId}`).style.display = 'none';
        document.getElementById(`workout-steps-${workoutId}`).style.display = 'block';
        
        // Initialize workout variables
        let currentExerciseIndex = 0;
        let timer = null;
        let remainingTime = workoutExercises[workoutId][currentExerciseIndex].duration;
        let isPaused = false;
        
        // Set up initial exercise
        updateExerciseDisplay(workoutId, currentExerciseIndex);
        startTimer(workoutId);
        
        // Set up event listeners for workout controls
        document.getElementById(`next-step-${workoutId}`).addEventListener('click', function() {
            if (currentExerciseIndex < workoutExercises[workoutId].length - 1) {
                currentExerciseIndex++;
                remainingTime = workoutExercises[workoutId][currentExerciseIndex].duration;
                updateExerciseDisplay(workoutId, currentExerciseIndex);
                
                // Enable previous button after first exercise
                document.getElementById(`previous-step-${workoutId}`).disabled = false;
                
                // Disable next button on last exercise
                if (currentExerciseIndex === workoutExercises[workoutId].length - 1) {
                    this.innerHTML = 'Finish';
                }
            } else {
                // Workout completed
                clearInterval(timer);
                document.getElementById(`workout-steps-${workoutId}`).innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-4" style="font-size: 3rem; color: #00ffcc;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="mb-3">Great Work!</h3>
                        <p class="mb-4">You've completed the workout. How do you feel?</p>
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">😓 Exhausted</button>
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">😊 Good</button>
                            <button class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; border-radius: 50px; padding: 5px 15px;">💪 Energized</button>
                        </div>
                        <button class="mt-3" style="background: linear-gradient(135deg, #00ffcc, #ff00cc); color: #000; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; cursor: pointer;" data-bs-dismiss="modal">Done</button>
                    </div>
                `;
            }
        });
        
        document.getElementById(`previous-step-${workoutId}`).addEventListener('click', function() {
            if (currentExerciseIndex > 0) {
                currentExerciseIndex--;
                remainingTime = workoutExercises[workoutId][currentExerciseIndex].duration;
                updateExerciseDisplay(workoutId, currentExerciseIndex);
                
                // Disable previous button on first exercise
                if (currentExerciseIndex === 0) {
                    this.disabled = true;
                }
                
                // Change next button text back if needed
                document.getElementById(`next-step-${workoutId}`).innerHTML = 'Next';
            }
        });
        
        document.getElementById(`pause-workout-${workoutId}`).addEventListener('click', function() {
            if (isPaused) {
                // Resume timer
                isPaused = false;
                this.textContent = 'Pause';
                startTimer(workoutId);
            } else {
                // Pause timer
                isPaused = true;
                this.textContent = 'Resume';
                clearInterval(timer);
            }
        });
        
        function updateExerciseDisplay(workoutId, index) {
            const exercise = workoutExercises[workoutId][index];
            
            // Update exercise title and instructions
            document.getElementById(`current-exercise-title-${workoutId}`).textContent = exercise.title;
            document.getElementById(`exercise-instructions-${workoutId}`).textContent = exercise.instructions;
            
            // Update timer display
            updateTimerDisplay(workoutId, exercise.duration);
            
            // Update progress bar
            const progressPercentage = (index / (workoutExercises[workoutId].length - 1)) * 100;
            document.getElementById(`workout-progress-${workoutId}`).style.width = `${progressPercentage}%`;
            
            // Clear existing timer and start new one
            clearInterval(timer);
            if (!isPaused) {
                startTimer(workoutId);
            }
        }
        
        function startTimer(workoutId) {
            timer = setInterval(() => {
                remainingTime--;
                
                // Update timer display
                updateTimerDisplay(workoutId, remainingTime);
                
                // Check if time is up
                if (remainingTime <= 0) {
                    clearInterval(timer);
                    
                    // Auto-advance to next exercise if not the last one
                    if (currentExerciseIndex < workoutExercises[workoutId].length - 1) {
                        setTimeout(() => {
                            document.getElementById(`next-step-${workoutId}`).click();
                        }, 1000);
                    }
                }
            }, 1000);
        }
        
        function updateTimerDisplay(workoutId, seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            document.getElementById(`exercise-timer-${workoutId}`).textContent = 
                `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
        }
    }
});
</script>
</body>
</html>
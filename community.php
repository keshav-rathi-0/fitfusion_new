<?php
// community.php - No database version
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Set default user for demonstration
$_SESSION['user_id'] = 1;

// Sample data - Will be replaced with database later
$current_user = [
    'id' => 1,
    'username' => 'You',
    'profile_image' => 'assets/images/default-avatar.jpg'
];

$trending_tags = [
    ['hashtag' => 'fitness', 'count' => 125],
    ['hashtag' => 'workout', 'count' => 98],
    ['hashtag' => 'nutrition', 'count' => 87],
    ['hashtag' => 'yoga', 'count' => 76],
    ['hashtag' => 'running', 'count' => 65]
];

$suggested_users = [
    [
        'id' => 2,
        'username' => 'FitnessGuru',
        'profile_image' => 'assets/images/avatar1.jpg',
        'bio' => 'Certified trainer',
        'follower_count' => 1250
    ],
    [
        'id' => 3,
        'username' => 'YogaMaster',
        'profile_image' => 'assets/images/avatar2.jpg',
        'bio' => 'Daily yoga flows',
        'follower_count' => 980
    ],
    [
        'id' => 4,
        'username' => 'NutritionPro',
        'profile_image' => 'assets/images/avatar3.jpg',
        'bio' => 'Healthy eating tips',
        'follower_count' => 875
    ]
];

$posts = [
    [
        'id' => 1,
        'user_id' => 2,
        'username' => 'FitnessGuru',
        'profile_image' => 'assets/images/avatar1.jpg',
        'content' => "Just completed an intense HIIT session! Who's joining me for the next one? #fitness #HIIT",
        'image' => 'assets/images/post1.jpg',
        'likes' => 24,
        'comment_count' => 5,
        'created_at' => '2025-04-10 09:30:00',
        'user_liked' => false,
        'comments' => [
            [
                'username' => 'YogaMaster',
                'profile_image' => 'assets/images/avatar2.jpg',
                'content' => 'Great work! HIIT is so effective!',
                'created_at' => '2025-04-10 10:15:00'
            ],
            [
                'username' => 'You',
                'profile_image' => 'assets/images/default-avatar.jpg',
                'content' => 'What routine did you follow?',
                'created_at' => '2025-04-10 11:20:00'
            ]
        ]
    ],
    [
        'id' => 2,
        'user_id' => 3,
        'username' => 'YogaMaster',
        'profile_image' => 'assets/images/avatar2.jpg',
        'content' => "Morning sunrise yoga session to start the day right. Remember to breathe deeply! #yoga #mindfulness",
        'image' => 'assets/images/post2.jpg',
        'likes' => 42,
        'comment_count' => 8,
        'created_at' => '2025-04-09 07:15:00',
        'user_liked' => true,
        'comments' => [
            [
                'username' => 'NutritionPro',
                'profile_image' => 'assets/images/avatar3.jpg',
                'content' => 'Beautiful setting for yoga!',
                'created_at' => '2025-04-09 08:30:00'
            ]
        ]
    ]
];

$events = [
    [
        'title' => 'City Marathon Challenge',
        'image' => 'assets/images/event1.jpg',
        'date' => 'May 15, 2025',
        'participants' => 235
    ],
    [
        'title' => 'Sunrise Beach Yoga',
        'image' => 'assets/images/event2.jpg',
        'date' => 'April 30, 2025',
        'participants' => 82
    ]
];

$challenges = [
    [
        'title' => '7-Day Water Challenge',
        'difficulty' => 'easy',
        'days_completed' => 5,
        'total_days' => 7,
        'participants' => 712
    ],
    [
        'title' => '10K Steps Daily',
        'difficulty' => 'medium',
        'days_completed' => 12,
        'total_days' => 30,
        'participants' => 1500
    ],
    [
        'title' => 'Full Month No Sugar',
        'difficulty' => 'hard',
        'days_completed' => 18,
        'total_days' => 30,
        'participants' => 436
    ]
];

// Function to format date/time
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    elseif ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    elseif ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    elseif ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    elseif ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    else return 'just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - FitFusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #00ffcc;
            --secondary: #ff00cc;
            --dark: #000;
            --light: #f8f9fa;
            --gray: #111;
            --card-bg: #1a1a1a;
            --text-light: rgba(255, 255, 255, 0.7);
            --text-lighter: rgba(255, 255, 255, 0.5);
            --border-color: rgba(255, 255, 255, 0.1);
        }
        
        body {
            background-color: var(--dark);
            color: white;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        /* Header */
        header {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            z-index: 100;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
            background: linear-gradient(90deg, var(--primary), var(--secondary));
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
            background: var(--secondary);
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
        
        /* Main Content */
        .community-main {
            padding-top: 100px;
            padding-bottom: 50px;
            min-height: 100vh;
        }
        
        .community-layout {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 30px;
        }
        
        .community-sidebar, .community-sidebar-right {
            position: sticky;
            top: 120px;
            height: fit-content;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        /* Sidebar Components */
        .trending-topics ul {
            list-style: none;
            padding: 0;
        }
        
        .trending-topics li {
            margin-bottom: 10px;
        }
        
        .trending-topics a {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            background: var(--card-bg);
            transition: all 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .trending-topics a:hover {
            background: rgba(0, 255, 204, 0.1);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.1);
        }
        
        .trending-topics .count {
            color: var(--primary);
            font-weight: 600;
        }
        
        .user-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.1);
        }
        
        .user-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .user-followers {
            font-size: 0.8rem;
            color: var(--text-lighter);
        }
        
        .follow-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: #000;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .follow-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }
        
        /* Feed Content */
        .community-feed {
            padding: 0 20px;
        }
        
        .feed-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border-radius: 50px;
            background: var(--card-bg);
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .filter-btn.active, .filter-btn:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.2);
        }
        
        /* Post Form */
        .post-form {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .post-form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .post-form-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .post-form h4 {
            font-weight: 600;
            margin: 0;
        }
        
        .post-form textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            color: white;
            resize: none;
            margin-bottom: 15px;
            min-height: 100px;
        }
        
        .post-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 255, 204, 0.2);
        }
        
        .post-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .post-form-tools {
            display: flex;
            gap: 15px;
        }
        
        .form-tool-btn {
            background: transparent;
            border: none;
            color: var(--primary);
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .form-tool-btn:hover {
            transform: scale(1.1);
            color: var(--secondary);
        }
        
        .post-submit-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .post-submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.3);
        }
        
        /* Post Card */
        .post-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 255, 204, 0.1);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .post-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .post-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .post-user-info h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .post-time {
            font-size: 0.8rem;
            color: var(--text-lighter);
        }
        
        .post-options {
            color: var(--text-lighter);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .post-options:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }
        
        .post-content {
            margin-bottom: 20px;
            line-height: 1.6;
            color: var(--text-light);
        }
        
        .post-content a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .post-image {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 500px;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .post-actions {
            display: flex;
            gap: 25px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .post-action {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .post-action:hover, .post-action.liked {
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        .post-action.liked i {
            font-weight: 900;
        }
        
        .post-comments {
            margin-top: 20px;
        }
        
        .comment {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .comment-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .comment-content {
            flex: 1;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        
        .comment-user {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        
        .comment-text {
            margin: 10px 0;
            line-height: 1.5;
            color: var(--text-light);
        }
        
        .comment-time {
            font-size: 0.8rem;
            color: var(--text-lighter);
        }
        
        .comment-form {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .comment-form input {
            flex: 1;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 12px 20px;
            color: white;
            transition: all 0.3s;
        }
        
        .comment-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 255, 204, 0.2);
        }
        
        .comment-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .comment-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }
        
        /* Event Cards */
        .event-card {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            height: 180px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 255, 204, 0.2);
        }
        
        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }
        
        .event-date {
            font-size: 0.9rem;
            color: var(--primary);
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .event-title {
            font-size: 1.1rem;
            margin: 0 0 5px 0;
            font-weight: 700;
        }
        
        .event-participants {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .event-participants i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        /* Challenge Cards */
        .challenge-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        
        .challenge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 255, 204, 0.1);
        }
        
        .challenge-card.easy {
            border-left: 5px solid var(--primary);
        }
        
        .challenge-card.medium {
            border-left: 5px solid #ffcc00;
        }
        
        .challenge-card.hard {
            border-left: 5px solid #ff0066;
        }
        
        .challenge-difficulty {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 50px;
        }
        
        .challenge-difficulty.easy {
            background: rgba(0, 255, 204, 0.2);
            color: var(--primary);
        }
        
        .challenge-difficulty.medium {
            background: rgba(255, 204, 0, 0.2);
            color: #ffcc00;
        }
        
        .challenge-difficulty.hard {
            background: rgba(255, 0, 102, 0.2);
            color: #ff0066;
        }
        
        .challenge-title {
            font-size: 1.1rem;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .challenge-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .challenge-progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        /* Footer */
        footer {
            background: #000;
            padding: 80px 0 30px;
            position: relative;
            overflow: hidden;
            border-top: 1px solid var(--border-color);
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
        
        .footer-container {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 50px;
            margin-bottom: 50px;
        }
        
        .footer-logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .footer-text {
            color: var(--text-light);
            line-height: 1.7;
            margin-bottom: 30px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            color: white;
            text-decoration: none;
        }
        
        .social-link:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transform: translateY(-5px);
            color: #000;
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 15px;
        }
        
        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .contact-info {
            color: var(--text-light);
        }
        
        .contact-info div {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-info i {
            color: var(--primary);
        }
        
        .copyright {
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
            text-align: center;
            color: var(--text-lighter);
            font-size: 0.9rem;
        }
        
        .copyright a {
            color: var(--text-lighter);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .copyright a:hover {
            color: var(--primary);
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .community-layout {
                grid-template-columns: 250px 1fr 250px;
                gap: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .community-layout {
                grid-template-columns: 1fr;
            }
            
            .community-sidebar, .community-sidebar-right {
                position: static;
                margin-bottom: 40px;
            }
            
            .community-feed {
                padding: 0;
            }
            
            .footer-container {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .feed-filters {
                flex-wrap: wrap;
            }
            
            .filter-btn {
                flex: 1;
                min-width: 120px;
                text-align: center;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .post-actions {
                gap: 15px;
            }
            
            .post-action span {
                display: none;
            }
        }
    </style>
</head>
<body>
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
                        <li><a href="community.php" class="active">Community</a></li>
                    </ul>
                </nav>
                <div class="header-icons">
                    <a href="profile.php" class="text-white"><i class="fas fa-user"></i></a>
                    <a href="cart.php" class="text-white cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <section class="community-main">
        <div class="container">
            <div class="community-layout">
                <!-- Left Sidebar -->
                <div class="community-sidebar">
                    <div class="trending-topics">
                        <h3 class="section-title">Trending Topics</h3>
                        <ul>
                            <?php foreach ($trending_tags as $tag): ?>
                                <li>
                                    <a href="#">
                                        <span>#<?php echo htmlspecialchars($tag['hashtag']); ?></span>
                                        <span class="count"><?php echo $tag['count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="suggested-users">
                        <h3 class="section-title">Suggested Users</h3>
                        <?php foreach ($suggested_users as $user): ?>
                            <div class="user-card">
                                <div class="user-avatar">
                                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <div class="user-followers"><?php echo $user['follower_count']; ?> followers</div>
                                </div>
                                <button class="follow-btn">Follow</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Main Feed -->
                <div class="community-feed">
                    <div class="feed-filters">
                        <a href="?filter=all" class="filter-btn active">All Posts</a>
                        <a href="?filter=trending" class="filter-btn">Trending</a>
                        <a href="?filter=following" class="filter-btn">Following</a>
                    </div>
                    
                    <form class="post-form">
                        <div class="post-form-header">
                            <div class="post-form-avatar">
                                <img src="<?php echo $current_user['profile_image']; ?>" alt="Your Avatar">
                            </div>
                            <h4>Share your fitness journey</h4>
                        </div>
                        <textarea placeholder="What's on your fitness mind?"></textarea>
                        <div class="post-form-actions">
                            <div class="post-form-tools">
                                <label for="post_image" class="form-tool-btn">
                                    <i class="fas fa-image"></i>
                                </label>
                                <input type="file" id="post_image" name="post_image" style="display: none;">
                                <button type="button" class="form-tool-btn">
                                    <i class="fas fa-video"></i>
                                </button>
                                <button type="button" class="form-tool-btn">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                            <button type="button" class="post-submit-btn">Post</button>
                        </div>
                    </form>
                    
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div class="post-user">
                                    <div class="post-avatar">
                                        <img src="<?php echo htmlspecialchars($post['profile_image']); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                                    </div>
                                    <div class="post-user-info">
                                        <h4><?php echo htmlspecialchars($post['username']); ?></h4>
                                        <div class="post-time"><?php echo time_elapsed_string($post['created_at']); ?></div>
                                    </div>
                                </div>
                                <div class="post-options">
                                    <i class="fas fa-ellipsis-v"></i>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </div>
                            
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
                            <?php endif; ?>
                            
                            <div class="post-actions">
                                <a href="#" class="post-action <?php echo $post['user_liked'] ? 'liked' : ''; ?>">
                                    <i class="<?php echo $post['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
                                    <span><?php echo $post['likes']; ?></span>
                                </a>
                                <div class="post-action">
                                    <i class="far fa-comment"></i>
                                    <span><?php echo $post['comment_count']; ?></span>
                                </div>
                                <div class="post-action">
                                    <i class="far fa-share-square"></i>
                                    <span>Share</span>
                                </div>
                            </div>
                            
                            <div class="post-comments">
                                <?php foreach ($post['comments'] as $comment): ?>
                                    <div class="comment">
                                        <div class="comment-avatar">
                                            <img src="<?php echo htmlspecialchars($comment['profile_image']); ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-user"><?php echo htmlspecialchars($comment['username']); ?></div>
                                            <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
                                            <div class="comment-time"><?php echo time_elapsed_string($comment['created_at']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <form class="comment-form">
                                    <div class="comment-avatar">
                                        <img src="<?php echo $current_user['profile_image']; ?>" alt="Your Avatar">
                                    </div>
                                    <input type="text" placeholder="Write a comment...">
                                    <button type="button" class="comment-submit"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Right Sidebar -->
                <div class="community-sidebar-right">
                    <h3 class="section-title">Upcoming Events</h3>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                            <div class="event-overlay">
                                <div class="event-date"><?php echo htmlspecialchars($event['date']); ?></div>
                                <h4 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                <div class="event-participants">
                                    <i class="fas fa-users"></i> <?php echo $event['participants']; ?> participants
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="challenges">
                        <h3 class="section-title">Active Challenges</h3>
                        <?php foreach ($challenges as $challenge): ?>
                            <div class="challenge-card <?php echo $challenge['difficulty']; ?>">
                                <div class="challenge-difficulty <?php echo $challenge['difficulty']; ?>"><?php echo ucfirst($challenge['difficulty']); ?></div>
                                <h4 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h4>
                                <div class="challenge-stats">
                                    <span><?php echo $challenge['days_completed']; ?>/<?php echo $challenge['total_days']; ?> days</span>
                                    <span><?php echo $challenge['participants']; ?> participants</span>
                                </div>
                                <div class="challenge-progress">
                                    <div class="progress-bar" style="width: <?php echo ($challenge['days_completed']/$challenge['total_days'])*100; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-container">
                <div>
                    <div class="footer-logo">FitFusion</div>
                    <p class="footer-text">Transforming lives through fitness, nutrition, and community support. Join our journey to a healthier, stronger you.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="workouts.php">Workouts</a></li>
                        <li><a href="nutrition.php">Nutrition</a></li>
                        <li><a href="community.php">Community</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="shipping.php">Shipping Policy</a></li>
                        <li><a href="returns.php">Returns Policy</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Contact Us</h4>
                    <div class="contact-info">
                        <div>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Fitness Avenue, Wellness City</span>
                        </div>
                        <div>
                            <i class="fas fa-phone-alt"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div>
                            <i class="fas fa-envelope"></i>
                            <span>support@fitfusion.com</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2025 FitFusion. All rights reserved. | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms of Service</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Like buttons
            document.querySelectorAll('.post-action').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const icon = this.querySelector('i');
                    const countSpan = this.querySelector('span');
                    
                    if (this.classList.contains('liked')) {
                        // Unlike
                        this.classList.remove('liked');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        countSpan.textContent = parseInt(countSpan.textContent) - 1;
                    } else {
                        // Like
                        this.classList.add('liked');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    }
                });
            });
            
            // Comment submission
            document.querySelectorAll('.comment-form').forEach(form => {
                const input = form.querySelector('input');
                const button = form.querySelector('button');
                
                button.addEventListener('click', function() {
                    if (input.value.trim() !== '') {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'comment';
                        commentDiv.innerHTML = `
                            <div class="comment-avatar">
                                <img src="${'<?php echo $current_user["profile_image"]; ?>'}" alt="Your Avatar">
                            </div>
                            <div class="comment-content">
                                <div class="comment-user">You</div>
                                <div class="comment-text">${input.value}</div>
                                <div class="comment-time">just now</div>
                            </div>
                        `;
                        
                        form.parentNode.insertBefore(commentDiv, form);
                        input.value = '';
                        
                        // Update comment count
                        const postCard = form.closest('.post-card');
                        const commentCount = postCard.querySelector('.post-action:nth-child(2) span');
                        commentCount.textContent = parseInt(commentCount.textContent) + 1;
                    }
                });
                
                // Allow pressing Enter to submit
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        button.click();
                    }
                });
            });
            
            // Post submission
            const postForm = document.querySelector('.post-form');
            const postTextarea = postForm.querySelector('textarea');
            const postButton = postForm.querySelector('.post-submit-btn');
            
            postButton.addEventListener('click', function() {
                if (postTextarea.value.trim() !== '') {
                    // In a real app, this would send to server
                    alert('Post submitted! (In a real app, this would be sent to the server)');
                    postTextarea.value = '';
                }
            });
            
            // Follow buttons
            document.querySelectorAll('.follow-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (this.textContent === 'Follow') {
                        this.textContent = 'Following';
                        this.style.background = '#333';
                        this.style.color = '#fff';
                    } else {
                        this.textContent = 'Follow';
                        this.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
                        this.style.color = '#000';
                    }
                });
            });
        });
    </script>
</body>
</html>
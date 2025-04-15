<?php
// process_checkout.php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize all inputs
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_STRING);
    $cardNumber = preg_replace('/\s+/', '', $_POST['card_number']);
    $expiryDate = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);
    $cvv = filter_input(INPUT_POST, 'cvv', FILTER_SANITIZE_STRING);

    // Validate all required fields
    $errors = [];
    
    if (empty($firstName) || !preg_match('/^[A-Za-z ]+$/', $firstName)) {
        $errors[] = "Invalid first name";
    }
    
    if (empty($lastName) || !preg_match('/^[A-Za-z ]+$/', $lastName)) {
        $errors[] = "Invalid last name";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address";
    }
    
    if (empty($cardNumber) || !preg_match('/^\d{16}$/', $cardNumber)) {
        $errors[] = "Invalid card number (must be 16 digits)";
    }
    
    if (empty($expiryDate) || !preg_match('/^\d{2}\/\d{2}$/', $expiryDate)) {
        $errors[] = "Invalid expiry date (use MM/YY format)";
    }
    
    if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
        $errors[] = "Invalid CVV (must be 3-4 digits)";
    }
    
    // Check for any validation errors
    if (!empty($errors)) {
        http_response_code(400);
        die(json_encode(["error" => "Validation failed", "details" => $errors]));
    }

    // Process the order if validation passes
    $_SESSION['cart'] = []; // Clear the cart
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true,
        "message" => "Order processed successfully",
        "order_id" => uniqid()
    ]);
} else {
    http_response_code(405);
    die(json_encode(["error" => "Method not allowed"]));
}
?>
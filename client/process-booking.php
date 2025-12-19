<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Database connection
$serverName = "localhost";
$connectionOptions = [
    "Database" => "trvlr",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
$required = ['hotel_id', 'hotel_name', 'hotel_price', 'check_in', 'check_out', 
             'guests', 'nights', 'total_price', 'card_holder_name', 'card_number'];

foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Get user email from session
$userEmail = $_SESSION['user']['email'];

// Store only last 4 digits of card
$cardLastFour = substr(str_replace(' ', '', $data['card_number']), -4);

// Prepare SQL
$sql = "INSERT INTO BOOKINGS (USER_EMAIL, HOTEL_ID, HOTEL_NAME, HOTEL_ADDRESS, HOTEL_IMAGE, 
        HOTEL_RATING, HOTEL_PRICE, CHECK_IN, CHECK_OUT, GUESTS, TOTAL_NIGHTS, TOTAL_PRICE, 
        CARD_HOLDER_NAME, CARD_LAST_FOUR, BILLING_ADDRESS) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$params = [
    $userEmail,
    $data['hotel_id'],
    $data['hotel_name'],
    $data['hotel_address'] ?? '',
    $data['hotel_image'] ?? '',
    $data['hotel_rating'] ?? null,
    $data['hotel_price'],
    $data['check_in'],
    $data['check_out'],
    $data['guests'],
    $data['nights'],
    $data['total_price'],
    $data['card_holder_name'],
    $cardLastFour,
    $data['billing_address'] ?? ''
];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Booking failed', 'error' => sqlsrv_errors()]);
} else {
    echo json_encode(['success' => true, 'message' => 'Booking confirmed']);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

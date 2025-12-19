<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display in output
ini_set('log_errors', 1);

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$serverName = "waphayll\\SQLEXPRESS";
$connectionOptions = ["Database" => "trvlr", "Uid" => "", "PWD" => ""];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'details' => $errors]);
    exit;
}

if (isset($_POST['save_booking'])) {
    $email = $_SESSION['user']['email'];
    $hotelId = $_POST['hotel_id'];
    $hotelName = $_POST['hotel_name'];
    $hotelImage = $_POST['hotel_image'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $adults = intval($_POST['adults']);
    $price = floatval($_POST['price']);
    $currency = $_POST['currency'];
    $paymentMethod = $_POST['payment_method'];
    $status = $_POST['status'];
    
    // Validate required fields
    if (empty($hotelId) || empty($hotelName) || empty($checkin) || empty($checkout) || empty($paymentMethod)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // Convert dates to SQL Server format (YYYY-MM-DD is fine)
    $checkinDate = date('Y-m-d', strtotime($checkin));
    $checkoutDate = date('Y-m-d', strtotime($checkout));
    
    $sql = "INSERT INTO BOOKINGS (USER_EMAIL, HOTEL_ID, HOTEL_NAME, HOTEL_IMAGE, CHECKIN, CHECKOUT, ADULTS, PRICE, CURRENCY, PAYMENT_METHOD, STATUS, CREATED_AT) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
    
    $params = array(
        $email, 
        $hotelId, 
        $hotelName, 
        $hotelImage, 
        $checkinDate, 
        $checkoutDate, 
        $adults, 
        $price, 
        $currency, 
        $paymentMethod, 
        $status
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        echo json_encode([
            'success' => false, 
            'message' => 'Database insert error',
            'sql_errors' => $errors,
            'data_sent' => [
                'email' => $email,
                'hotel_id' => $hotelId,
                'checkin' => $checkinDate,
                'checkout' => $checkoutDate,
                'adults' => $adults,
                'price' => $price
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Booking saved successfully']);
        sqlsrv_free_stmt($stmt);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

sqlsrv_close($conn);
?>

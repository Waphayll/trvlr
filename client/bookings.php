<?php
session_start();

// Database connection
$serverName = "waphayll\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "trvlr",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Connection failed");
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$email = $_SESSION['user']['email'];
$successMessage = "";
$errorMessage = "";

// Handle delete booking
if (isset($_GET['delete'])) {
    $bookingId = $_GET['delete'];
    $sql_delete = "DELETE FROM BOOKINGS WHERE BOOKING_ID = ? AND USER_EMAIL = ?";
    $params = [$bookingId, $email];
    $stmt = sqlsrv_query($conn, $sql_delete, $params);
    
    if ($stmt) {
        $successMessage = "Booking cancelled successfully!";
        sqlsrv_free_stmt($stmt);
    } else {
        $errorMessage = "Failed to cancel booking.";
    }
}

// Handle update booking
if (isset($_POST['update_booking'])) {
    $bookingId = $_POST['booking_id'];
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $guests = $_POST['guests'];
    
    // Calculate new nights and total
    $date1 = new DateTime($checkIn);
    $date2 = new DateTime($checkOut);
    $nights = $date1->diff($date2)->days;
    
    // Get hotel price
    $sql_price = "SELECT HOTEL_PRICE FROM BOOKINGS WHERE BOOKING_ID = ?";
    $stmt_price = sqlsrv_query($conn, $sql_price, [$bookingId]);
    $priceData = sqlsrv_fetch_array($stmt_price, SQLSRV_FETCH_ASSOC);
    $totalPrice = $priceData['HOTEL_PRICE'] * $nights;
    
    $sql_update = "UPDATE BOOKINGS SET CHECK_IN = ?, CHECK_OUT = ?, GUESTS = ?, 
                   TOTAL_NIGHTS = ?, TOTAL_PRICE = ? WHERE BOOKING_ID = ? AND USER_EMAIL = ?";
    $params = [$checkIn, $checkOut, $guests, $nights, $totalPrice, $bookingId, $email];
    $stmt = sqlsrv_query($conn, $sql_update, $params);
    
    if ($stmt) {
        $successMessage = "Booking updated successfully!";
        sqlsrv_free_stmt($stmt);
    } else {
        $errorMessage = "Failed to update booking.";
    }
}

// Get all bookings for user
$sql = "SELECT * FROM BOOKINGS WHERE USER_EMAIL = ? ORDER BY BOOKING_DATE DESC";
$stmt = sqlsrv_query($conn, $sql, [$email]);
$bookings = [];

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $bookings[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
    <body>
    <header>
        <nav class="navbar bg-body-tertiary d-flex fixed-top">
        <div class="container-fluid"></div>
        <div class="container-fluid justify-content-center">
            <a class="navbar-brand mx-4" style="min-width: 100px;" href="index.php">
                <img src="../images/trvlr.svg" alt="trvlr logo" width="60" height="60" class="d-inline-block align-text-top">
            </a>  
            
            <div class="d-flex mx-auto justify-content-center align-items-center">
                <a class="nav-link mx-4" href="#stay">
                    <i class="bi bi-house-door mx-auto"></i>Stay</a>
                <a class="nav-link mx-4" href="#explore">
                    <i class="bi bi-compass mx-auto"></i>Explore</a>
                <a class="nav-link mx-4" href="bookings.php">
                    <i class="bi bi-bookmark mx-auto"></i>Bookings</a>
                <a class="nav-link mx-4" href="#hero">
                    <i class="bi bi-search mx-auto"></i>Search</a>
            </div>          
            
            <div class="dropdown">
              <button class="btn btn-light dropdown-toggle" type="button" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountMenu">
              <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="index.html">Sign out</a></li>
              </ul>
            </div>
        </div>
    </nav>
    </header>

    <!-- Main Content -->
    <div class="container" style="margin-top: 100px;">
        <h1 class="mb-4"><i class="bi bi-calendar-check"></i> My Bookings</h1>

        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= $successMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?= $errorMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <h3 class="mt-3">No Bookings Yet</h3>
                <p class="text-muted">Start exploring amazing destinations and book your stay!</p>
                <a href="index.php" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-search"></i> Explore Hotels
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($booking['HOTEL_IMAGE']): ?>
                                <img src="<?= htmlspecialchars($booking['HOTEL_IMAGE']) ?>" 
                                     class="card-img-top" alt="Hotel" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($booking['HOTEL_NAME']) ?></h5>
                                
                                <?php if ($booking['HOTEL_RATING']): ?>
                                    <span class="badge bg-warning text-dark mb-2">
                                        <i class="bi bi-star-fill"></i> <?= number_format($booking['HOTEL_RATING'], 1) ?>
                                    </span>
                                <?php endif; ?>
                                
                                <p class="card-text small text-muted mb-3">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($booking['HOTEL_ADDRESS']) ?>
                                </p>
                                
                                <div class="border-top pt-3">
                                    <div class="row g-2 small mb-2">
                                        <div class="col-6">
                                            <strong>Check-in:</strong><br>
                                            <?= $booking['CHECK_IN']->format('M d, Y') ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Check-out:</strong><br>
                                            <?= $booking['CHECK_OUT']->format('M d, Y') ?>
                                        </div>
                                    </div>
                                    <div class="row g-2 small mb-2">
                                        <div class="col-6">
                                            <strong>Guests:</strong> <?= $booking['GUESTS'] ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Nights:</strong> <?= $booking['TOTAL_NIGHTS'] ?>
                                        </div>
                                    </div>
                                    <p class="mb-2">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-success"><?= $booking['STATUS'] ?></span>
                                    </p>
                                </div>
                                
                                <div class="border-top pt-3">
                                    <h5 class="text-primary mb-1">₱<?= number_format($booking['TOTAL_PRICE'], 2) ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-credit-card"></i> Card ending in <?= $booking['CARD_LAST_FOUR'] ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white border-top">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick='showEditModal(<?= json_encode($booking, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="bi bi-pencil"></i> Edit Booking
                                    </button>
                                    <a href="bookings.php?delete=<?= $booking['BOOKING_ID'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="bi bi-trash"></i> Cancel Booking
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <img src="../images/trvlr.svg" alt="trvlr logo" width="50" height="50" class="mb-2">
                    <p class="text-muted">Your gateway to exploring the beautiful Philippines.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="text-dark">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#hero" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="#stay" class="text-muted text-decoration-none">Stay</a></li>
                        <li><a href="#explore" class="text-muted text-decoration-none">Explore</a></li>
                        <li><a href="bookings.php" class="text-muted text-decoration-none">My Bookings</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="text-dark">Contact</h6>
                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope"></i> support@trvlr.ph
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-telephone"></i> +63 123 456 7890
                    </p>
                </div>
            </div>
            <hr>
            <div class="text-center text-muted">
                <p class="mb-0">&copy; 2025 trvlr. All rights reserved.</p>
            </div>
        </div>
    </footer>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showEditModal(booking) {
            document.getElementById('editBookingId').value = booking.BOOKING_ID;
            
            // Format dates properly
            const checkInDate = new Date(booking.CHECK_IN.date);
            const checkOutDate = new Date(booking.CHECK_OUT.date);
            
            document.getElementById('editCheckIn').value = checkInDate.toISOString().split('T')[0];
            document.getElementById('editCheckOut').value = checkOutDate.toISOString().split('T')[0];
            document.getElementById('editGuests').value = booking.GUESTS;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>

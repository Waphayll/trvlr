<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$searchResults = null;
$errorMessage = "";

// Handle search form
if (isset($_POST['search_hotels'])) {
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $adults = $_POST['adults'] ?? 2; // Fixed: added default value
    $city = $_POST['city'];
    $countryCode = $_POST['countryCode'];
    $environment = 'sandbox';
    
    // Call Node.js API
    $url = "http://localhost:3000/search-hotels?" . http_build_query([
        'checkin' => $checkin,
        'checkout' => $checkout,
        'adults' => $adults,
        'city' => $city,
        'countryCode' => $countryCode,
        'environment' => $environment
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $searchResults = json_decode($response, true);
    } else {
        $errorMessage = "Error searching hotels. Please try again.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Hotels - trvlr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="pt-5">
    <header>
        <nav class="navbar bg-body-tertiary d-flex fixed-top">
            <div class="container-fluid justify-content-center">
                <a class="navbar-brand mx-4" href="index.php">
                    <img src="../images/trvlr.svg" alt="trvlr logo" width="60" height="60">
                </a>  
                
                <div class="d-flex mx-auto justify-content-center align-items-center">
                    <a class="nav-link mx-4" href="index.php#stay"><i class="bi bi-house-door"></i> Stay</a>
                    <a class="nav-link mx-4" href="index.php#explore"><i class="bi bi-compass"></i> Explore</a>
                    <a class="nav-link mx-4" href="bookings.php"><i class="bi bi-bookmark"></i> Bookings</a>
                </div>          
                
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.html">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container my-5">
        <h1 class="mb-4">Search Hotels</h1>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="manila" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="countryCode" value="PH" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Check-in</label>
                            <input type="date" class="form-control" name="checkin" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Check-out</label>
                            <input type="date" class="form-control" name="checkout" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Adults</label>
                            <input type="number" class="form-control" name="adults" value="2" min="1" required>
                        </div>
                    </div>
                    <button type="submit" name="search_hotels" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search Hotels
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Search Results -->
        <?php if ($searchResults && isset($searchResults['rates'])): ?>
            <h2>Available Hotels (<?php echo count($searchResults['rates']); ?> found)</h2>
            <div class="row">
                <?php foreach ($searchResults['rates'] as $rate): ?>
                    <?php if (isset($rate['hotel'])): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($rate['hotel']['name']); ?></h5>
                                <p class="card-text">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($rate['hotel']['address'] ?? 'Address not available'); ?>
                                </p>
                                
                                <?php if (isset($rate['roomTypes'][0]['rates'][0])): ?>
                                    <?php 
                                    $minRate = $rate['roomTypes'][0]['rates'][0];
                                    $price = $minRate['retailRate']['total'][0]['amount'];
                                    $currency = $minRate['retailRate']['total'][0]['currency'];
                                    ?>
                                    
                                    <div class="mt-3">
                                        <h6>From: $<?php echo number_format($price, 2); ?> <?php echo $currency; ?></h6>
                                        <p class="mb-1"><small>Board: <?php echo $minRate['boardName']; ?></small></p>
                                        <p class="mb-1"><small>Max Occupancy: <?php echo $minRate['maxOccupancy']; ?></small></p>
                                        <span class="badge <?php echo $minRate['cancellationPolicies']['refundableTag'] == 'RFN' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $minRate['cancellationPolicies']['refundableTag'] == 'RFN' ? 'Refundable' : 'Non-refundable'; ?>
                                        </span>
                                    </div>
                                    
                                    <a href="hotel-details.php?hotelId=<?php echo $rate['hotelId']; ?>&checkin=<?php echo $_POST['checkin']; ?>&checkout=<?php echo $_POST['checkout']; ?>&adults=<?php echo $_POST['adults']; ?>" 
                                       class="btn btn-primary mt-3">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

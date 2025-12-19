<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}
?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel - Explore the Philippines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <!-- Header Navigation -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand" href="index.php">
                    <img src="../images/trvlr.svg" alt="trvlr logo" width="50" height="50" class="d-inline-block align-text-top">
                </a>
                
                <!-- Navigation Links -->
                <div class="navbar-nav mx-auto">
                    <a class="nav-link mx-3" href="#stay">
                        <i class="bi bi-house-door"></i> Stay
                    </a>
                    <a class="nav-link mx-3" href="#explore">
                        <i class="bi bi-compass"></i> Explore
                    </a>
                    <a class="nav-link mx-3" href="bookings.php">
                        <i class="bi bi-bookmark"></i> Bookings
                    </a>
                    <a class="nav-link mx-3" href="#hero">
                        <i class="bi bi-search"></i> Search
                    </a>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountMenu">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="index.html"><i class="bi bi-box-arrow-right"></i> Sign out</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Travel</h1>
            <p class="lead mb-4">Here, There, And Everywhere</p>
            
            <!-- Search Form -->
            <form id="searchForm" class="row g-3 justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <input type="text" class="form-control form-control-lg" id="cityInput" 
                           placeholder="Where do you want to go?" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-light btn-lg">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Alert Container -->
    <div class="container mt-4">
        <div id="alertContainer"></div>
    </div>

    <!-- Stay Section -->
    <section id="stay" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Stay in <span id="cityName">Manila</span></h2>
                <p class="lead text-muted">Find the perfect accommodation for your trip.</p>
            </div>

            <!-- Hotel Cards Row -->
            <div class="row" id="stayRowMain">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading hotels...</p>
                </div>
            </div>

            <!-- More Hotels Row (Hidden Initially) -->
            <div class="row" id="stayRowMore" style="display: none;"></div>

            <!-- Show More Button -->
            <div id="showMoreContainer" class="text-center mt-4" style="display: none;">
                <button class="btn btn-outline-primary" id="showMoreBtn">
                    <i class="bi bi-chevron-down"></i> Show More
                </button>
            </div>
        </div>
    </section>

  <!-- Explore Section -->
    <section id="explore" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Explore the Philippines</h2>
                <p class="lead text-muted">Discover amazing destinations across the archipelago</p>
            </div>

            <!-- El Nido Card -->
            <div class="card mb-4 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="../images/elnido.jpg" 
                             alt="El Nido, Palawan" 
                             class="img-fluid" 
                             style="height: 100%; min-height: 350px; object-fit: cover; border-radius: 0.375rem 0 0 0.375rem;">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-success mb-2"><i class="bi bi-geo-alt-fill me-1"></i>Palawan</span>
                                    <h3 class="fw-bold mb-2">El Nido Paradise</h3>
                                </div>
                                <div class="text-end">
                                    <div class="text-warning mb-1">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <small class="text-muted">Top Destination</small>
                                </div>
                            </div>
                            <p class="text-muted mb-4">Experience pristine beaches, crystal-clear lagoons, and dramatic limestone cliffs. El Nido is often called the Philippines' last frontier paradise with world-class diving spots and hidden lagoons perfect for adventurers and relaxation seekers.</p>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-compass text-primary me-2"></i>Top Attractions</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Big & Small Lagoon</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Island Hopping Tours</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Nacpan Beach</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-activity text-primary me-2"></i>Activities</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Scuba Diving</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Kayaking</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Snorkeling</li>
                                    </ul>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-lg px-4" onclick="searchDestination('El Nido')">
                                <i class="bi bi-search me-2"></i>Book Hotels in El Nido
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boracay Card -->
            <div class="card mb-4 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-7 order-md-1">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-danger mb-2"><i class="bi bi-geo-alt-fill me-1"></i>Aklan</span>
                                    <h3 class="fw-bold mb-2">White Beach Haven</h3>
                                </div>
                                <div class="text-end">
                                    <div class="text-warning mb-1">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </div>
                                    <small class="text-muted">Beach Paradise</small>
                                </div>
                            </div>
                            <p class="text-muted mb-4">Famous for its powdery white sand beaches and vibrant nightlife. Boracay offers a perfect blend of relaxation and adventure with water sports, beach parties, and stunning sunsets that paint the sky in brilliant colors.</p>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-compass text-primary me-2"></i>Top Attractions</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>White Beach</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Puka Shell Beach</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Willy's Rock</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-activity text-primary me-2"></i>Activities</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Kiteboarding</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Parasailing</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Sunset Sailing</li>
                                    </ul>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-lg px-4" onclick="searchDestination('Boracay')">
                                <i class="bi bi-search me-2"></i>Book Hotels in Boracay
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5 order-md-2">
                        <img src="../images/boracay.jpg" 
                             alt="Boracay Island" 
                             class="img-fluid" 
                             style="height: 100%; min-height: 350px; object-fit: cover; border-radius: 0 0.375rem 0.375rem 0;">
                    </div>
                </div>
            </div>

            <!-- Cebu Card -->
            <div class="card mb-4 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="../images/cebu.jpg" 
                             alt="Cebu City" 
                             class="img-fluid" 
                             style="height: 100%; min-height: 350px; object-fit: cover; border-radius: 0.375rem 0 0 0.375rem;">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-info mb-2"><i class="bi bi-geo-alt-fill me-1"></i>Cebu</span>
                                    <h3 class="fw-bold mb-2">Queen City of the South</h3>
                                </div>
                                <div class="text-end">
                                    <div class="text-warning mb-1">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <small class="text-muted">Cultural Hub</small>
                                </div>
                            </div>
                            <p class="text-muted mb-4">Dive into vibrant culture, historical landmarks, and breathtaking waterfalls. Cebu combines urban excitement with natural wonders like Kawasan Falls and world-famous whale shark encounters in Oslob.</p>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-compass text-primary me-2"></i>Top Attractions</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Kawasan Falls</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Whale Shark Watching</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Magellan's Cross</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-activity text-primary me-2"></i>Activities</h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Canyoneering</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Island Hopping</li>
                                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Historical Tours</li>
                                    </ul>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-lg px-4" onclick="searchDestination('Cebu')">
                                <i class="bi bi-search me-2"></i>Book Hotels in Cebu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Your Stay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hotel Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <img id="modalHotelImage" src="" class="img-fluid rounded" alt="Hotel" style="width: 100%; height: 250px; object-fit: cover;">
                        </div>
                        <div class="col-md-7">
                            <h4 id="modalHotelName">Hotel Name</h4>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> <span id="modalHotelAddress">Address</span>
                            </p>
                            <p class="mb-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> <span id="modalHotelRating">N/A</span>
                                </span>
                            </p>
                            <p id="modalHotelDescription" class="mb-3">Hotel description</p>
                            <div class="bg-light p-3 rounded">
                                <h5 class="text-primary mb-0" id="modalHotelPrice">₱0</h5>
                                <small class="text-muted">per night</small>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Booking Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Check-in:</strong> <span id="modalCheckIn">-</span></p>
                                    <p class="mb-2"><strong>Check-out:</strong> <span id="modalCheckOut">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Guests:</strong> <span id="modalGuests">-</span></p>
                                    <p class="mb-2"><strong>Nights:</strong> <span id="modalNights">-</span></p>
                                </div>
                            </div>
                            <hr>
                            <h5 class="text-end">Total: <span class="text-primary" id="modalTotalPrice">₱0</span></h5>
                        </div>
                    </div>

                    <!-- Billing Form -->
                    <form id="bookingForm">
                        <h6 class="mb-3">Payment Information</h6>
                        
                        <div class="mb-3">
                            <label for="cardHolderName" class="form-label">Cardholder Name *</label>
                            <input type="text" class="form-control" id="cardHolderName" name="card_holder_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Card Number *</label>
                            <input type="text" class="form-control" id="cardNumber" name="card_number" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expiryDate" class="form-label">Expiry Date *</label>
                                <input type="text" class="form-control" id="expiryDate" name="expiry_date" 
                                       placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cvv" class="form-label">CVV *</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                       placeholder="123" maxlength="3" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="billingAddress" class="form-label">Billing Address *</label>
                            <textarea class="form-control" id="billingAddress" name="billing_address" 
                                      rows="2" required></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
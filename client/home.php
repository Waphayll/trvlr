<?php
require_once 'config.php'; // includes session_start and DB connection

// Make sure user is logged in
require_login();

// Optional: you can fetch user info from session
$firstName = htmlspecialchars($_SESSION['user']['first_name'] ?? $_SESSION['user']['firstname'] ?? 'Traveler', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>trvlr - Discover Your Next Adventure</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            padding-top: 80px;
        }
        .explore-image-horizontal {
            background-size: cover;
            background-position: center;
        }
        .shadow-custom {
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        .toggle-btn {
            border: none;
            background: none;
        }
    </style>
</head>
<body class="pt-5">

<!-- Navbar -->
<header>
    <nav class="navbar bg-body-tertiary d-flex fixed-top">
        <div class="container-fluid justify-content-center">
            <a class="navbar-brand mx-4" style="min-width: 100px;" href="home.php">
                <img src="./images/trvlr.svg" alt="trvlr logo" width="60" height="60"
                     class="d-inline-block align-text-top">
            </a>
            <div class="d-flex mx-auto justify-content-center align-items-center">
                <a class="nav-link mx-4" href="#stay"><i class="bi bi-house-door mx-auto"></i>Stay</a>
                <a class="nav-link mx-4" href="#explore"><i class="bi bi-compass mx-auto"></i>Explore</a>
                <a class="nav-link mx-4" href="bookings.php"><i class="bi bi-bookmark mx-auto"></i>Bookings</a>
                <a class="nav-link mx-4" href="#hero"><i class="bi bi-search mx-auto"></i>Search</a>
            </div>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        style="min-width: 100px;">
                    <i class="bi bi-person-circle me-2"></i>
                    <?php echo $firstName; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="bookings.php">
                        <i class="bi bi-bookmark me-2"></i>My Bookings</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main>
    <!-- Hero Section -->
    <section id="hero" class="py-5 text-center container">
        <div class="row py-lg-5">
            <div class="col-lg-6 col-md-8 mx-auto">
                <h1 class="display-4 fw-bold">Travel</h1>
                <h2 class="fw-light">Here, There, And Everywhere</h2>
                <p class="lead text-muted mt-3">
                    Find the best places to stay in the Philippines. Search hotels, compare prices, and book your next trip.
                </p>

                <form id="searchForm" class="mt-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control form-control-lg" id="searchCity"
                               placeholder="Search a city e.g., manila, cebu, boracay" value="manila" required>
                        <button type="submit" class="btn btn-primary btn-lg">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Stay Section (hotels from Node.js API) -->
    <section id="stay" class="container mb-5 fade-in-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 fw-bold">Stay in <span id="cityName">Manila</span></h2>
                <p class="text-muted mb-0">Find the perfect accommodation for your trip.</p>
            </div>
            <button class="toggle-btn" type="button" id="toggleMoreHotels" style="display: none;">
                <i class="bi bi-three-dots"></i>
            </button>
        </div>

        <div class="row" id="stayRowMain">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading hotels...</p>
            </div>
        </div>

        <div id="stayRowMore" class="row mt-3" style="display:none;"></div>
    </section>

    <!-- Explore Philippines Section -->
    <section id="explore" class="container mb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Explore the Philippines</h2>
            <p class="text-muted">Discover amazing destinations across the archipelago.</p>
        </div>

        <!-- Palawan -->
        <div class="card mb-4 shadow-custom explore-card-horizontal">
            <div class="row g-0">
                <div class="col-md-5">
                    <div class="explore-image-horizontal"
                         style="background-image: url('https://images.unsplash.com/photo-1621655458802-74fa2d1d6e8c?w=800');
                                height: 100%; min-height: 350px; border-radius: 12px 0 0 12px;">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-body p-5">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary mb-2">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Palawan
                                </span>
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
                        <p class="text-muted mb-4">
                            Experience pristine beaches, crystal-clear lagoons, and dramatic limestone cliffs.
                            El Nido is often called the Philippines' last frontier paradise with world-class diving spots.
                        </p>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-compass text-primary me-2"></i>Top Attractions
                                </h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Big & Small Lagoon</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Island Hopping Tours</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Nacpan Beach</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-activity text-primary me-2"></i>Activities
                                </h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Scuba Diving</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Kayaking</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Snorkeling</li>
                                </ul>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-lg px-4" onclick="searchDestination('palawan')">
                            <i class="bi bi-search me-2"></i>Book Hotels in Palawan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- You can keep your existing Boracay and Cebu cards here (same structure as in your original file) -->

    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="fw-bold mb-3">trvlr</h5>
                    <p class="small">Discover your next adventure with us. Travel here, there, and everywhere.</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#stay" class="text-light text-decoration-none">
                            <i class="bi bi-house-door me-2"></i>Stay</a></li>
                        <li class="mb-2"><a href="#explore" class="text-light text-decoration-none">
                            <i class="bi bi-compass me-2"></i>Explore</a></li>
                        <li class="mb-2"><a href="bookings.php" class="text-light text-decoration-none">
                            <i class="bi bi-bookmark me-2"></i>Bookings</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Follow Us</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p class="small mb-0">
                    &copy; 2025 trvlr. All rights reserved. Made with
                    <i class="bi bi-heart-fill text-danger"></i> in the Philippines.
                </p>
            </div>
        </div>
    </footer>

    <!-- Hotel Modal (kept same structure as your original so app.js works) -->
    <div class="modal fade" id="hotelModal" tabindex="-1" aria-labelledby="hotelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalHotelName">Hotel Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="modalHotelImage"
                                 style="height: 300px; background-size: cover; background-position: center;
                                        border-radius: 12px; background-color: #f0f0f0;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p id="modalHotelStars" class="mb-2"></p>
                            <p class="mb-2">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span id="modalHotelAddress"></span>
                            </p>
                            <p id="modalHotelDescription" class="text-muted small"></p>
                            <h4 class="text-primary mt-3 fw-bold">
                                <span id="modalHotelPrice"></span>/night
                            </h4>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-3">Booking Details</h5>
                    <form id="bookingForm">
                        <input type="hidden" id="bookingHotelId" name="hotel_id">
                        <input type="hidden" id="bookingHotelName" name="hotel_name">
                        <input type="hidden" id="bookingPrice" name="price">
                        <input type="hidden" id="bookingCurrency" name="currency">
                        <input type="hidden" id="bookingHotelImage" name="hotel_image">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check me-2"></i>Check-in Date
                                </label>
                                <input type="date" class="form-control" id="bookingCheckin" name="checkin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x me-2"></i>Check-out Date
                                </label>
                                <input type="date" class="form-control" id="bookingCheckout" name="checkout" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-people me-2"></i>Number of Adults
                            </label>
                            <input type="number" class="form-control" id="bookingAdults" name="adults"
                                   min="1" max="10" value="2" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-credit-card me-2"></i>Payment Method
                            </label>
                            <select class="form-select" id="bookingPayment" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Cash">Cash on Arrival</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">
                            <i class="bi bi-check-circle me-2"></i>Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXKBmnVDxMD2scQbITxI"
        crossorigin="anonymous"></script>
<script src="app.js"></script>
</body>
</html>

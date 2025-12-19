<?php
session_start();

$serverName = "waphayll\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "trvlr",
    "Uid" => "",
    "PWD" => ""
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Database connection failed.");
}

// Only run login logic if email and password are provided
if (!empty($_POST['loginemail']) && !empty($_POST['loginpassword'])) {
    $email = $_POST['loginemail'];
    $password = $_POST['loginpassword'];

    $sql_check = "SELECT FIRST_NAME, LAST_NAME, PASSWORD FROM USERS WHERE EMAIL = ?";
    $params_check = array($email);
    $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);
    $user = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);

    if (!$user || $user['PASSWORD'] !== $password) {
        echo "Wrong email or password.";
        exit;
    }
    
    $_SESSION['user'] = [
        'first_name' => $user['FIRST_NAME'],
        'last_name' => $user['LAST_NAME'],
        'email' => $email
    ];
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>trvlr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  </head>
    <body class="pt-5">
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
    
    <main>
        <!-- Hero Section -->
      <section id="hero" class="py-5 text-center container">
        <div class="row py-lg-5">
          <div class="col-lg-6 col-md-8 mx-auto">
            <h1 class="display-4">Travel</h1>
            <h2>Here, There, And Everywhere</h2>
            <form id="searchForm">
              <div class="input-group mb-3 mt-4">
                <span class="input-group-text">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="searchCity" placeholder="Search a city (e.g., manila, cebu, boracay)" value="manila" required>
                <button type="submit" class="btn btn-primary">Search</button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Stay Section -->
      <section id="stay" class="container mb-5">
        <h2 class="mb-3">Stay in <span id="cityName">Manila</span></h2>
        <p>Find the perfect accommodation for your trip.</p>
        
        <div id="alertContainer"></div>
        
        <div class="row" id="stayRowMain">
          <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading hotels...</p>
          </div>
        </div>
        
        <div class="collapse" id="stayMore">
          <div class="row mt-3" id="stayRowMore"></div>
        </div>
        
        <div class="text-center mb-2" id="showMoreContainer" style="display: none;">
          <button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#stayMore" id="stayToggleBtn">
            Show More
          </button>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="app.js"></script>

  </body>
</html>



    
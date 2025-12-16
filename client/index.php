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

        <!--Featured/Explore Section-->
        <section id="explore" class="container mb-5">
            <div class="card">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-body bg-secondary bg-opacity-25" id="exploreImage" style="height: 500px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-body" id="exploreContent">
                            <h3>Featured</h3>
                            <p>Search a city to explore places</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>trvlr</h5>
                    <p class="small">Discover your next adventure with us. Travel here, there, and everywhere.</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#stay" class="text-light text-decoration-none">Stay</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-instagram fs-4"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-twitter fs-4"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3 bg-light">
            <div class="text-center">
                <p class="small mb-0">&copy; 2025 trvlr. All rights reserved.</p>
            </div>
        </div>
    </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="app.js"></script>
  </body>
</html>

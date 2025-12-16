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
    die("Connection failed");
}

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$email = $_SESSION['user']['email'];
$successMessage = "";
$errorMessage = "";

// Handle profile update
if (isset($_POST['update_profile'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $sql_update = "UPDATE USERS SET FIRST_NAME = ?, LAST_NAME = ?, PHONE = ?, ADDRESS = ? WHERE EMAIL = ?";
    $params = array($firstName, $lastName, $phone, $address, $email);
    $stmt = sqlsrv_query($conn, $sql_update, $params);
    
    if ($stmt === false) {
        $errorMessage = "Update failed";
    } else {
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $successMessage = "Profile updated successfully!";
        sqlsrv_free_stmt($stmt);
    }
}

// Get user data
$sql_user = "SELECT * FROM USERS WHERE EMAIL = ?";
$params_user = array($email);
$stmt_user = sqlsrv_query($conn, $sql_user, $params_user);
$userData = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt_user);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>trvlr Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
        <h1 class="mb-4">Traveler Profile</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($userData['FIRST_NAME']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($userData['LAST_NAME']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($userData['PHONE'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($userData['ADDRESS'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Current Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong><br><?php echo htmlspecialchars($userData['FIRST_NAME'] . ' ' . $userData['LAST_NAME']); ?></p>
                        <p><strong>Email:</strong><br><?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Phone:</strong><br><?php echo htmlspecialchars($userData['PHONE'] ?: 'Not set'); ?></p>
                        <p><strong>Address:</strong><br><?php echo htmlspecialchars($userData['ADDRESS'] ?: 'Not set'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

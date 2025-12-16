<?php
$serverName = "waphayll\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "trvlr",
    "Uid" => "",
    "PWD" => ""
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    header("Location: index.html?error=connection");
    exit;
}

// Read form fields
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic required field check
if ($first_name === '' || $last_name === '' || $email === '' || $password === '' || $confirm_password === '') {
    sqlsrv_close($conn);
    header("Location: signup.html?error=required");
    exit;
}

// Password match check
if ($password !== $confirm_password) {
    sqlsrv_close($conn);
    header("Location: signup.html?error=password_mismatch");
    exit;
}

$sql_check = "SELECT EMAIL FROM USERS WHERE EMAIL = ?";
$params_check = array($email);
$stmt_check = sqlsrv_query($conn, $sql_check, $params_check);


if ($stmt_check === false) {
    die(print_r(sqlsrv_errors(), true));
}
if (sqlsrv_fetch_array($stmt_check)) {
    die("Error: Email already exists.");
}

// Insert new user
$sql_insert = "INSERT INTO USERS (FIRST_NAME, LAST_NAME, EMAIL, PASSWORD) VALUES ('$first_name', '$last_name', '$email', '$password')";
$insert_result = sqlsrv_query($conn, $sql_insert);
if ($insert_result === false) {
    sqlsrv_close($conn);
    header("Location: signup.html?error=db_insert");
    exit;
}
sqlsrv_close($conn);

// On success, redirect back to index page with success flag
header("Location: index.html?signup=success");
exit;
?>

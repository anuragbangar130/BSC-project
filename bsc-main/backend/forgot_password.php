<?php

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);
    exit;
}

$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$newPassword = $_POST["newPassword"] ?? "";

if ($username === "" || $email === "" || $newPassword === "") {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters."
    ]);
    exit;
}

/* Check username and email */
$query = "SELECT id FROM users WHERE username = $1 AND email = $2";

$result = pg_query_params($conn, $query, [$username, $email]);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);
    exit;
}

if (pg_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Username and email do not match."
    ]);
    exit;
}

/* Hash new password */
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

/* Update password */
$updateQuery = "UPDATE users SET password = $1 WHERE username = $2 AND email = $3";

$updateResult = pg_query_params(
    $conn,
    $updateQuery,
    [$hashedPassword, $username, $email]
);

if ($updateResult) {

    echo json_encode([
        "success" => true,
        "message" => "Password reset successfully. Please login with your new password."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to reset password."
    ]);
}

?>
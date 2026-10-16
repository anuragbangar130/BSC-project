<?php

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}

$id = intval($_POST["id"] ?? 0);

if ($id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user ID"
    ]);

    exit;
}

// Check whether user exists

$userResult = pg_query_params(
    $conn,
    "SELECT id, name, role
     FROM users
     WHERE id = $1",
    [$id]
);

if (pg_num_rows($userResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);

    exit;
}

$user = pg_fetch_assoc($userResult);

// Protect admin account

if ($user["role"] === "admin") {

    echo json_encode([
        "success" => false,
        "message" => "Admin account cannot be deleted"
    ]);

    exit;
}

// Delete user

$result = pg_query_params(
    $conn,
    "DELETE FROM users
     WHERE id = $1",
    [$id]
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not delete user"
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "message" => "User deleted successfully"
]);

?>
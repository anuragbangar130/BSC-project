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
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$username = trim($_POST["username"] ?? "");
$role = trim($_POST["role"] ?? "");


if (
    $id <= 0 ||
    $name === "" ||
    $email === "" ||
    $username === "" ||
    $role === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);

    exit;
}


// Only Student and Teacher roles can be assigned

if (
    $role !== "student" &&
    $role !== "teacher"
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid role"
    ]);

    exit;
}


// Check whether user exists

$userResult = pg_query_params(
    $conn,
    "SELECT id, role
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


$existingUser =
    pg_fetch_assoc($userResult);


// Protect admin account

if ($existingUser["role"] === "admin") {

    echo json_encode([
        "success" => false,
        "message" => "Admin account cannot be edited"
    ]);

    exit;
}


// Check duplicate email

$emailCheck = pg_query_params(
    $conn,
    "SELECT id
     FROM users
     WHERE email = $1
     AND id != $2",
    [$email, $id]
);


if (pg_num_rows($emailCheck) > 0) {

    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);

    exit;
}


// Check duplicate username

$usernameCheck = pg_query_params(
    $conn,
    "SELECT id
     FROM users
     WHERE username = $1
     AND id != $2",
    [$username, $id]
);


if (pg_num_rows($usernameCheck) > 0) {

    echo json_encode([
        "success" => false,
        "message" => "Username already exists"
    ]);

    exit;
}


// Update user

$result = pg_query_params(
    $conn,
    "UPDATE users
     SET
        name = $1,
        email = $2,
        username = $3,
        role = $4
     WHERE id = $5",
    [
        $name,
        $email,
        $username,
        $role,
        $id
    ]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not update user"
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "message" => "User updated successfully"
]);

?>
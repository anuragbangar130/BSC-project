<?php

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        echo json_encode([
            "success" => false,
            "message" => "Username and password are required"
        ]);

        exit;
    }

    // Find user
    $result = pg_query_params(
        $conn,
        "SELECT id, name, username, password, role, teacher_status, account_status
         FROM users
         WHERE username = $1",
        [$username]
    );

    if (!$result || pg_num_rows($result) === 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password"
        ]);

        exit;
    }

    $user = pg_fetch_assoc($result);

    // Check password
    if (!password_verify($password, $user["password"])) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password"
        ]);

        exit;
    }

    // Teacher verification check
    if ($user["role"] === "teacher") {

        if ($user["account_status"] === "pending") {

            echo json_encode([
                "success" => false,
                "message" => "Your teacher account is waiting for admin approval."
            ]);

            exit;
        }

        if ($user["account_status"] === "rejected") {

            echo json_encode([
                "success" => false,
                "message" => "Your teacher account request was rejected."
            ]);

            exit;
        }

        if ($user["account_status"] !== "approved") {

            echo json_encode([
                "success" => false,
                "message" => "Your teacher account is not approved yet."
            ]);

            exit;
        }
    }

    // Login successful
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "id" => $user["id"],
            "name" => $user["name"],
            "username" => $user["username"],
            "role" => $user["role"]
        ]
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
}

?>
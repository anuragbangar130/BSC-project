<?php

include "db.php";

header("Content-Type: application/json");


/* =========================
   GET USER ID
   ========================= */

$userId = intval($_GET["user_id"] ?? 0);


if ($userId <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user"
    ]);

    exit;
}


/* =========================
   CHECK USER
   ========================= */

$userResult = pg_query_params(
    $conn,
    "SELECT id, name, role
     FROM users
     WHERE id = $1",
    [$userId]
);


if (pg_num_rows($userResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);

    exit;
}


$user = pg_fetch_assoc($userResult);


/* =========================
   GET USER HISTORY
   ========================= */

$result = pg_query_params(
    $conn,
    "SELECT
        id,
        content_id,
        action,
        title,
        description,
        created_at
     FROM activity_history
     WHERE user_id = $1
     ORDER BY created_at DESC",
    [$userId]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch history"
    ]);

    exit;
}


/* =========================
   CREATE HISTORY ARRAY
   ========================= */

$history = [];


while ($row = pg_fetch_assoc($result)) {

    $history[] = $row;

}


/* =========================
   SEND RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $user["id"],
        "name" => $user["name"],
        "role" => $user["role"]
    ],
    "data" => $history
]);

?>
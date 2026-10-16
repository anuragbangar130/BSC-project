<?php

include "db.php";

header("Content-Type: application/json");


/* =========================
   CHECK REQUEST
   ========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}


/* =========================
   GET DATA
   ========================= */

$contentId = intval($_POST["id"] ?? 0);
$userId = intval($_POST["user_id"] ?? 0);

if (
    $contentId <= 0 ||
    $userId <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid information"
    ]);

    exit;
}


/* =========================
   CHECK USER
   ========================= */

$userResult = pg_query_params(
    $conn,
    "SELECT role
     FROM users
     WHERE id = $1",
    [$userId]
);

if (!$userResult) {

    echo json_encode([
        "success" => false,
        "message" => "Could not check user"
    ]);

    exit;
}

if (pg_num_rows($userResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);

    exit;
}

$user = pg_fetch_assoc($userResult);

$role = $user["role"];


/* =========================
   CHECK PERMISSION
   ========================= */

if (
    $role !== "teacher" &&
    $role !== "admin"
) {

    echo json_encode([
        "success" => false,
        "message" => "You do not have permission to delete information"
    ]);

    exit;
}


/* =========================
   GET INFORMATION
   ========================= */

$contentResult = pg_query_params(
    $conn,
    "SELECT
        id,
        title,
        content
     FROM content
     WHERE id = $1",
    [$contentId]
);

if (!$contentResult) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch information"
    ]);

    exit;
}

if (pg_num_rows($contentResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Information not found"
    ]);

    exit;
}

$content = pg_fetch_assoc($contentResult);


/* =========================
   SAVE DELETE HISTORY
   ========================= */

$historyResult = pg_query_params(
    $conn,
    "INSERT INTO activity_history
     (
        user_id,
        content_id,
        action,
        title,
        description
     )
     VALUES
     ($1, $2, $3, $4, $5)",
    [
        $userId,
        $contentId,
        "DELETE",
        $content["title"],
        $content["content"]
    ]
);

if (!$historyResult) {

    echo json_encode([
        "success" => false,
        "message" => "Delete history could not be saved"
    ]);

    exit;
}


/* =========================
   DELETE INFORMATION
   ========================= */

$result = pg_query_params(
    $conn,
    "DELETE FROM content
     WHERE id = $1",
    [$contentId]
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Information could not be deleted"
    ]);

    exit;
}


/* =========================
   FINAL RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "message" => "Information deleted successfully"
]);

?>
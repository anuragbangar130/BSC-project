<?php

include "db.php";

header("Content-Type: application/json");


/* =========================
   GET ID
   ========================= */

$contentId = intval($_GET["id"] ?? 0);


if ($contentId <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid information ID"
    ]);

    exit;
}


/* =========================
   GET INFORMATION
   ========================= */

$result = pg_query_params(
    $conn,
    "SELECT
        content.id,
        content.title,
        content.content,
        content.content_type,
        content.file_name,
        content.file_path,
        content.mime_type,
        content.created_at,
        subjects.subject_name,
        users.name AS user_name,
        users.role AS user_role

     FROM content

     JOIN subjects
     ON content.subject_id = subjects.id

     JOIN users
     ON content.user_id = users.id

     WHERE content.id = $1",

    [$contentId]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch information"
    ]);

    exit;
}


/* =========================
   CHECK INFORMATION
   ========================= */

if (pg_num_rows($result) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Information not found"
    ]);

    exit;
}


/* =========================
   FETCH DATA
   ========================= */

$information = pg_fetch_assoc($result);


/* =========================
   RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "data" => $information
]);

?>
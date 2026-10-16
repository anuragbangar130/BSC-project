<?php

include "db.php";

header("Content-Type: application/json");


/* =========================
   CHECK USER ID
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
   GET SUBJECT ID
   ========================= */

$subjectId = intval($_GET["subject_id"] ?? 0);


/* =========================
   GET USER ROLE
   ========================= */

$userResult = pg_query_params(
    $conn,
    "SELECT role
     FROM users
     WHERE id = $1",
    [$userId]
);


if (!$userResult || pg_num_rows($userResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);

    exit;
}


$user = pg_fetch_assoc($userResult);

$role = $user["role"];


/* =========================
   GET INFORMATION
   ========================= */

if ($subjectId > 0) {

    /* =========================
       SUBJECT-WISE INFORMATION
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

            subjects.id AS subject_id,
            subjects.subject_name,

            users.name AS user_name

         FROM content

         JOIN subjects
         ON content.subject_id = subjects.id

         JOIN users
         ON content.user_id = users.id

         WHERE content.subject_id = $1

         ORDER BY content.created_at DESC",

        [$subjectId]
    );

} else {

    /* =========================
       ALL INFORMATION
       ========================= */

    $result = pg_query(
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

            subjects.id AS subject_id,
            subjects.subject_name,

            users.name AS user_name

         FROM content

         JOIN subjects
         ON content.subject_id = subjects.id

         JOIN users
         ON content.user_id = users.id

         ORDER BY content.created_at DESC"
    );
}


/* =========================
   CHECK RESULT
   ========================= */

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch information"
    ]);

    exit;
}


/* =========================
   CREATE ARRAY
   ========================= */

$information = [];


while ($row = pg_fetch_assoc($result)) {

    $information[] = $row;

}


/* =========================
   SEND RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "role" => $role,
    "data" => $information
]);

?>
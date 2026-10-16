<?php

include "db.php";

header("Content-Type: application/json");

$result = pg_query(
    $conn,
    "SELECT
        id,
        name,
        email,
        teacher_id,
        teacher_proof
     FROM users
     WHERE role = 'teacher'
       AND teacher_status = 'pending'
     ORDER BY id DESC"
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not load teacher requests."
    ]);

    exit;
}

$requests = [];

while ($row = pg_fetch_assoc($result)) {

    $requests[] = $row;

}

echo json_encode([
    "success" => true,
    "requests" => $requests
]);

?>
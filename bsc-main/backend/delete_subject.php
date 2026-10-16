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
        "message" => "Invalid subject ID"
    ]);

    exit;
}


// Check whether subject exists

$subjectCheck = pg_query_params(
    $conn,
    "SELECT id, subject_name
     FROM subjects
     WHERE id = $1",
    [$id]
);


if (!$subjectCheck) {

    echo json_encode([
        "success" => false,
        "message" => "Could not check subject"
    ]);

    exit;
}


if (pg_num_rows($subjectCheck) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Subject not found"
    ]);

    exit;
}


$subject =
    pg_fetch_assoc($subjectCheck);


// Delete subject

$result = pg_query_params(
    $conn,
    "DELETE FROM subjects
     WHERE id = $1",
    [$id]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not delete subject"
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "message" =>
        "Subject '" .
        $subject["subject_name"] .
        "' deleted successfully"
]);

?>
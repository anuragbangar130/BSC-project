<?php

include "db.php";

header("Content-Type: application/json");

$pattern = $_GET["pattern"] ?? "";
$pattern = trim($pattern);

if ($pattern === "") {
    echo json_encode([
        "success" => false,
        "message" => "Pattern is required"
    ]);
    exit;
}

$result = pg_query_params(
    $conn,
    "SELECT
        id,
        subject_name,
        semester,
        description,
        pattern,
        created_at
     FROM subjects
     WHERE LOWER(pattern) = LOWER($1)
     ORDER BY semester ASC, id ASC",
    [$pattern]
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch subjects"
    ]);

    exit;
}

$subjects = [];

while ($row = pg_fetch_assoc($result)) {
    $subjects[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $subjects
]);

?>
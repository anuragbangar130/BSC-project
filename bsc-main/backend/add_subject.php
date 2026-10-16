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

$subjectName = trim($_POST["subject_name"] ?? "");
$semester = intval($_POST["semester"] ?? 0);
$description = trim($_POST["description"] ?? "");


if ($subjectName === "" || $semester <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Subject name and semester are required"
    ]);

    exit;
}


// Check duplicate subject

$check = pg_query_params(
    $conn,
    "SELECT id
     FROM subjects
     WHERE LOWER(subject_name) = LOWER($1)
     AND semester = $2",
    [
        $subjectName,
        $semester
    ]
);


if (!$check) {

    echo json_encode([
        "success" => false,
        "message" => "Could not check subject"
    ]);

    exit;
}


if (pg_num_rows($check) > 0) {

    echo json_encode([
        "success" => false,
        "message" => "This subject already exists for this semester"
    ]);

    exit;
}


// Insert subject

$result = pg_query_params(
    $conn,
    "INSERT INTO subjects
        (subject_name, semester, description)
     VALUES
        ($1, $2, $3)
     RETURNING id",
    [
        $subjectName,
        $semester,
        $description
    ]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not add subject"
    ]);

    exit;
}


$subject =
    pg_fetch_assoc($result);


echo json_encode([
    "success" => true,
    "message" => "Subject added successfully",
    "id" => $subject["id"]
]);

?>
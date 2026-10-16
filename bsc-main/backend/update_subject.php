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
$subjectName = trim($_POST["subject_name"] ?? "");
$semester = intval($_POST["semester"] ?? 0);
$description = trim($_POST["description"] ?? "");


if (
    $id <= 0 ||
    $subjectName === "" ||
    $semester <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "All required fields are needed"
    ]);

    exit;
}


// Check subject exists

$subjectCheck = pg_query_params(
    $conn,
    "SELECT id
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


// Check duplicate subject

$duplicateCheck = pg_query_params(
    $conn,
    "SELECT id
     FROM subjects
     WHERE LOWER(subject_name) = LOWER($1)
     AND semester = $2
     AND id != $3",
    [
        $subjectName,
        $semester,
        $id
    ]
);


if (!$duplicateCheck) {

    echo json_encode([
        "success" => false,
        "message" => "Could not check duplicate subject"
    ]);

    exit;
}


if (pg_num_rows($duplicateCheck) > 0) {

    echo json_encode([
        "success" => false,
        "message" => "This subject already exists for this semester"
    ]);

    exit;
}


// Update subject

$result = pg_query_params(
    $conn,
    "UPDATE subjects
     SET
        subject_name = $1,
        semester = $2,
        description = $3
     WHERE id = $4",
    [
        $subjectName,
        $semester,
        $description,
        $id
    ]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not update subject"
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "message" => "Subject updated successfully"
]);

?>
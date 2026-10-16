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
   GET FORM DATA
   ========================= */

$subjectId = intval($_POST["subject_id"] ?? 0);
$title = trim($_POST["title"] ?? "");
$contentType = trim($_POST["content_type"] ?? "");
$description = trim($_POST["description"] ?? "");
$userId = intval($_POST["user_id"] ?? 0);


/* =========================
   VALIDATION
   ========================= */

if (
    $subjectId <= 0 ||
    $title === "" ||
    $contentType === "" ||
    $userId <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Required fields are missing"
    ]);

    exit;
}


/* =========================
   CHECK USER + ROLE
   ========================= */

$userResult = pg_query_params(
    $conn,
    "SELECT id, role
     FROM users
     WHERE id = $1",
    [$userId]
);

if (!$userResult || pg_num_rows($userResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid user"
    ]);

    exit;
}


$user = pg_fetch_assoc($userResult);

$role = $user["role"];


/* =========================
   CHECK ROLE
   ========================= */

if (
    $role !== "student" &&
    $role !== "teacher" &&
    $role !== "admin"
) {

    echo json_encode([
        "success" => false,
        "message" => "You are not allowed to add information"
    ]);

    exit;
}


/* =========================
   CHECK SUBJECT
   ========================= */

$subjectResult = pg_query_params(
    $conn,
    "SELECT id
     FROM subjects
     WHERE id = $1
     LIMIT 1",
    [$subjectId]
);


if (!$subjectResult || pg_num_rows($subjectResult) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Subject not found. Please add subject first."
    ]);

    exit;
}


/* =========================
   FILE VARIABLES
   ========================= */

$fileName = null;
$filePath = null;
$mimeType = null;


/* =========================
   FILE UPLOAD
   ========================= */

if (
    isset($_FILES["file"]) &&
    $_FILES["file"]["error"] === UPLOAD_ERR_OK
) {

    $originalName =
        basename($_FILES["file"]["name"]);

    $tmpName =
        $_FILES["file"]["tmp_name"];

    $mimeType =
        $_FILES["file"]["type"];


    /* Create upload folder */

    $uploadDir =
        "../uploads/";


    if (!is_dir($uploadDir)) {

        mkdir(
            $uploadDir,
            0777,
            true
        );

    }


    /* Create unique filename */

    $newFileName =
        time() . "_" . $originalName;


    $destination =
        $uploadDir . $newFileName;


    /* Move uploaded file */

    if (
        !move_uploaded_file(
            $tmpName,
            $destination
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "File upload failed"
        ]);

        exit;
    }


    $fileName =
        $originalName;

    $filePath =
        "uploads/" . $newFileName;
}


/* =========================
   INSERT CONTENT
   ========================= */

$result = pg_query_params(
    $conn,
    "INSERT INTO content
    (
        subject_id,
        user_id,
        content,
        title,
        content_type,
        file_name,
        file_path,
        mime_type
    )
    VALUES
    (
        $1,
        $2,
        $3,
        $4,
        $5,
        $6,
        $7,
        $8
    )
    RETURNING id",
    [
        $subjectId,
        $userId,
        $description,
        $title,
        $contentType,
        $fileName,
        $filePath,
        $mimeType
    ]
);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Information could not be added"
    ]);

    exit;
}


/* =========================
   GET CONTENT ID
   ========================= */

$contentRow =
    pg_fetch_assoc($result);

$contentId =
    $contentRow["id"];


/* =========================
   SAVE ACTIVITY HISTORY
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
    (
        $1,
        $2,
        $3,
        $4,
        $5
    )",
    [
        $userId,
        $contentId,
        "ADD",
        $title,
        $description
    ]
);


/* =========================
   CHECK HISTORY
   ========================= */

if (!$historyResult) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Information added but history could not be saved"
    ]);

    exit;
}


/* =========================
   FINAL RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "message" =>
        "Information added successfully"
]);

?>
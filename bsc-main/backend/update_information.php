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

$title = trim($_POST["title"] ?? "");
$contentType = trim($_POST["content_type"] ?? "");
$contentText = trim($_POST["content"] ?? "");


if (
    $contentId <= 0 ||
    $userId <= 0 ||
    $title === "" ||
    $contentType === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "Required information is missing"
    ]);

    exit;
}


/* =========================
   CHECK USER
   ========================= */

$userResult = pg_query_params(
    $conn,
    "SELECT id, role
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
        "message" =>
            "You do not have permission to update information"
    ]);

    exit;
}


/* =========================
   GET OLD INFORMATION
   ========================= */

$contentResult = pg_query_params(
    $conn,
    "SELECT
        id,
        title,
        content,
        content_type,
        file_name,
        file_path,
        mime_type
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


$oldContent =
    pg_fetch_assoc($contentResult);


/* =========================
   OLD FILE DETAILS
   ========================= */

$oldFileName =
    $oldContent["file_name"];

$oldFilePath =
    $oldContent["file_path"];

$oldMimeType =
    $oldContent["mime_type"];


/* =========================
   DEFAULT FILE VALUES
   ========================= */

$fileName = $oldFileName;
$filePath = $oldFilePath;
$mimeType = $oldMimeType;


/* =========================
   CHECK NEW FILE
   ========================= */

$newFileUploaded = false;

if (
    isset($_FILES["file"]) &&
    $_FILES["file"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    /* =========================
       CHECK UPLOAD ERROR
       ========================= */

    if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {

        echo json_encode([
            "success" => false,
            "message" => "File upload failed"
        ]);

        exit;
    }


    /* =========================
       GET NEW FILE DETAILS
       ========================= */

    $originalName =
        basename($_FILES["file"]["name"]);

    $tmpName =
        $_FILES["file"]["tmp_name"];

    $newMimeType =
        $_FILES["file"]["type"];


    if ($originalName === "") {

        echo json_encode([
            "success" => false,
            "message" => "Invalid file name"
        ]);

        exit;
    }


    /* =========================
       UPLOAD DIRECTORY
       ========================= */

    $uploadDir =
        "../uploads/";


    if (!is_dir($uploadDir)) {

        if (!mkdir($uploadDir, 0777, true)) {

            echo json_encode([
                "success" => false,
                "message" =>
                    "Could not create upload folder"
            ]);

            exit;
        }
    }


    /* =========================
       CREATE UNIQUE FILE NAME
       ========================= */

    $newFileName =
        time() . "_" . $originalName;


    $destination =
        $uploadDir . $newFileName;


    /* =========================
       MOVE NEW FILE
       ========================= */

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


    /* =========================
       UPDATE FILE VALUES
       ========================= */

    $fileName =
        $originalName;

    $filePath =
        "uploads/" . $newFileName;

    $mimeType =
        $newMimeType;

    $newFileUploaded = true;
}


/* =========================
   UPDATE DATABASE
   ========================= */

$result = pg_query_params(
    $conn,
    "UPDATE content
     SET
        title = $1,
        content = $2,
        content_type = $3,
        file_name = $4,
        file_path = $5,
        mime_type = $6
     WHERE id = $7",
    [
        $title,
        $contentText,
        $contentType,
        $fileName,
        $filePath,
        $mimeType,
        $contentId
    ]
);


if (!$result) {

    /*
     * If database update fails after
     * uploading a new file, remove
     * the newly uploaded file.
     */

    if (
        $newFileUploaded &&
        isset($destination) &&
        file_exists($destination)
    ) {

        unlink($destination);
    }


    echo json_encode([
        "success" => false,
        "message" =>
            "Information could not be updated"
    ]);

    exit;
}


/* =========================
   DELETE OLD FILE
   ========================= */

if (
    $newFileUploaded &&
    !empty($oldFilePath)
) {

    $oldFileFullPath =
        "../" . $oldFilePath;


    if (
        file_exists($oldFileFullPath) &&
        is_file($oldFileFullPath)
    ) {

        unlink($oldFileFullPath);
    }
}


/* =========================
   SAVE UPDATE HISTORY
   ========================= */

$description =
    "Information updated." .
    "\n\nOld Content: " .
    ($oldContent["content"] ?? "") .
    "\n\nNew Content: " .
    $contentText;


if ($newFileUploaded) {

    $description .=
        "\n\nFile replaced: " .
        ($oldFileName ?? "No old file") .
        " → " .
        $fileName;
}


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
        "UPDATE",
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
            "Information updated but history could not be saved"
    ]);

    exit;
}


/* =========================
   FINAL RESPONSE
   ========================= */

echo json_encode([
    "success" => true,
    "message" =>
        "Information updated successfully"
]);


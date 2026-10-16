<?php

include "db.php";

header("Content-Type: application/json");


// =========================
// CHECK REQUEST
// =========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}


// =========================
// GET FORM DATA
// =========================

$fullname = trim($_POST["fullname"] ?? "");
$email = trim($_POST["email"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$role = trim($_POST["role"] ?? "");

$teacher_id = trim($_POST["teacher_id"] ?? "");


// =========================
// BASIC VALIDATION
// =========================

if (
    $fullname === "" ||
    $email === "" ||
    $username === "" ||
    $password === "" ||
    $role === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);

    exit;
}


// =========================
// EMAIL VALIDATION
// =========================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address"
    ]);

    exit;
}


// =========================
// ROLE VALIDATION
// =========================

if (
    $role !== "student" &&
    $role !== "teacher"
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid role selected"
    ]);

    exit;
}


// =========================
// ACCOUNT STATUS
// =========================

// Student = direct access
// Teacher = admin approval required

$account_status = "approved";

if ($role === "teacher") {
    $account_status = "pending";
}


// =========================
// TEACHER VALIDATION
// =========================

$teacher_proof_path = null;

$teacher_status = "approved";


if ($role === "teacher") {

    if ($teacher_id === "") {

        echo json_encode([
            "success" => false,
            "message" => "Teacher / Employee ID is required"
        ]);

        exit;
    }


    if (
        !isset($_FILES["teacher_proof"]) ||
        $_FILES["teacher_proof"]["error"] !== UPLOAD_ERR_OK
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Teacher proof is required"
        ]);

        exit;
    }


    // Maximum file size = 5 MB
    if ($_FILES["teacher_proof"]["size"] > 5 * 1024 * 1024) {

        echo json_encode([
            "success" => false,
            "message" => "Teacher proof must be less than 5 MB"
        ]);

        exit;
    }


    // Allowed file types
    $allowedTypes = [
        "image/jpeg",
        "image/png",
        "application/pdf"
    ];


    $fileType =
        mime_content_type(
            $_FILES["teacher_proof"]["tmp_name"]
        );


    if (!in_array($fileType, $allowedTypes)) {

        echo json_encode([
            "success" => false,
            "message" => "Only JPG, PNG or PDF files are allowed"
        ]);

        exit;
    }


    // Teacher account remains pending
    $teacher_status = "pending";


    // Create unique file name
    $extension =
        pathinfo(
            $_FILES["teacher_proof"]["name"],
            PATHINFO_EXTENSION
        );


    $newFileName =
        "teacher_" .
        time() .
        "_" .
        uniqid() .
        "." .
        $extension;


    $uploadDir =
        "../uploads/teacher_proofs/";


    $uploadPath =
        $uploadDir .
        $newFileName;


    if (
        !move_uploaded_file(
            $_FILES["teacher_proof"]["tmp_name"],
            $uploadPath
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Teacher proof upload failed"
        ]);

        exit;
    }


    $teacher_proof_path =
        "uploads/teacher_proofs/" .
        $newFileName;
}


// =========================
// CHECK EXISTING EMAIL / USERNAME
// =========================

$check = pg_query_params(
    $conn,
    "SELECT id
     FROM users
     WHERE email = $1
     OR username = $2",
    [
        $email,
        $username
    ]
);


if (!$check) {

    echo json_encode([
        "success" => false,
        "message" => "Could not check existing account"
    ]);

    exit;
}


if (pg_num_rows($check) > 0) {

    echo json_encode([
        "success" => false,
        "message" => "Email or username already exists"
    ]);

    exit;
}


// =========================
// HASH PASSWORD
// =========================

$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


// =========================
// INSERT USER
// =========================

$result = pg_query_params(
    $conn,
    "INSERT INTO users
    (
        name,
        email,
        username,
        password,
        role,
        account_status,
        teacher_status,
        teacher_id,
        teacher_proof
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
        $8,
        $9
    )",
    [
        $fullname,
        $email,
        $username,
        $hashedPassword,
        $role,
        $account_status,
        $teacher_status,
        $teacher_id !== "" ? $teacher_id : null,
        $teacher_proof_path
    ]
);


// =========================
// RESPONSE
// =========================

if ($result) {

    if ($role === "teacher") {

        echo json_encode([
            "success" => true,
            "message" => "Teacher request submitted. Waiting for admin verification."
        ]);

    } else {

        echo json_encode([
            "success" => true,
            "message" => "Account created successfully"
        ]);

    }

} else {

    echo json_encode([
        "success" => false,
        "message" => "Account creation failed"
    ]);
}

?>
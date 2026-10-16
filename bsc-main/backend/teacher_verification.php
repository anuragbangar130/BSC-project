<?php

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


$id = intval($_POST["id"] ?? 0);
$action = trim($_POST["action"] ?? "");


if ($id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid teacher ID."
    ]);

    exit;
}


if (
    $action !== "approve" &&
    $action !== "reject"
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid action."
    ]);

    exit;
}


/* =========================
   APPROVE
   ========================= */

if ($action === "approve") {

    $result = pg_query_params(
        $conn,

        "UPDATE users
         SET teacher_status = 'approved'
         WHERE id = $1
           AND role = 'teacher'
           AND teacher_status = 'pending'",

        [$id]
    );


    if ($result && pg_affected_rows($result) > 0) {

        echo json_encode([
            "success" => true,
            "message" => "Teacher approved successfully."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Teacher request not found."
        ]);

    }

    exit;
}


/* =========================
   REJECT
   ========================= */

if ($action === "reject") {

    $result = pg_query_params(
        $conn,

        "UPDATE users
         SET teacher_status = 'rejected'
         WHERE id = $1
           AND role = 'teacher'
           AND teacher_status = 'pending'",

        [$id]
    );


    if ($result && pg_affected_rows($result) > 0) {

        echo json_encode([
            "success" => true,
            "message" => "Teacher request rejected."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Teacher request not found."
        ]);

    }

    exit;
}

?>
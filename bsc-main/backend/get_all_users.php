<?php

include "db.php";

header("Content-Type: application/json");

$result = pg_query(
    $conn,
    "SELECT
        id,
        name,
        email,
        username,
        role,
        created_at
     FROM users
     ORDER BY id ASC"
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch users"
    ]);

    exit;
}

$users = [];

while ($row = pg_fetch_assoc($result)) {

    $users[] = $row;

}

echo json_encode([
    "success" => true,
    "data" => $users
]);

?>
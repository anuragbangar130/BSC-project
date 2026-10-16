<?php

include "db.php";

header("Content-Type: application/json");

$result = pg_query(
    $conn,
    "SELECT
        activity_history.id,
        activity_history.content_id,
        activity_history.action,
        activity_history.title,
        activity_history.description,
        activity_history.created_at,
        users.name AS user_name,
        users.username,
        users.role
     FROM activity_history
     JOIN users
     ON activity_history.user_id = users.id
     ORDER BY activity_history.created_at DESC"
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Could not fetch activity history"
    ]);

    exit;
}

$history = [];

while ($row = pg_fetch_assoc($result)) {

    $history[] = $row;

}

echo json_encode([
    "success" => true,
    "data" => $history
]);

?>
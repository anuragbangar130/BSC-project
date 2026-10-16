<?php

header("Content-Type: application/json");

require_once "db.php";

if (!isset($_GET["q"]) || trim($_GET["q"]) === "") {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a search keyword."
    ]);

    exit;
}


$search = trim($_GET["q"]);

$keyword = "%" . $search . "%";


$sql = "

    /* =========================
       SUBJECT SEARCH
       ========================= */

    SELECT
        s.id,
        s.subject_name AS title,
        s.description,
        'Subject' AS type
    FROM subjects s

    WHERE
        s.subject_name ILIKE $1
        OR s.description ILIKE $1


    UNION ALL


    /* =========================
       INFORMATION / CONTENT SEARCH
       ========================= */

    SELECT
        c.id,
        c.title,
        c.content AS description,
        'Content' AS type
    FROM content c

    WHERE
        c.title ILIKE $1
        OR c.content ILIKE $1


    ORDER BY title

";


$result =
    pg_query_params(
        $conn,
        $sql,
        [$keyword]
    );


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Search failed."
    ]);

    exit;
}


$data = [];


while (
    $row = pg_fetch_assoc($result)
) {

    $data[] = $row;

}


echo json_encode([

    "success" => true,

    "results" => $data

]);


?>
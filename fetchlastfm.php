<?php
header("Content-Type: text/html; charset=utf-8");
include("credentials.php");

//Set up connection to database
$conn = new mysqli(SERVER_NAME, SERVER_USERNAME, SERVER_PASS, DB_NAME);
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    exit;
} 

//Hold values for interpretation later
$values = array(
    "sinceTimestamp" => date("Y-m-d H:i:s"),
    "tracks" => array(),
);

//Fetch cached data
$sql = "SELECT * FROM tpc_last_fm";

$sql .= " ORDER BY post_timestamp DESC LIMIT 5";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $values['tracks'][] = array(
            "artist" => $row['artist'],
            "title" => $row['title'],
            "album" => $row['album'],
            "url" => $row['url'],
            "image_url_small" => $row['image_url_small'],
            "image_url_large" => $row['image_url_large'],
            "timestamp" => $row['post_timestamp']
        );
    }
}

header('Content-Type: application/json');
echo json_encode($values);
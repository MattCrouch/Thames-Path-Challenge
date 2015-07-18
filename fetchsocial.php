<?php
include("credentials.php");

//Set up connection to database
$conn = new mysqli(SERVER_NAME, SERVER_USERNAME, SERVER_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    exit;
} 

//Hold values for interpretation later
$values = array(
    "sinceTimestamp" => date("Y-m-d H:i:s"),
    "posts" => array(),
);

$lastUpdated = array(
    "last_twitter_check" => NULL,
    "last_instagram_check" => NULL
);

//Check last updates from social feeds
$sql = "SELECT * FROM tpc WHERE name IN('last_twitter_check', 'last_instagram_check')";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $lastUpdated[$row['name']] = array(
            "value" => $row['value'],
            "last_updated" => new DateTime($row['last_updated']),
        );
    }
}

//Create datetime for 30 minutes ago
$now = new DateTime();
$dateInterval = new DateInterval("PT5M"); //30 mins interval
$updateTime = clone $now;
$updateTime->sub($dateInterval);

if($lastUpdated['last_instagram_check']['last_updated'] < $updateTime) {
    /* Fetch new Instagram data */
    $currentMinId = $lastUpdated['last_instagram_check']['value'];

    //Set up cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/users/" . INSTAGRAM_USER_ID . "/media/recent?client_id=" . INSTAGRAM_CLIENT_ID . "&min_id=" . $currentMinId); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);

    //Decode result
    $output = json_decode($output);

    $newMinId = NULL;

    $newRows = array();

    foreach($output->data as $item){
        if($item->id === $currentMinId) {
            continue;
        }

        if(in_array($item->type, array("image", "video"))) {
            if($newMinId == NULL) {
                $newMinId = $item->id;
            }

            $timestamp = new DateTime();
            if(isset($item->created_time)) {
                $timestamp->setTimestamp($item->created_time);
            }

            $newRows[] = "('instagram', " . "'" . (isset($item->caption) ? $conn->real_escape_string($item->caption->text) : "") . "', '" . $conn->real_escape_string($item->images->low_resolution->url) . "', '" . $conn->real_escape_string($item->link) . "', " . (isset($item->location) ? "'" . $item->location->latitude . "'" : "NULL") . ", " . (isset($item->location) ? "'" . $item->location->longitude . "'" : "NULL") . ", '" . $timestamp->format("Y-m-d H:i:s") . "')";
        }
    }

    if(count($newRows) > 0) {
        $sql = "INSERT INTO tpc_social (source, post_text, image_url, url, latitude, longitude, post_timestamp) VALUES " . implode(",", $newRows);
        $insert = $conn->query($sql);
    }

    //Update last checked
    $sql = "UPDATE tpc SET " . (isset($newMinId) ? "value = '" . $newMinId . "', " : "") . "last_updated = '" . date("Y-m-d H:i:s") . "' WHERE name = 'last_instagram_check'";
    $update = $conn->query($sql);
}

//Only get the updates we don't already have
$sinceTimestamp = NULL;
if(isset($_GET['since'])) {
    $checkDate = date_parse($_GET['since']);
    if($checkDate['error_count'] == 0) {
        $sinceTimestamp = new DateTime($_GET['since']);
    }
}

//Fetch cached data
$sql = "SELECT * FROM tpc_social";

if($sinceTimestamp) {
    $sql .= " WHERE post_timestamp >= '" . $sinceTimestamp->format("Y-m-d H:i:s") . "'";
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $values['posts'][] = array(
            "source" => $row['source'],
            "text" => $row['post_text'],
            "image" => $row['image_url'],
            "url" => $row['url'],
            "lat" => $row['latitude'],
            "lng" => $row['longitude'],
            "timestamp" => $row['post_timestamp']
        );
    }
}

header('Content-Type: application/json');
echo json_encode($values);
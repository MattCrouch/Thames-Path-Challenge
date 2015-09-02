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

$newRows = array(); // Holds any new entries

//Create datetime for 8 minutes ago
$now = new DateTime();
$dateInterval = new DateInterval("PT5M"); //8 mins interval
$updateTime = clone $now;
$updateTime->sub($dateInterval);

if($lastUpdated['last_twitter_check']['last_updated'] < $updateTime) {
    /* Fetch new Twitter data */
    $sinceId = $lastUpdated['last_twitter_check']['value'];

    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=stupler&exclude_replies=true&trim_user=true";

    if($sinceId > 0) {
        $url .= "&since_id=" . $sinceId;
    }

    //Set up cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . TWITTER_APPLICATION_TOKEN,
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
    ));
    $output = curl_exec($ch); 
    curl_close($ch);

    //Decode result
    $output = json_decode($output);

    $newMinId = NULL;

    foreach($output as $tweet){
        if($tweet->id === $sinceId) {
            continue;
        }
        
        if($newMinId == NULL) {
            $newMinId = $tweet->id;
        }

        if(isset($tweet->created_at)) {
            $timestamp = new DateTime($tweet->created_at);
        } else {
            $timestamp = new DateTime();
        }

        $url = "https://twitter.com/statuses/" . $tweet->id;

        $lat = NULL;
        $lng = NULL;

        if(isset($tweet->coordinates)) {
            $lat = $tweet->coordinates->coordinates[1];
            $lng = $tweet->coordinates->coordinates[0];
        } else if(isset($tweet->place)) {
            //Get an average position from bounding box
            $smallLat = NULL;
            $bigLat = NULL;
            $smallLng = NULL;
            $bigLng = NULL;

            foreach($tweet->place->bounding_box->coordinates as $polygon) {
                foreach($polygon as $coords) {
                    if(!$smallLat || $coords[1] < $smallLat) {
                        $smallLat = $coords[1];
                    }
                    if(!$bigLat || $coords[1] > $bigLat) {
                        $bigLat = $coords[1];
                    }
                    if(!$smallLng || $coords[0] < $smallLng) {
                        $smallLng = $coords[0];
                    }
                    if(!$bigLng || $coords[0] > $bigLng) {
                        $bigLng = $coords[0];
                    }
                }
            }

            if(!$smallLat || !$bigLat || !$smallLng || !$bigLng) {
                continue; //Can't really do much without them!
            }

            $lat = ($smallLat + $bigLat) / 2;
            $lng = ($smallLng + $bigLng) / 2;
        } else {
            continue;
        }

        if(isset($tweet->entities) && isset($tweet->entities->media)) {
            $image = $tweet->entities->media[0]->media_url;
        } else {
            $image = false;
        }

        $newRows[] = "('twitter', " . "'" . $conn->real_escape_string($tweet->text) . "', '" . ($image ? $conn->real_escape_string($image) : "") . "', '" . $conn->real_escape_string($url) . "', " . (isset($lat) ? "'" . $lat . "'" : "NULL") . ", " . (isset($lng) ? "'" . $lng . "'" : "NULL") . ", '" . $timestamp->format("Y-m-d H:i:s") . "')";
    }

    //Update last checked
    $sql = "UPDATE tpc SET " . (isset($newMinId) ? "value = '" . $newMinId . "', " : "") . "last_updated = '" . date("Y-m-d H:i:s") . "' WHERE name = 'last_twitter_check'";
    $update = $conn->query($sql);
}

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

    //Update last checked
    $sql = "UPDATE tpc SET " . (isset($newMinId) ? "value = '" . $newMinId . "', " : "") . "last_updated = '" . date("Y-m-d H:i:s") . "' WHERE name = 'last_instagram_check'";
    $update = $conn->query($sql);
}

if(count($newRows) > 0) {
    $sql = "INSERT INTO tpc_social (source, post_text, image_url, url, latitude, longitude, post_timestamp) VALUES " . implode(",", $newRows);
    $insert = $conn->query($sql);
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
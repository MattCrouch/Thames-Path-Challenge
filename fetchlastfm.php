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

$lastUpdated = NULL;

//Check last updates from social feeds
$sql = "SELECT * FROM tpc WHERE name = 'last_last_fm_check';";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $lastUpdated = array(
            "value" => $row['value'],
            "last_updated" => new DateTime($row['last_updated']),
        );
    }
}

$newRows = array(); // Holds any new entries

//Create datetime for 2 minutes ago
$now = new DateTime();
$dateInterval = new DateInterval("PT2M"); //2 mins interval
$updateTime = clone $now;
$updateTime->sub($dateInterval);

if($lastUpdated['last_updated'] < $updateTime) {
    /* Fetch new scrobbles */
    $since = $lastUpdated['value'];

    //Set up cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=stupler&api_key=" . LASTFM_API_KEY . "&format=json&extended=1&from=" . $since); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch); 
    curl_close($ch);

    //Decode result
    $output = json_decode($output);

    $newMinId = NULL;

    function processTrack($track) {
        global $newMinId, $conn;

        if(isset($track->date)) {
            $timestamp = new DateTime();
            $timestamp->setTimestamp($track->date->uts);

            if($newMinId == NULL) {
                $newMinId = $track->date->uts;
            }
        } else {
            $timestamp = new DateTime();

            if($newMinId == NULL) {
                $newMinId = time();
            }
        }

        $imageSmall = NULL;
        $imageLarge = NULL;

        if(isset($track->image)) {
            if(isset($track->image[1])) {
                $imageSmall = $track->image[1]->{"#text"};
            }
            if(isset($track->image[3])) {
                $imageLarge = $track->image[3]->{"#text"};
            }
        }

        if(!$imageSmall || !$imageLarge) {
            if(!$imageSmall && isset($track->artist->image[1])) {
                $imageSmall = $track->artist->image[1]->{"#text"};
            }
            if(!$imageLarge && isset($track->artist->image[3])) {
                $imageLarge = $track->artist->image[3]->{"#text"};
            }
        }

        return "('" . 
            (isset($track->artist) ? $conn->real_escape_string($track->artist->name) : "") . 
            "', " . "'" . $conn->real_escape_string($track->name) . 
            "', '" . (isset($track->album) ? $conn->real_escape_string($track->album->{"#text"}) : "") . 
            "', '" . $conn->real_escape_string($track->url) . 
            "', " . (isset($imageSmall) ? "'" . $imageSmall . "'" : "NULL") . 
            ", " . (isset($imageLarge) ? "'" . $imageLarge . "'" : "NULL") . 
            ", '" . $timestamp->format("Y-m-d H:i:s") . "')";
    }

    if(isset($output->recenttracks->track)) {
        if(is_array($output->recenttracks->track)) {
            foreach($output->recenttracks->track as $track){
                $newRows[] = processTrack($track);
            }
        } else {
            $newRows[] = processTrack($output->recenttracks->track);
        }
    }

    //Update last checked
    $sql = "UPDATE tpc SET " . (isset($newMinId) ? "value = '" . $newMinId . "', " : "") . "last_updated = '" . date("Y-m-d H:i:s") . "' WHERE name = 'last_last_fm_check'";
    $update = $conn->query($sql);
}

if(count($newRows) > 0) {
    $sql = "INSERT INTO tpc_last_fm (artist, title, album, url, image_url_small, image_url_large, post_timestamp) VALUES " . implode(",", $newRows);
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
$sql = "SELECT * FROM tpc_last_fm";

if($sinceTimestamp) {
    $sql .= " WHERE post_timestamp >= '" . $sinceTimestamp->format("Y-m-d H:i:s") . "'";
}

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
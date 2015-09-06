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
	"coordinates" => array()
);

$runkeeperId = NULL;

//Find the Runkeeper ID we're going to be tracking
$sql = "SELECT * FROM tpc WHERE name = 'live';";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $runkeeperId = $row['value'];
    }
}

if($runkeeperId) {
    //We have an activity to track

    $lastUpdated = NULL;

    //Check last updates from social feeds
    $sql = "SELECT * FROM tpc WHERE name = 'last_runkeeper_check';";
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
    $lastestUpdate = NULL; //Holds the Runkeeper timestamp of the most recent update for saving later

    //Create datetime for 45 seconds ago
    $now = new DateTime();
    $dateInterval = new DateInterval("PT45S"); //45 sec interval
    $updateTime = clone $now;
    $updateTime->sub($dateInterval);

    if($lastUpdated['last_updated'] < $updateTime) {
        /* Fetch new waypoints */
        $since = $lastUpdated['value'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.runkeeper.com/fitnessActivities/" . (int)$runkeeperId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/vnd.com.runkeeper.LiveFitnessActivityUpdate+json',
            'Authorization: Bearer ' . RUNKEEPER_ACCESS_TOKEN
        ));
        $output = curl_exec($ch);

        curl_close($ch);

        //Decode result
        $output = json_decode($output);

        $startTime = strtotime($output->start_time);

        foreach($output->path as $path) {
            if($path->timestamp <= $since) {
                //Happened before our last check, we have it already
                continue;
            }

            if($since < $path->timestamp) {
                $since = (int)$path->timestamp;
            }

            $timestamp = new DateTime();
            $timestamp->setTimestamp($startTime + (int)$path->timestamp);

            $newRows[] = "(" . 
            $path->latitude . 
            ", " . $path->longitude . 
            ", '" . $timestamp->format("Y-m-d H:i:s") . "')";
        }

        //Update last checked
        $sql = "UPDATE tpc SET " . (isset($since) ? "value = '" . $since . "', " : "") . "last_updated = '" . date("Y-m-d H:i:s") . "' WHERE name = 'last_runkeeper_check'";
        $update = $conn->query($sql);
    }

    if(count($newRows) > 0) {
        $sql = "INSERT INTO tpc_route (`latitude`, `longitude`, `timestamp`) VALUES " . implode(",", $newRows);
        $insert = $conn->query($sql);
    }
}

//Only get the co-ordinates we don't already have, as there's going to be quite a few...
$sinceTimestamp = NULL;
if(isset($_GET['since'])) {
	$checkDate = date_parse($_GET['since']);
	if($checkDate['error_count'] == 0) {
		$sinceTimestamp = new DateTime($_GET['since']);
	}
}

//Fetch latest co-ordinates
$sql = "SELECT * FROM tpc_route";

if($sinceTimestamp) {
	$sql .= " WHERE timestamp >= '" . $sinceTimestamp->format("Y-m-d H:i:s") . "'";
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $values['coordinates'][] = array(
        	"lat" => $row['latitude'],
        	"lng" => $row['longitude'],
        	"timestamp" => $row['timestamp']
        );
    }
}

header('Content-Type: application/json');
echo json_encode($values);
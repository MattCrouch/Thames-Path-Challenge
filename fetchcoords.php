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
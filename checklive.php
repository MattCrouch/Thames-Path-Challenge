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
	"live" => false,
	"last_updated" => NULL
);

//Check if live
$sql = "SELECT * FROM tpc WHERE name = 'live'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
    	if($row['value'] !== "0") {
    		$values['live'] = true;
    	}
        
        $values['last_updated'] = date("Y-m-d H:i:s", strtotime($row['last_updated']));
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($values);
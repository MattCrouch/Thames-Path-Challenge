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
	"total_raised" => array(
		"value" => NULL,
		"last_updated" => NULL
	),
	"target" => array(
		"value" => NULL,
		"last_updated" => NULL
	)
);

//Fetch currently cached data from JustGiving
$sql = "SELECT * FROM tpc WHERE name IN('total_raised', 'target')";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if(array_key_exists($row['name'], $values)) {
        	$values[$row['name']]['value'] = $row['value'];
        	$values[$row['name']]['last_updated'] = new DateTime($row['last_updated']);
        }
    }
}

$now = new DateTime();
$dateInterval = new DateInterval("PT30M"); //30 mins interval
$doUpdate = false;

//Check if we need to do an update to our data
foreach($values as $value) {
	if($value['last_updated']) {
		$updateTime = clone $value['last_updated'];
		$updateTime->add($dateInterval);
	}

	if(!$value['last_updated'] || $updateTime <= $now) {
		$doUpdate = true;
		break;
	}
}

if($doUpdate) {
	//Set up cURL
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://api.justgiving.com/" . JUST_GIVING_API_KEY . "/v1/fundraising/pages/matt-crouch-tpc?format=json"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
	curl_close($ch);

	//Decode result
	$output = json_decode($output);

	//Update values
	$values['total_raised']['value'] = number_format($output->grandTotalRaisedExcludingGiftAid,2);
	$values['total_raised']['last_updated'] = new DateTime();
	$values['target']['value'] = number_format($output->fundraisingTarget,2);
	$values['target']['last_updated'] = new DateTime();

	foreach($values as $key => $value) {
		$stmt = $conn->prepare("UPDATE tpc SET value = ?, last_updated = ? WHERE name = ?");
		$lastUpdated = $value['last_updated']->format("Y-m-d H:i:s");
		$stmt->bind_param('sss', $value['value'], $lastUpdated, $key);
		$stmt->execute();
	}
}

$conn->close();

//Build response
$return = array(
	"totalRaised" => number_format($values['total_raised']['value'],2),
	"target" => number_format($values['target']['value'],2)
);

header('Content-Type: application/json');
echo json_encode($return);
<?php
include("credentials.php");

//Set up connection to database
$conn = new mysqli(SERVER_NAME, SERVER_USERNAME, SERVER_PASS, DB_NAME);

$gpx = simplexml_load_file("gpx/file.gpx");

$newRows = array(); //Holds new entries for route

foreach($gpx->trk->trkseg as $trkseg) {
	foreach($trkseg->trkpt as $trkpt) {
		$attributes = $trkpt->attributes();

		$timestamp = new DateTime($trkpt->time);
		$newRows[] = "('" . $conn->real_escape_string((string)$trkpt->attributes()->lat) . "', '" . $conn->real_escape_string((string)$trkpt->attributes()->lon) . "', '" . $timestamp->format("Y-m-d H:i:s") . "')";
	}
}

$sql = "INSERT INTO tpc_route (`latitude`, `longitude`, `timestamp`) VALUES " . implode(",", $newRows);
$insert = $conn->query($sql);

echo "DONE!";
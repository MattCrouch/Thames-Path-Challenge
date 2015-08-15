<?php
header("Content-Type: text/html; charset=utf-8");
include("credentials.php");

$checkDate = date_parse("1439631760");

var_dump($checkDate);
exit;

//Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=stupler&api_key=" . LASTFM_API_KEY . "&format=json&from=1439631760"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch); 
curl_close($ch);

//Decode result
$output = json_decode($output);

if(isset($output->recenttracks->track)) {
	foreach($output->recenttracks->track as $track) {
		echo "<pre>"; var_dump($track->time); echo "</pre>"; exit;
		echo "<img src='" . $track->image[2]->{"#text"} . "'/>";
	}
}
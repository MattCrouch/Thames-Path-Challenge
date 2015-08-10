<?php 
include("credentials.php");

//Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=kinexperiments&exlude_replies=true&trim_user=true"); 
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS,
//             "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . TWITTER_APPLICATION_TOKEN,
    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
));
$output = curl_exec($ch); 
curl_close($ch);

//Decode result
$output = json_decode($output);

//echo "<pre>"; var_dump($output); echo "</pre>"; exit;

foreach($output as $tweet) {
	echo "<pre>"; var_dump($tweet); echo "</pre>"; exit;
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

		if(!$smallLat || !$bigLat || !$smallLng || $bigLng) {
			continue; //Can't really do much without them!
		}

		$lat = ($smallLat + $bigLat) / 2;
		$lng = ($smallLng + $bigLng) / 2;
	} else {
		continue;
	}

	if(isset($tweet->media)) {
		$image = $tweet->media[0]->media_url;
	} else {
		$image = false;
	}
}
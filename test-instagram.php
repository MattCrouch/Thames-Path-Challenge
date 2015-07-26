<?php 
include("credentials.php");

//Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/users/" . INSTAGRAM_USER_ID . "/media/recent?client_id=" . INSTAGRAM_CLIENT_ID); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);

//Decode result
$output = json_decode($output);

foreach($output->data as $item){
	$image_link = $item->images->low_resolution->url;
    echo '<img src="'.$image_link.'" />';
}
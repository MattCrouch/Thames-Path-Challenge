<?php 
include("credentials.php");

//Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.runkeeper.com/fitnessActivities/642284955"); 
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS,
//             "");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //'Accept: application/vnd.com.runkeeper.FitnessActivity+json',
    'Content-Type: application/vnd.com.runkeeper.LiveFitnessActivityUpdate+json',
    'Authorization: Bearer ' . RUNKEEPER_ACCESS_TOKEN
));
$output = curl_exec($ch); 
//echo "<pre>"; var_dump(curl_errno($ch)); echo "</pre>"; exit;
curl_close($ch);

//Decode result
$output = json_decode($output);

echo "<pre>"; var_dump($output); echo "</pre>"; exit;
$startTime = strtotime($output->start_time);

foreach($output->path as $path) {
	if(in_array($path->type, array("pause", "resume"))) {
		continue;
	}
	
	echo $path->latitude . " - " . $path->longitude . " (" . date("d-m-Y H:i:s", ($startTime + $path->timestamp)) . ")<br/>";
	//echo "<pre>"; var_dump($path); echo "</pre>";
}

exit;

?>
<?php 
include("credentials.php");

//Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.runkeeper.com/fitness-activities"); 
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS,
//             "");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/vnd.com.runkeeper.LiveFitnessActivity+json',
    'Authorization: Bearer ' . RUNKEEPER_ACCESS_TOKEN
));
$output = curl_exec($ch); 
curl_close($ch);

//Decode result
$output = json_decode($output);

echo "<pre>"; var_dump($output); echo "</pre>"; exit;

?>
<!-- 
<form action="https://runkeeper.com/apps/token" method="POST" enctype="application/x-www-form-urlencoded">
	<input type="hidden" name="grant_type" value="authorization_code"/>
	<input type="hidden" name="code" value="0ef4e2d83dd445bba326797bfc6f1edb"/>
	<input type="hidden" name="client_id" value="3def90baed8b4ce78474ffebc12dd680"/>
	<input type="hidden" name="client_secret" value="8704f5bf1bbd4275a944537252cbbc80"/>
	<input type="hidden" name="redirect_uri" value="http://lc.tpc"/>

	<input type="submit"/>
</form> -->
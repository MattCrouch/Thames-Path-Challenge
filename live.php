<?php include("credentials.php"); ?>
<!DOCTYPE html>
<html lang="en" class="live">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<meta property="og:title" content="Matt takes on the Thames Path Challenge" />
	<meta property="og:site_name" content="MattCrouch.net"/>
	<meta property="og:url" content="http://www.mattcrouch.net/tpc/live" />
	<meta property="article:author" content="https://www.facebook.com/matt.crouch" />
	<meta property="og:description" content="Track me live! I'm doing the Thames Path Challenge - walking 50km along the Thames to raise money for Diabetes UK." />
	<meta property="og:image" content="http://mattcrouch.net/tpc/build/images/diabetes-uk-logo.png" />
	<title>LIVE | Matt takes on the Thames Path Challenge</title>
	<link rel="stylesheet" type="text/css" href="build/css/style.css" />
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="build/js/min.js"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=GOOGLE_MAPS_API_KEY?>">
    </script>
</head>
<body>
	<div id="map"></div>
	<div class="overlay">
		<a href="#" class="show">&#9650; Show Updates &#9650;</a>
		
		<div class="lastfm">
			
		</div>
		<div class="total donate">
			<div class="current-amount">
				<h3>Amount Raised</h3>
				<span class="current loading">0.00</span> <span>of</span> <span class="total loading">0.00</span>
			</div>
			<div class="donate-now">
				<a href='http://www.justgiving.com/matt-crouch-tpc' title='JustGiving - Sponsor me now!' target='_blank' class="button">Donate Now</a>
			</div>
		</div>
	</div>
</body>
</html>
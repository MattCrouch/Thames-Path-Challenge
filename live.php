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
	<script type="text/javascript" src="build/js/handlebars.min-latest.js"></script>
	<script type="text/javascript" src="build/js/min.js"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=GOOGLE_MAPS_API_KEY?>">
    </script>
</head>
<body>
	<div id="map"></div>
	<div class="overlay">
		<div class="lastfm">
			
		</div>
		<div class="total">
			TOTAL HERE
		</div>
	</div>
</body>
<script id="some-template" type="text/x-handlebars-template">
	<div class="nowPlaying">
		<a href="{{ url }}">
			<img src="{{#if image_url_large }}{{image_url_large}}{{else}}build/images/live/icons/music-no-circle.svg{{/if}}" alt="Now Playing" class="albumArt"/>
		</a>
		<div class="detail">
			<span>Now Playing</span>
			<ul>
				<li class="title">{{ title }}</li>
				<li class="artist">{{ artist }}</li>
				<li class="album">{{ album }}</li>
			</ul>
		</div>
	</div>
</script>
</html>
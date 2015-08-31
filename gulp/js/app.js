function fetchDonations(fetchAutomatically) {
	var current = $(".donate .current-amount .current");
	var total = $(".donate .current-amount .total");

	$.ajax({
		url: "fetchdonations.php",
		type: "GET",
		success: function(data) {
			current.removeClass("loading");
			total.removeClass("loading");

			var totalRaised = data.totalRaised.replace(/\,/g,'');
			var target = data.target.replace(/\,/g,'');

			var currentValue = 0;

			if(current.data('amount')) {
				currentValue = current.data('amount');
			}

			current.text(parseFloat(currentValue).toFixed(2)).data('amount', parseFloat(currentValue).toFixed(2)).data('total', parseFloat(totalRaised).toFixed(2));
			total.text(target);

			var ms = 1000;
			var steps = 25;
			var stepLength = ms / steps;
			var stepAmount = totalRaised / steps;

			count();

			function count() {
				var value = parseFloat(current.data('amount') + stepAmount);
				if(value > current.data("total")) {
					value = parseFloat(current.data("total"));
				}
				
				current.data('amount', value);

				current.text(value.toFixed(2));

				if(value < current.data('total')) {
					setTimeout(function(){
						count();
					}, stepLength);
				}
			}
		},
		error: function() {
			//HANDLE ERROR
			current.text("-");
			total.text("-");
		}
	});

	if(fetchAutomatically) {
		setTimeout(function() {
			fetchDonations();
		}, 1800000); //Refetch every 30 minutes
	}
}

function checkLive(fetchAutomatically) {
	$.ajax({
		url: "checklive.php",
		type: "GET",
		success: function(data) {
			if(data.live) {
				console.log("LIVE!");
				var banner = "<div class='live-banner'>" +
								"Follow my progress right now! <a href='live' class='button'>Watch Live</a>" +
							"</div>";

				$("body").prepend(banner);
			} else {
				console.log("NOT LIVE :(");
			}
		},
		error: function() {
			//Die quietly...
		}
	});

	if(fetchAutomatically) {
		setTimeout(function() {
			checkLive();
		}, 1800000); //Refetch every 30 minutes
	}
}

$( document ).ready(function() {
	if($("#map").length > 0) {
		map();
	} else {
		//Not on live
		fetchDonations();
		checkLive();
	}
});

var map = function() {
	var imagePath = "build/images/"; //Change this if path changes
	var map;
	var markers = {
		"pointsOfInterest": [],
		"social": [],
		"currentLocation": null
	};
	var icons = {};
	var infoWindows = [];

	var pointsOfInterest = {};

	var route; //Holds the PolyLine drawn of the route made by routeWaypoints

	var routeWaypoints = []; //Holds multiple objects acting as waypoints

	var tracks = []; //Holds latest scrobbles
	var trackHistoryLength = 5;

	//Holds when the data way last updated, so we only need to pull down any changes
	var sinceTimestamp = {
		"waypoints": null,
		"social": null
	};

	//Holds if/when we should be checking for new updates
	var fetchAutomatically = true;
	var fetchFrequency = {
		"waypoints": 60000,
		"social": 300000,
		"scrobbles": 300000
	};

	var styles = [
		{
			"featureType": "poi",
			"stylers": [
				{ "visibility": "off" }
			]
		},
		{
			"featureType": "transit",
			"stylers": [
				{ "visibility": "off" }
			]
		},
		{
			"featureType": "poi.park",
			"stylers": [
				{ "visibility": "on" }
			]
		},
		{
			"featureType": "poi.attraction",
			"stylers": [
				{ "visibility": "on" }
			]
		}
	];

	function init() {
		//Enable maps
		google.maps.event.addDomListener(window, 'load', function() {
			map = new google.maps.Map($("#map")[0], {
				center: pointsOfInterest.start,
				zoom: 14,
				mapTypeControl: false,
				streetViewControl: false,
				zoomControlOptions: {
					position: google.maps.ControlPosition.TOP_LEFT
				}
			});

			map.setOptions({ styles: styles });

			createIcons();

			createPointsOfInterest();
			showPointsOfInterest();

			drawRoute();

			fetchNewWaypoints();
			fetchNewSocial();
			fetchNewScrobbles();

			fetchDonations(fetchAutomatically);

			$(".overlay a.show").click(function(e) {
				e.preventDefault();

				var show = $(this);
				var overlay = $(".overlay");

				if(overlay.hasClass('show')) {
					show.html("&#9650; Show Updates &#9650;");
					overlay.removeClass('show');
				} else {
					show.html("&#9660; Hide Updates &#9660;");
					overlay.addClass('show');
				}
			});
		});
	}

	function createPointsOfInterest() {
		pointsOfInterest = {
			"start": {
				name: "Putney Bridge",
				lat: 51.466780,
				lng: -0.213112,
				info: "START - Putney Bridge",
				icon: icons.flag
			},
			"half way": {
				name: "Hurst Park",
				lat: 51.392211,
				lng: -0.344472,
				info: "25km - Half Way<br/>Hurst Park",
				icon: icons.pointsOfInterest
			},
			"finish": {
				name: "Runnymede Pleasure Ground",
				lat: 51.442137,
				lng: -0.550820,
				info: "FINISH - Runnymede",
				icon: icons.flag
			}
		};
	}

	function createIcons() {
		icons.pointsOfInterest = {
			anchor: new google.maps.Point(15, 15),
			url: imagePath + "live/icons/poi.svg",
			scaledSize: new google.maps.Size(30,30)
		};

		icons.flag = {
			anchor: new google.maps.Point(15, 15),
			url: imagePath + "live/icons/poi.svg",
			scaledSize: new google.maps.Size(30,30)
		};

		icons.twitter = {
			anchor: new google.maps.Point(15, 15),
			url: imagePath + "live/icons/twitter.svg",
			scaledSize: new google.maps.Size(30,30)
		};

		icons.instagram = {
			anchor: new google.maps.Point(15, 15),
			url: imagePath + "live/icons/instagram.svg",
			scaledSize: new google.maps.Size(30,30)
		};

		icons.ping = {
			anchor: new google.maps.Point(5, 5),
			url: imagePath + "live/icons/ping.svg",
			scaledSize: new google.maps.Size(10,10)
		};
	}

	function showPointsOfInterest() {
		clearMarkers(markers.pointsOfInterest);

		$.each(pointsOfInterest, function(key, value) {
			//Make marker
			var marker = createNewMarker(value.lat, value.lng, value.name, value.icon);
			markers.pointsOfInterest.push(marker);

			//Realign window to accommodate points of interest
			realignWindow(markers.pointsOfInterest);

			//Attach an info window to marker
			var infoWindow = new google.maps.InfoWindow({
				content: value.info
			});

			google.maps.event.addListener(marker, 'click', function() {
				closeInfoWindows();
				infoWindow.open(map,marker);

				infoWindows.push(infoWindow);
			});
		});
	}

	function clearMarkers(markers) {
		$.each(markers, function(key, marker) {
			marker.setMap(null);
		});

		markers = [];
	}

	function closeInfoWindows() {
		$.each(infoWindows, function(key, infoWindow) {
			infoWindow.close();
		});
	}

	function drawRoute() {
		route = new google.maps.Polyline({
			clickable: false,
			strokeColor: '#0F3670',
			strokeOpacity: 1.0,
			strokeWeight: 5
		});

		route.setMap(map);

		$.each(routeWaypoints, function(key, waypoint){
			attachWaypoint(waypoint);
		});
	}

	function createNewWaypoint(data) {
		var waypoint = {
			lat: data.lat,
			lng: data.lng,
			timestamp: data.timestamp,
			getPosition: function() {
				return new google.maps.LatLng(this.lat,this.lng);
			}
		};

		routeWaypoints.push(waypoint);

		attachWaypoint(waypoint);
	}

	function createNewMarker(lat, lng, title, icon) {
		if(typeof icon === "undefined") {
			icon = null;
		}

		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(lat,lng),
			title: title,
			icon: icon,
			optimized: false
		});

		marker.setMap(map);
		
		return marker;
	}

	function createNewSocial(data) {
		var marker = createNewMarker(data.lat, data.lng, "this is a post from " + data.source, icons[data.source]);

		//Attach an info window to marker
		var infoWindow = new google.maps.InfoWindow({
			content: generateSocialMarkup(data)
		});

		google.maps.event.addListener(marker, 'click', function() {
			closeInfoWindows();
			infoWindow.open(map,marker);

			infoWindows.push(infoWindow);
		});
	}

	function addTrack(track) {
		if(tracks.length >= trackHistoryLength) {
			tracks.splice(tracks.length - 1,1); //Remove the oldest track
		}
		tracks.unshift(track);
	}

	function animateNewTrack(track) {
		var old = $(".overlay .lastfm .nowPlaying");
		old.addClass("out");

		setTimeout(function() {
			old.remove();
		}, 1000);

		createTrackView(track);
	}

	function createTrackView(track) {
		var container = $(".overlay .lastfm");
		var html = generateScrobbleMarkup(track);
		container.append(html);

		setTimeout(function() {
			$(".overlay .lastfm .nowPlaying").removeClass("in");
		}, 1000);
	}

	function attachWaypoint(waypoint) {
		var path = route.getPath();

		var point = new google.maps.LatLng(waypoint.lat, waypoint.lng);

		path.push(point);
	}

	function fetchNewWaypoints() {
		$.ajax({
			url: "fetchcoords.php",
			data: {
				since: sinceTimestamp.waypoints
			},
			type: "GET",
			success: function(data) {
				sinceTimestamp.waypoints = data.sinceTimestamp;

				$.each(data.coordinates, function(key, coords) {
					createNewWaypoint(coords);
				});

				updateBeacon(routeWaypoints);

				if(routeWaypoints.length > 0) {
					focusLiveTracking(routeWaypoints);
				}

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewWaypoints();
					}, fetchFrequency.waypoints);
				}
			},
			error: function() {
				console.log("Can't get the location at the moment :(");

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewWaypoints();
					}, fetchFrequency.waypoints);
				}
			}
		});
	}

	function fetchNewSocial() {
		$.ajax({
			url: "fetchsocial.php",
			data: {
				since: sinceTimestamp.social
			},
			type: "GET",
			success: function(data) {
				sinceTimestamp.social = data.sinceTimestamp;

				$.each(data.posts, function(key, post) {
					createNewSocial(post);
				});

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewSocial();
					}, fetchFrequency.social);
				}
			},
			error: function() {
				console.log("Can't get social feeds :(");

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewSocial();
					}, fetchFrequency.social);
				}
			}
		});
	}

	function fetchNewScrobbles() {
		$.ajax({
			url: "fetchlastfm.php",
			data: {
				since: sinceTimestamp.lastfm
			},
			type: "GET",
			success: function(data) {
				sinceTimestamp.lastfm = data.sinceTimestamp;

				$.each(data.tracks, function(key, track) {
					addTrack(track);
				});

				if(data.tracks.length > 0) {
					animateNewTrack(data.tracks[0]);
				}

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewSocial();
					}, fetchFrequency.social);
				}
			},
			error: function() {
				console.log("Can't get scrobbles :(");

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewScrobbles();
					}, fetchFrequency.scrobbles);
				}
			}
		});
	}

	function realignWindow(markerArray) {
		//Fit markers on screen
		var boundary = new google.maps.LatLngBounds();
		for (var i = 0; i < markerArray.length; i++) {
			boundary.extend(markerArray[i].getPosition());
		}
		map.fitBounds(boundary);
	}

	function focusLiveTracking(markerArray) {
		//Zoom on current position, and recent trail
		var recentMarkers = markerArray.slice(0);
		var recentCount = 100;

		if(recentMarkers.length > recentCount) {
			recentMarkers = recentMarkers.splice(recentMarkers.length - recentCount);
		}

		realignWindow(recentMarkers);
	}

	function updateBeacon(markerArray) {
		var location = markerArray[markerArray.length - 1];

		if(markers.currentLocation) {
			markers.currentLocation.setPosition(location.getPosition());
		} else {
			var marker = createNewMarker(location.lat, location.lng, "Current Location", icons.ping);
			markers.currentLocation = marker;
		}
	}

	function generateSocialMarkup(data) {
		html =  "<div class='social " + data.source + "'>" +
					"<a href='" + data.url + "' target='_blank'><img src='" + data.image + "'/></a>" +
					"<p class='caption'>" + data.text + "</p>" +
				"</div>";

		return html;
	}

	function generateScrobbleMarkup(data) {
		html =  "<div class='nowPlaying in'>" +
					"<a href='" + data.url + "'>" +
						"<img src='" + (data.image_url_large !== "" ? data.image_url_large : "build/images/live/icons/music-no-circle.svg") + "' alt='Now Playing' class='albumArt'/>" +
					"</a>" +
					"<div class='detail'>" +
						"<h3>Now Playing</h3>" +
						"<ul>" +
							"<li class='title'>" + data.title + "</li>" +
							"<li class='artist'>" + data.artist + "</li>" +
							"<li class='album'>" + data.album + "</li>" +
						"</ul>" +
					"</div>" +
				"</div>";

		return html;
	}

	init();
};
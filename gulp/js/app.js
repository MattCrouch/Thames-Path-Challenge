$( document ).ready(function() {
	function fetchDonations() {
		var current = $(".donate .current-amount .current");
		var total = $(".donate .current-amount .total");

		$.ajax({
			url: "fetchdonations.php",
			type: "GET",
			success: function(data) {
				current.removeClass("loading");
				total.removeClass("loading");

				current.text("0.00").data('amount', "0.00").data('total', parseFloat(data.totalRaised).toFixed(2));
				total.text(data.target);

				var ms = 1000;
				var steps = 25;
				var stepLength = ms / steps;
				var stepAmount = data.totalRaised / steps;

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
	}

	if($("#map").length > 0) {
		map();
	}

	//fetchDonations(); //Comment out during testing to save needless requests
});

var map = function() {
	var map;
	var markers = {
		"pointsOfInterest": [],
		"social": []
	};
	var infoWindows = [];

	var pointsOfInterest = {
		"start": {
			name: "Putney Bridge",
			lat: 51.466780,
			lng: -0.213112,
			info: "START - Putney Bridge"
		},
		"half way": {
			name: "Hurst Park",
			lat: 51.392211,
			lng: -0.344472,
			info: "25km - Half Way<br/>Hurst Park"
		},
		"finish": {
			name: "Runnymede Pleasure Ground",
			lat: 51.442137,
			lng: -0.550820,
			info: "FINISH - Runnymede"
		}
	};

	var route; //Holds the PolyLine drawn of the route made by routeWaypoints

	var routeWaypoints = []; //Holds multiple objects acting as waypoints

	//Holds when the data way last updated, so we only need to pull down any changes
	var sinceTimestamp = {
		"waypoints": null,
		"social": null
	};

	var fetchAutomatically = false;

	function init() {
		//Enable maps
		google.maps.event.addDomListener(window, 'load', function() {
			map = new google.maps.Map($("#map")[0], {
				center: pointsOfInterest.start,
				zoom: 14
			});

			showPointsOfInterest();

			drawRoute();

			fetchNewWaypoints();
			fetchNewSocial();
		});
	}

	function showPointsOfInterest() {
		clearMarkers(markers.pointsOfInterest);

		$.each(pointsOfInterest, function(key, value) {
			//Make marker
			var marker = createNewMarker(value.lat, value.lng, value.name);
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
			strokeColor: '#000000',
			strokeOpacity: 1.0,
			strokeWeight: 3
		});

		route.setMap(map);

		$.each(routeWaypoints, function(key, waypoint){
			attachWaypoint(waypoint);
		});
	}

	function createNewWaypoint(data) {
		console.log(data);
		var waypoint = {
			lat: data.lat,
			lng: data.lng,
			timestamp: data.timestamp
		};

		routeWaypoints.push(waypoint);

		attachWaypoint(waypoint);
	}

	function createNewMarker(lat, lng, title) {
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(lat,lng),
			title: title
		});

		marker.setMap(map);
		
		return marker;
	}

	function createNewSocial(data) {
		console.log(data);

		var marker = createNewMarker(data.lat, data.lng, "this is a post from " + data.source);

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

				if(fetchAutomatically) {
					setTimeout(function() {
						fetchNewWaypoints();
					}, 60000); //Refetch every minute
				}
			},
			error: function() {
				//HANDLE ERROR
				alert("Can't get the location at the moment :(");
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
					}, 300000); //Refetch every 5 minutes
				}
			},
			error: function() {
				//HANDLE ERROR
				alert("Can't get social feeds :(");
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

	function generateSocialMarkup(data) {
		html =  "<div class='social " + data.source + "'>" +
					"<a href='" + data.url + "' target='_blank'><img src='" + data.image + "'/></a>" +
					"<p class='caption'>" + data.text + "</p>" +
				"</div>";

		return html;
	}

	init();
};
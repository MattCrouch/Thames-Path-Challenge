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

	fetchDonations(); //Comment out during testing to save needless requests
});

var map = function() {
	var map;
	var markers = {
		"pointsOfInterest": []
	};
	var infoWindows = [];

	var pointsOfInterest = {
		"start": {
			name: "Putney Bridge",
			lat: 51.466780,
			lng: -0.213112,
			info: "START"
		},
		"finish": {
			name: "Runnymede Pleasure Ground",
			lat: 51.442137,
			lng: -0.550820,
			info: "FINISH"
		}
	};

	var route; //Holds the PolyLine drawn of the route made by routeWaypoints

	var routeWaypoints = []; //Contains multiple google.maps.LatLng() points

	function init() {
		//Enable maps
		google.maps.event.addDomListener(window, 'load', function() {
			map = new google.maps.Map($("#map")[0], {
				center: pointsOfInterest.start,
				zoom: 14
			});

			showPointsOfInterest();

			drawRoute();
		});
	}

	function showPointsOfInterest() {
		clearMarkers(markers.pointsOfInterest);

		$.each(pointsOfInterest, function(key, value) {
			//Make marker
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(value.lat,value.lng),
				title: value.name
			});

			marker.setMap(map);
			markers.pointsOfInterest.push(marker);

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

		var path = route.getPath();

		$.each(routeWaypoints, function(key, waypoint){
			path.push(waypoint);
		});
	}

	init();
};
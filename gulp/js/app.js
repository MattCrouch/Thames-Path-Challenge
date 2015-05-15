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

    fetchDonations(); //Comment out during testing to save needless requests
});
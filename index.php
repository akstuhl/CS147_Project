<?php include('header.php'); ?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwiHi6BAeRu7z44MIb8VTAxeyVe7WLvjo&sensor=true">
</script>

<form id="wikisearch" action="search.php" data-ajax="false" class="noEnterSubmit">
	<input id="searchfield" type="search" placeholder="Search Wikipedia" onsubmit="preventDefault();" />
</form>

<ul id="wikisearch_results" data-role="listview" style="display:none;">
</ul>

<div id="map_canvas" style="width:320px; height:285px;"></div> <!-- change width and height here -->

<div id="slider" class="swipe">
	<ul>
		<li style="display:block;"><div><p>Welcome to Wikitour! Click a pin to start exploring.</p></div></li>
	</ul>
</div>

<nav>
    <span id='position'></span>
    <a href='#' id='prev' onclick='slider.prev();return false;'>prev</a>
    <a href='#' id='next' onclick='slider.next();return false;'>next</a>
</nav>
<script type="text/javascript">
$(document).ready(function(){
	$('.noEnterSubmit').keypress(function(e){
	    if ( e.which == 13 ) return false;
	});
	
  //the following sets up the map, pins, etc.
  var addresses;
  var map;
  var last_marker;
	function setupMap() {
		navigator.geolocation.getCurrentPosition(locationSuccess, locationFail); //get the location 
	}
	function locationSuccess(position) { //successful location set up
		// var lat = "37.4307151";
		// 	var long ="-122.1733189";
	
		var lat = position.coords.latitude; //my location
		var long = position.coords.longitude;

		var mapOptions = { //map options
			center: new google.maps.LatLng(lat, long),
			zoom: 16,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false,
			mapTypeControl: false,
			zoomControl: false
		};
		
		map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
			
		$.ajax({ //look up facts
		  type: "POST",
		  url: "nearby_facts.php",
		  data: { lat: lat, long: long }
		}).done(function( addresses_json ) {
			addresses = jQuery.parseJSON(addresses_json);
			// console.log(addresses);
			for (var key in addresses) {
				var address = addresses[key];
				for(var sub_key in address){ //this loop is designed to just grab the first address and then break
					place_marker(address[sub_key]["lat"],address[sub_key]["long"],address[sub_key]["address"]);
					break;					
				}
			}
		});
	}
	
	function place_marker(lat,long,address){
		// console.log("placing");
		var latlong = new google.maps.LatLng(lat, long);
		var marker = new google.maps.Marker({
		    position: latlong, 
		    map: map, 
		    title:"",
			address:address,
			// addresses:addresses
		});
		// console.log(address);
		google.maps.event.addListener(marker, 'click', showFacts);
	}

	function locationFail() { //failed location look up
		alert('Oops, could not find you.');
	}
	
	function getPropertyCount(obj) {
	    var count = 0,
	        key;

	    for (key in obj) {
	        if (obj.hasOwnProperty(key)) {
	            count++;
	        }
	    }

	    return count;
	}
	
	function showFacts(e){
		if(typeof last_marker != 'undefined'){
			last_marker.setIcon("red-dot.png"); //we set the last one back to red
		}
		last_marker = this; // save the current marker
		this.setIcon("blue-dot.png"); //and then set it to blue, for now
		
		// console.log(addresses);
		$("#slider ul").html(""); //clear out previous
		address = addresses[this.address];
		// console.log(address);
		var i = 0;
		for(key in address){
			fact = address[key];
			if(i==0){
				$("#slider ul").append("<li style='display:block;'><div><a data-transition='slide' href='fact.php?id="+fact["id"]+"'><p>"+fact['fact']+"</p></a></div></li>")
			}else {
				$("#slider ul").append("<li style='display:none;'><div><a data-transition='slide' href='fact.php?id="+fact["id"]+"'><p>"+fact['fact']+"</p></a></div></li>")
			}
			i++;
		}
		$("#position").html("");
		$("#position").append("<em class='on'>&bull;</em>");
   	while($("#position").children().size() < $("#slider ul").children().size()){
		$("#position").append("<em>&bull;</em>");
	}
		$(document).ready(function(){
			
		 	slider = new Swipe(document.getElementById('slider'), {
		 		      	callback: function(e, pos) {
		 		        var i = bullets.length;
		 		        while (i--) {
		 		          bullets[i].className = ' ';
		 		        }
		 		        bullets[pos].className = 'on';

	      			}
	    }),
	    bullets = document.getElementById('position').getElementsByTagName('em');
	
		});
	}
	
	setupMap();
	
	//this is the code to setup the wikisearch, autocompletion, etc
	function get_results(searchterm){
		var results;
		$.ajax({ //look up facts
			  type: "GET",
			  url: "search.php",
			  data: { "searchterm": searchterm,}
			}).done(function( json_results ) {
				results = jQuery.parseJSON(json_results);
				results = results["query"]["search"];

				$("#wikisearch_results").html("");
				$("#wikisearch_results").css("display","block");
				for (key in results){
					result = results[key]["title"];
					$("#wikisearch_results").append("<li><a href='wikipedia.php?source="+result+"'>"+result+"</a></li>")
				}
				$("#wikisearch_results").listview('refresh');
				
			});
	}
	
	$('#searchfield').keyup(function() {
		if($('#searchfield').val() == ''){
			$("#wikisearch_results").css("display","none");
			return;
		}
		get_results($('#searchfield').val());
	});
	
	$('.ui-input-clear').on('click', function(e){ //the clear search button
	    $("#wikisearch_results").css("display","none");
	});
	// $('#searchfield').blur(function() {
		// $("#wikisearch_results").css("display","none");
	// });
	
	// get_results("hoover");
});
</script>
<script src='swipe.js'></script>
<script>
	var slider, bullets;
	$(document).ready(function(){
	 	slider = new Swipe(document.getElementById('slider'), {
      	callback: function(e, pos) {
		
        // var i = bullets.length;
        //       while (i--) {
        //         bullets[i].className = ' ';
        //       }
        //       bullets[pos].className = 'on';

      }
    }),
    bullets = document.getElementById('position').getElementsByTagName('em');
	});
</script>
<?php include('footer.php'); ?>
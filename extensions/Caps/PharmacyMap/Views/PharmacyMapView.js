PharmacyMapView = Backbone.View.extend({
    template: _.template($('#pharmacy_map_template').html()),
    map: null,
    lat:null,
    initialize: function(){
        this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #addMarker": "Location",
    },

    Location: function(){
	console.log(lat);
    },

    initMap: function(){
	var mapDiv = document.getElementById('map');
	map = new google.maps.Map(mapDiv, {
	    center:{lat:43.6560817, lng:-79.390945},
	    zoom:8
	});

	var input = document.getElementById('lat');
	var searchBox = new google.maps.places.SearchBox(input);
	//map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);	

	map.addListener('bounds_changed', function (){
	    searchBox.setBounds(map.getBounds());
	});

	var markers = [];	
	searchBox.addListener('places_changed', function(){
	    var places = searchBox.getPlaces();
	    if(places.length == 0){
		return;
	    }
	    
	    var bounds = new google.maps.LatLngBounds();
	    places.forEach(function(place){
	        this.lat = place.geometry.location.lat();//use this to save  .lng() for longittude
//		console.log(place);
		if (place.geometry.viewport){
		    bounds.union(place.geometry.viewport);
		} else {
		    bounds.extend(place.geometry.location);
		}
	     });
	     if(places.length >1){
		 map.fitBounds(bounds);
	     }
	     else if(places.length==1){
	         map.setCenter(bounds.getCenter());
	         map.setZoom(17);
	     }
        });
    },

    AddMarker: function(location){
	var marker = new google.maps.Marker({
	    position: location,
	    map: map,
	    data:"pharm"
	});
    },

    TestMarker: function(){
	var centralPark = new google.maps.LatLng(44.500, -80.450);
	this.AddMarker(centralPark);
    },

    render: function(){
        main.set('title', 'Locate a Pharmacy (In development)');
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
	this.initMap(); 
	this.TestMarker();
        return this.$el;
    }

});

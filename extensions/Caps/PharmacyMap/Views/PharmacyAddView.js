PharmacyAddView = Backbone.View.extend({
    template: _.template($('#pharmacy_add_template').html()),
    map: null,
    lat:0,
    lng:0,
    initialize: function(){
        this.model.bind('sync', this.render);//change to on
    },

    events:{
        "click #addPharmacy": "addPharmacy",

    },

    addPharmacy: function(){
	var university = new University({name: $('#name').val(),
					 latitude: lat.toString(),
					 longitude: lng.toString(),
					 province_string: $('#prov').val(),
					 address: $('#lat').val()
					 });
	university.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#addPharmacy").prop('disabled', false);
                clearAllMessages();
                document.location = wgServer+wgScriptPath+"/index.php/Special:PharmacyMap#";
                addSuccess("Pharmacy Added");
            }, this),
            error: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#addPharmacy").prop('disabled', false);
                clearAllMessages();
                addError("There was a problem saving the Pharmacy", true);
            }, this)
        });
   },

    initMap: function(){
        var mapDiv = document.getElementById('map');
        map = new google.maps.Map(mapDiv, {
            center:{lat:43.6560817, lng:-79.390945},
            zoom:8,
            width:'100%'
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
                lat = place.geometry.location.lat();//use this to save  .lng() for longittude
                lng = place.geometry.location.lng();//use this to save  .lng() for longittude
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

    AddMarkers: function(group){
        _.each(group,$.proxy(function(val){
            if(val.latitude != null){
                var pharmLoc = new google.maps.LatLng(val.latitude, val.longitude);
                var marker = new google.maps.Marker({
                    position: pharmLoc,
                    map: map,
                    data:"pharm",
                    title:val.name
                });

                var infowindow = new google.maps.InfoWindow({
                    content: val.name
                });

                marker.addListener('click', function(){
                    infowindow.open(map, marker);
                });
            }
        },this));
    },

    render: function(){
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
	$(".chzn-select").chosen();
        this.initMap();
        var title = $("#pageTitle").clone();
	    $(title).attr('id', 'copiedTitle');
	    this.$el.prepend(title);
        return this.$el;
    }

});


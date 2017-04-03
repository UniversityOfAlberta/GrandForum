PharmacyMapView = Backbone.View.extend({
    template: _.template($('#pharmacy_map_template').html()),
    map: null,
    lat:53.7608608,
    long:-98.8138763,
    zoom:4,

    initialize: function(){
        this.model.bind('sync', this.render);//change to on
    },

    events:{
        "click #addPharmacy": "addPharmacy",
        "click #findLocation": "findLocation",
        "click #printMap": "printMap"

    },

    printMap: function(){
        window.print();
    },

    findLocation: function(){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition($.proxy(function(position) {
                  var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                  };
                this.lat = position.coords.latitude;
                this.long = position.coords.longitude;
                this.model.lat = this.lat;
                this.model.long = this.long;
                this.model.fetch();
                this.map.setCenter(pos);
                this.zoom = 10;
            },this), function() {
            });
         }
         else{
            map.setCenter(map.getCenter());
         }
    },

    addPharmacy: function(){
            document.location = document.location + '#/add';
    },

    initMap: function(){
    var mapDiv = document.getElementById('map');
    map = new google.maps.Map(mapDiv, {
        center:{lat:this.lat, lng:this.long},
        zoom:this.zoom,
        width:'100%'
    });
    this.map = map;

    var input = document.getElementById('lat');
    var searchBox = new google.maps.places.SearchBox(input);

    map.addListener('bounds_changed', function (){
        searchBox.setBounds(map.getBounds());
    });

    var markers = [];    
    searchBox.addListener('places_changed',$.proxy(function(){
        var places = searchBox.getPlaces();
        if(places.length == 0){
        return;
        }
        
        var bounds = new google.maps.LatLngBounds();
        places.forEach($.proxy(function(place){
            this.lat = place.geometry.location.lat();
            this.long = place.geometry.location.lng();
            console.log(this.lat, this.long);
            this.model.lat = this.lat;
            this.model.long = this.long;
            this.model.fetch();

        if (place.geometry.viewport){
            bounds.union(place.geometry.viewport);
        } else {
            bounds.extend(place.geometry.location);
        }
         }, this));
         if(places.length >1){
         map.fitBounds(bounds);
         }
         else if(places.length==1){
             map.setCenter(bounds.getCenter());
             this.zoom = 10;
         }
        }, this));
    },

    AddMarkers: function(group){
         var pinColor = "FE7569";
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
            new google.maps.Size(21, 34),
            new google.maps.Point(0,0),
            new google.maps.Point(10, 34));
        var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow",
            new google.maps.Size(40, 37),
            new google.maps.Point(0, 0),
            new google.maps.Point(12, 35));
        _.each(group, function(val){
            if(val.latitude != null){
                var pharmLoc = new google.maps.LatLng(val.latitude, val.longitude);
                var marker = new google.maps.Marker({
                    position: pharmLoc,
                    map: map,
                    data:"pharm",
                    title:val.name,
                    icon: pinImage,
                    shadow: pinShadow
                });

                var infowindow = new google.maps.InfoWindow({
                    content: "Name: " 
                             + val.name 
                             + "<br>"
                             + "Address: "
                             + val.shortName 
                             + "<br>" 
                             + "Phone: "
                             + val.phone 
                             + "<br>" 
                             + "Hours: "
                             + val.hours
                });

                marker.addListener('click', function(){
                    infowindow.open(map, marker);
                });
            }
        });
    },

    render: function(){
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
    this.initMap();
        var empty = [];
        this.AddMarkers(empty);
    this.AddMarkers(data);
    var title = $("#pageTitle").clone();
    $(title).attr('id', 'copiedTitle');
    this.$el.prepend(title);
        return this.$el;
    }

});

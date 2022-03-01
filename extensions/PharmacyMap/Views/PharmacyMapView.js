PharmacyMapView = Backbone.View.extend({
    template: _.template($('#pharmacy_map_template').html()),
    map: null,
    geocoder: null,
    lat: 44.23846,
    long: -76.4409887,
    zoom: 6,
    refresh: true,
    buttons: [],
    cat_json: null,
    category: null,
    previous: [],
    renderMap: false,
    initialize: function () {
        this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #addPharmacy": "addPharmacy",
        "click #findLocation": "findLocation",
        "click #printMap": "printMap",
        "click .category": "findCategory",
        "click .previous_button": "previousCategory",

    },

    printMap: function () {
        window.print();
    },

    findCat: function (clicked_cat) {
        this.model.cat = clicked_cat;
        this.model.fetch();
    },

    findLocation: function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition($.proxy(function (position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
		    console.log(pos);
                this.map.setCenter(pos);
                this.zoom = 30;
            }, this), function () {
            });
        }
        else {
            map.setCenter(map.getCenter());
        }
    },

    findCategory: function (ev) {
        var cat = $(ev.currentTarget).data('cat');;
        if (this.buttons[cat].hasOwnProperty('children')) {
            this.previous.push({ "buttons": this.buttons, "text": this.buttons[cat]["text"] });
            this.buttons = this.buttons[cat]["children"];
            //$('#address_bar').hide();
            //$('#map-container').hide();
        }
        else {
            this.refresh = false;
            this.renderMap = true;
            //this.model.cat = this.buttons[cat]["text"];
	    this.model.cat = this.buttons[cat]["code"];
            this.model.fetch();
        }
        this.drawButtons();
    },

    previousCategory: function () {
        if (this.previous.length > 0) {
            var previous_buttons = this.previous.pop()
            this.buttons = previous_buttons["buttons"];
        }
        else {
            this.buttons = this.cat_json;
        }
        this.refresh = false;
            //$('#address_bar').hide();
            //$('#map-container').hide();
        this.drawButtons();
    },

    addPharmacy: function () {
        document.location = document.location + '#/add';
    },

    initMap: function () {
        var mapDiv = document.getElementById('map');
        map = new google.maps.Map(mapDiv, {
            center: { lat: this.lat, lng: this.long },
            zoom: this.zoom,
            width: '50%'
        });
        this.map = map;
        this.geocoder = new google.maps.Geocoder();

        var input = document.getElementById('lat');
        var searchBox = new google.maps.places.SearchBox(input);

        map.addListener('bounds_changed', function () {
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
             this.zoom = 50;
         }
        }, this));
    },

    addCategoryButtons: function () {
        var cat_view = new CategoryButtonsView({ model: this.model, parent: this });
        this.cat_json = cat_view.getCategoryJSON();
        this.buttons = this.cat_json;

    },

    drawButtons: function () {
        this.$('#treemap').remove();
        this.$('#prev_button').remove();
        var text = "";
        var text2 = "Categories";
        if (this.previous.length > 0) {
            text = this.previous[this.previous.length - 1]["text"];
            if (this.previous.length > 1) {
                text2 = this.previous[this.previous.length - 2]["text"];
            }
            this.$('#prev_category').append('<div id="prev_button"></div>');
            this.$('#prev_button').append('<a href="#" class="previous_button">' + text2 + '</a> > ' + text);
        }
        for (var i = 0; i < this.buttons.length; i++) {
            var obj = this.buttons[i];
            this.$('#treemap-container').append('<div id="treemap"></div>');
            if (obj.hasOwnProperty('children')) {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + ' (+)"/>');
                var r = $('<a class="category program-button menuTooltip" data-cat=' + i + ' title="' + obj.description + '">' + obj.text + ' (+)</a>');

            } else {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + '"/>');
		var r = $('<a class="category program-button menuTooltip" data-cat=' + i + ' title="' + obj.description + '">' +obj.text + '</a>');

            }
            this.$('#treemap').append(r);

        }
    },

    addRows: function (rows) {
        this.$('#listTable').hide();
        if (this.table != undefined) {
            this.table.destroy();
        }
        var fragment = document.createDocumentFragment();
        rows.forEach(function (p, i) {
            var row = new CommunityRowView({ model: p, parent: this });
            row.render();
            fragment.appendChild(row.el);
        }.bind(this));
        this.$("#sopRows").html(fragment);

        // Create the DataTable
        this.createDataTable();

        // Show the DataTable
        this.$('#listTable').show();
        this.$('.dataTables_scrollHead table').show();
        this.$('.DTFC_LeftHeadWrapper table').show();
    },

    createDataTable: function () {
        // Create the DataTable
        this.table = this.$('#listTable').DataTable();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        table = this.table;
    },
    AddMarkers: function (geocoder, group) {
        var pinColor = "FE7569";
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
            new google.maps.Size(21, 34),
            new google.maps.Point(0, 0),
            new google.maps.Point(10, 34));
        var pinShadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow",
            new google.maps.Size(40, 37),
            new google.maps.Point(0, 0),
            new google.maps.Point(12, 35));
        _.each(group, function (val) {
            if (val.PhysicalAddress1 != null) {
                var pharmLoc = null;
                var marker = null;
                var geocodeUrl = (val.PhysicalPostalCode != "") 
                                 ? 'https://geocode.maps.co/search?q=' + val.PhysicalPostalCode
                                 : 'https://geocode.maps.co/search?q=' + val.PhysicalAddress1;
                $.ajax({
                    type: 'GET',
                    url: geocodeUrl,
                    data: { get_param: 'value' },
                    dataType: 'json',
                    success: function (data) {
			if(data.length >0){
                        pharmLoc = new google.maps.LatLng(data[0].lat, data[0].lon);
                        if (pharmLoc != null) {
                                marker = new google.maps.Marker({
                                position: pharmLoc,
                                map: map,
                                data: "pharm",
                                title: val.name,
                                icon: pinImage,
                                shadow: pinShadow
                            });
                var infowindow = new google.maps.InfoWindow({
                    content: "Name: " 
                             + val.PublicName_Program
                             + "<br>"
                             + "Address: "
                             + val.PhysicalAddress1
                             + "<br>" 
                             + "Phone: "
                             + val.Phone1Number
                             + "<br>"
                             + "Email: "
                             + val.EmailAddressMain
			     + "<br>"
                             + "Website: <a href='"
                             + val.WebsiteAddress
			     + "' target='_blank'>" 
			     + val.WebsiteAddress
			     + "</a>"
                });
                
                marker.addListener('click', function(){
                    infowindow.open(map, marker);
                });



                        }
			}
                    }
                });

            }
        });
    },

    render: function () {
        //this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template({
            output: data,
            findCat: this.findCat.bind(this)
        }));


        if(this.renderMap){
            this.initMap();
            var empty = [];
            this.AddMarkers(this.geocoder, empty);
            this.AddMarkers(this.geocoder, data);
            $('#body_accordion').accordion({ autoHeight: false, collapsible: true, header: '> div.wrap >h3' });
            $('#address_bar').show();
            $('#map-container').show();
	    $('#table').show();

        }
        this.addRows(this.model);
        if(this.refresh){
	    $('#address_bar').hide();
            $('#map-container').hide();
	    $('#table').hide();
            this.addCategoryButtons();
	}
            var title = $("#pageTitle").clone();
            $(title).attr('id', 'copiedTitle');
            this.$el.prepend(title);
                   var r = $('<a style="float:right; font-size:0.7em;" href="/index.php/Special:Report?report=SubmitProgram" class="program-button" title="Submit a Program">Submit a Program</a>');
            $('#copiedTitle').append(r);
 
	this.drawButtons();        
        return this.$el;
    }

});


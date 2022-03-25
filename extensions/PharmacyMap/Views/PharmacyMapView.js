PharmacyMapView = Backbone.View.extend({
    template: _.template($('#pharmacy_map_template').html()),
    map: null,
    lat: 44.8052529,
    long: -76.6619867,
    zoom: 8,
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
        if(ev != null){
            var cat = $(ev.currentTarget).data('cat');
            if (this.buttons[cat].hasOwnProperty('children')) {
                this.previous.push({ "buttons": this.buttons, "text": this.buttons[cat]["text"] });
                this.buttons = this.buttons[cat]["children"];
                //$('#address_bar').hide();
                //$('#map-container').hide();
                this.drawButtons();
            }
            else {
                this.refresh = false;
                this.renderMap = true;
                //this.model.cat = this.buttons[cat]["text"];
                this.model.cat = this.buttons[cat]["code"];
                this.model.fetch();
                $(".throbber", ev.currentTarget).show();
            }
        } else {
            this.refresh = false;
            this.renderMap = true;
            this.model.fetch();
            this.drawButtons();
        }
        
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
        this.cat_json = cat_json;
        this.buttons = this.cat_json;
        if(this.model.cat != null){
            //fix buttons
            this.findCategory();
        }
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
            text = ' > ' + text;
        }
        this.$('#prev_category').append('<h3 id="prev_button"></h3>');
        this.$('#prev_button').append('<a href="#" class="previous_button">' + text2 + "</a>" + text);
        for (var i = 0; i < this.buttons.length; i++) {
            var obj = this.buttons[i];
            this.$('#treemap-container').append('<div id="treemap" class="modules"></div>');
            if (obj.hasOwnProperty('children')) {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + ' (+)"/>');
                var r = $('<div class="module-3cols-outer"><a class="category program-button" id="'+obj.code+'" data-cat=' + i + ' title="' + obj.description + '">' + obj.text + ' (+)</a></div>');
            } else {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + '"/>');
                var r = $('<div class="module-3cols-outer"><a class="category program-button" id="'+obj.code+'" data-cat=' + i + ' title="' + obj.description + '">' +obj.text + '<span class="throbber" style="display:none;position:absolute;margin-left:5px;"></span></a></div>');
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


AddMarkers: function (group) {
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
        if (val.Longitude != "" && val.Latitude != "") {
            var pharmLoc = null;
            var marker = null;
            pharmLoc = new google.maps.LatLng(val.Latitude, val.Longitude);
            if (pharmLoc != null) {
                marker = new google.maps.Marker({
                    position: pharmLoc,
                    map: map,
                    data: "pharm",
                    title: val.name,
                    icon: pinImage,
                    shadow: pinShadow
                });
		var phoneNumber = "";
		if(val.PhoneNumbers.length != 0){
		    phoneNumber = val["PhoneNumbers"][0]["Phone"];
		}

                var infowindow = new google.maps.InfoWindow({
                    content: "Name: "
                        + val.PublicName
                        + "<br>"
                        + "Address: "
                        + val.PhysicalAddress1
                        + "<br>"
                        + "Phone: "
                        + phoneNumber
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

                marker.addListener('click', function () {
                    infowindow.open(map, marker);
                });
            }
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
            this.AddMarkers(data);
            var active = (this.previous.length == 0) ? false : 0;
            $('#body_accordion').accordion({ autoHeight: false, collapsible: true, header: '#accordionHeader', active: active });
            $('#accordionHeader').show();
            $('#address_bar').show();
            $('#map-container').show();
            $('#table').show();
        }
        $('#body_accordion .wrap').show();
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
        var r = $('<p class="note_small">Have you used a formal or informal program recently that you don’t see in the library and wish to share with the community?</p><a style="float:right; font-size:0.7em;" href='+wgServer+wgScriptPath+'/index.php/Special:Report?report=SubmitProgram class="program-button" title="Submit a Program">Submit a Program</a>');
            //$('#copiedTitle').append(r);
 
        $('#listTable_filter').css("font-weight","bold");
        $('#listTable_filter').css("font-size","1.6em");
        $('#listTable_filter').css("position","absolute");
        $(".dataTables_filter input").css("width","230px")
                                     .css("vertical-align", "bottom");
        $('#listTable_filter').css("margin-left","5px")
                              .css("left", 0)
                              .css("text-align", "left");
        $(".dataTables_filter input").css("margin-left","15px");
        this.drawButtons();        
        return this.$el;
    }

});

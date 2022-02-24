PharmacyMapView = Backbone.View.extend({
    template: _.template($('#pharmacy_map_template').html()),
    map: null,
    geocoder: null,
    lat:53.7608608,
    long:-98.8138763,
    zoom:4,
    refresh:true,
    buttons: [],
    cat_json: null,
    category:null,
    previous:[],
    renderMap:false,
    initialize: function(){
        this.model.bind('sync', this.render);//change to on
    },

    events:{
        "click #addPharmacy": "addPharmacy",
        "click #findLocation": "findLocation",
        "click #printMap": "printMap",
	"click .category": "findCategory",
	"click .previous_button": "previousCategory",

    },

    printMap: function(){
        window.print();
    },

    findCat: function(clicked_cat){
        this.model.cat = clicked_cat;
        this.model.fetch();
    },

    findLocation: function(){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition($.proxy(function(position) {
                  var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                  };
                this.map.setCenter(pos);
                this.zoom = 10;
            },this), function() {
            });
         }
         else{
            map.setCenter(map.getCenter());
         }
    },


    findCategory: function (ev) {
        var cat = $(ev.currentTarget).data('cat');;
        //this.model.cat = this.cat;
        //this.model.fetch();
	console.log(cat);
	console.log(this.buttons);
        if (this.buttons[cat].hasOwnProperty('children')) {
            this.previous.push(this.buttons);
            this.buttons = this.buttons[cat]["children"];
            t
        }
        else {
            console.log(this.buttons[cat]["text"]);
            console.log("render map");
        }
    },

    previousCategory: function () {
        if (this.previous.length > 0) {
            console.log(this.previous);
            var lastItem = this.previous.pop();
            this.buttons = this.lastItem;
            this.drawButtons();
        }
        else {
            this.buttons = this.cat_json;
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
        width:'50%'
    });
    this.map = map;
    this.geocoder = new google.maps.Geocoder();

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
         
	 if(places.length >1){
         map.fitBounds(bounds);
         }
         else if(places.length==1){
             map.setCenter(bounds.getCenter());
             this.zoom = 10;
         }
        }, this));
    },

	/*addCategoryButtons: function(){
	var buttons = new CategoryButtonsView({model:this.model, parent:this});
	buttons.render();
	this.$('#treemap').html(buttons);
	},*/

	addCategoryButtons: function(){
		var cat_view = new CategoryButtonsView({model:this.model, parent:this});
		this.cat_json = cat_view.getCategoryJSON();	
		this.buttons = this.cat_json;
		$('#currentView').accordion({autoHeight: false, collapsible: true, header: '> div.wrap >h3'});

        },

    drawButtons: function () {
        this.$('#treemap').remove();
        this.$('#prev_button').remove();
        if (this.previous.length > 0) {
            var text = this.previous[this.previous.length - 1]["text"];
            this.$('#prev_category').append('<div id="prev_button"></div>');
            this.$('#prev_button').append('<a href="#" class="previous_button">'+text+'</a>');
        }
        for (var i = 0; i < this.buttons.length; i++) {
            var obj = this.buttons[i];
            this.$('#treemap-container').append('<div id="treemap"></div>');
            var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + '"/>');
            this.$('#treemap').append(r);
        }
    },

	addRows: function(rows){
        this.$('#listTable').hide();
        if(this.table != undefined){
            this.table.destroy();
        }
        var fragment = document.createDocumentFragment();
        rows.forEach(function(p, i){
            var row = new CommunityRowView({model: p, parent: this});
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

	createDataTable: function(){
        // Create the DataTable
        this.table = this.$('#listTable').DataTable();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        table = this.table;
    },
    AddMarkers: function(geocoder, group){
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
            if(val.PhysicalAddress1 != null){
		    var pharmLoc = null;
		    $.ajax({
                    	type: 'GET',
                    	url: 'https://geocode.maps.co/search?q='+val.PhysicalAddress1,
                    	data: { get_param: 'value' },
                    	dataType: 'json',
                    	success: function (data) {
                        console.log(data);
			pharmLoc = new google.maps.LatLng(data[0].lat, data[0].lon);
				console.log(data[0].lat)
				console.log(data[0].lon);
			if (pharmLoc != null){
                        var marker = new google.maps.Marker({
                         position: pharmLoc,
                        map: map,
                        data:"pharm",
                        title:val.name,
                        icon: pinImage,
                        shadow: pinShadow
                        });
                }

                    	}
                   });



                /*var infowindow = new google.maps.InfoWindow({
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
                });*/
            }
        });
    },

    render: function(){
        //this.$el.empty();
        var data = this.model.toJSON();
        if(this.refresh){
        	this.$el.html(this.template({
		output:data,	
		findCat: this.findCat.bind(this)}));
	}
    //	this.initMap();
        var empty = [];
        //this.AddMarkers(this.geocoder, empty);
    //	this.AddMarkers(this.geocoder, data);
	this.addRows(this.model);
	if(this.refresh){
    		//this.buildTreemap();
		this.addCategoryButtons();
		this.drawButtons();
    		var title = $("#pageTitle").clone();
    		$(title).attr('id', 'copiedTitle');
    		this.$el.prepend(title);
	}
        return this.$el;
    }

});

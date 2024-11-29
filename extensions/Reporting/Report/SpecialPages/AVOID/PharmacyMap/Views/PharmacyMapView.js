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
    arrmarkers: [],
    interval: null,
    infowindows: [],
    renderMap: false,
    category_text: "",
    note: null,
    clipboard: null,
    
    // AI Stuff
    isListening: false,
    recognition: null,
    
    initialize: function () {
        dc.init(me.get('id'), 'ProgramLibrary');
        dc.increment("count");
    
        this.model.bind('sync', this.render);//change to on
        
        $(document).on('click', 'a.programWebsite', function(){
            this.clickWebsite(this.model.cat);
        }.bind(this));
        
        $(window).resize(function(){
            if($("#questionsDialog").is(':visible')){
                $("#questionsDialog").dialog({
                    width: 'auto',
                    height: $(window).height()*0.90,
                });
                $("#questionsDialog").dialog({
                    position: { 'my': 'center', 'at': 'center' }
                });
            }
            if($('#how').is(':visible')){
                $(".ui-dialog").css("top", "25%");
            }
        });
    },

    events: {
        "click #addPharmacy": "addPharmacy",
        "click #findLocation": "findLocation",
        "click #printMap": "printMap",
        "click .category": "findCategory",
        "click .previous_button": "previousCategory",
        "click #questions": "clickQuestions",
        "keypress #keywordsearch": "keywordSearch",
        "click #keysearch_btn": "keywordSearch",
        "click #howLink": "clickHow",
        "click #aiSearchButton": "clickAiSearchButton",
        "keypress #messageInput": "enterAiSearch",
        "click #sendButton": "clickSend",
        "click #micButton": "clickMic"
    },
    
    clickHow: function(){
        $('#how').dialog({
            title: "<en>How do I use this page?</en><fr>Comment puis-je utiliser cette page?</fr>",
            modal: false,
            resizable: false,
            position: { 'my': 'center', 'at': 'center' },
            width: '50%'
        });
        $('.ui-dialog').addClass('program-body').css('margin-bottom', 0);
        $(window).resize();
    },
    
    clickAiSearchButton: function(){
        this.$("#aiSearch").slideDown();
        this.$("#aiSearchButton").hide();
        this.$("#searchbar_key").hide();
        //this.aiSearch();
    },
    
    enterAiSearch: function(event){
        if (event.key === "Enter") {
            event.preventDefault();
            $("#sendButton").click();
        }
    },
    
    clickSend: function(){
        var input = $('#messageInput');
        if ($(input).val().trim() !== '') {
            var text = input.val()
            var li = document.createElement('li');
            li.textContent = text;
            document.getElementById('messageList').appendChild(li);
            $(input).val(''); // Clear input after sending

            var data = {queryText: text};
            $('.chat-box .throbber').show();
            $.post(wgServer + wgScriptPath + "/index.php?action=api.aiSearch", data, function(response){
                var li = document.createElement('li');
                li.textContent = response.message;
                li.classList.add("ai");
                document.getElementById('messageList').appendChild(li);
                $('.chat-box .throbber').hide();
                this.aiSearch(response.documents);
            }.bind(this));
        }
    },
    
    handleInputChange: function(event) {
        //setInputText(event.target.value);
        console.log(event.target.value);
    },
    
    toggleListening: function(){
        if (this.isListening) {
            this.recognition.stop();
            this.isListening = false;
        } else {
            if ('webkitSpeechRecognition' in window) {
                this.recognition = new window.webkitSpeechRecognition();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = 'en-US';
                this.recognition.onstart = () => {
                    this.isListening = true;
                };
                this.recognition.onresult = function(event) {
                    transcript = event.results[0][0].transcript;
                    $('#messageInput').val(transcript);
                    this.clickSend();
                    this.clickMic();
                }.bind(this);
                this.recognition.onerror = function(event) {
                    console.error('Speech recognition error', event);
                    this.isListening = false;
                };
                this.recognition.onend = function() {
                    this.isListening = false;
                };
                this.recognition.start();
            } else {
                console.warn('Speech recognition not supported in this browser.');
            }
        }
    },
    
    clickMic: function(){
        var micButton = document.getElementById('micButton')
        micButton.textContent = micButton.textContent === 'Mic' ? 'Mic Off' : 'Mic';
        this.toggleListening();
    },
    
    aiSearch: function(docs){        
        var rows = new AvoidResources();
        _.each(docs, function(doc){
            var row = new AvoidResource({
                id: _.uniqueId("ai"),
                PublicName: doc.service_name,
                PhysicalAddress1: doc.address,
                PhysicalCity: doc.city,
                WebsiteAddress: doc.website,
                PhysicalAddress2: doc.address,
                PhysicalStateProvince: doc.province,
                PhysicalPostalCode: doc.postal_code,
                MailingPostalCode: doc.postal_code,
                PhoneNumbers: [{"Phone": doc.phone_number}],
                Latitude: doc.latitude,
                Longitude: doc.longitude
            });
            rows.add(row);
        });
        this.renderMap = true;
        this.refresh = false;
        this.model.reset(rows.toJSON());
        this.addRows(this.model);
        //this.render();
        this.$("#table").show();
        this.refreshMap();
        this.$('#body_accordion').accordion({ autoHeight: false, collapsible: true, header: '#accordionHeader'});
        this.$('#accordionHeader').show();
        this.$('#address_bar').show();
        this.$('#map-container').show();
        if($("#body_accordion").accordion("option", "active") !== false){
            $('#accordionHeader').click();
        }
    },

    keywordSearch(e){
        if (event.key === "Enter" || e.currentTarget.id == 'keysearch_btn') {
            this.refresh = false;
            this.renderMap = true;
            this.model.cat = null;
            this.model.key = $('#keywordsearch').val();
            $("#searchbar_key").append('<img style="height:4em;" src='+wgServer+wgScriptPath+'/skins/throbber.gif>');
            this.model.fetch(); 
        }
    },

    clickQuestions(e){
        $("#questionsDialog").dialog({
            title: showLanguage(wgLang, "Questions to consider", "Questions à poser avant de s’inscrire à des ressources communautaires"),
            width: 'auto',
            height: $(window).height()*0.90,
            resizable: false
        });
        $('.ui-dialog').addClass('program-body');
        $(window).resize();
        return false;
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
    
    clickWebsite: function(cat){
        dc.init(me.get('id'), 'ProgramLibrary-' + cat);
        dc.increment("websiteClicks");
        $.when(dc.ready()).then(function(){
            $.get(wgServer + wgScriptPath + '/index.php/Special:PharmacyMap?clickedProgram');
        });
    },
    
    record: function(cat){
        dc.init(me.get('id'), 'ProgramLibrary-' + cat);
        dc.increment("pageCount");
    },

    findCategory: function (ev) {
        if(ev != null){
            // Clicked category button
            var cat = $(ev.currentTarget).data('cat');
            this.record(this.buttons[cat]["code"]);
            if (this.buttons[cat].hasOwnProperty('children')) {
                this.previous.push({ "buttons": this.buttons, "text": this.buttons[cat]["text"], "code": this.buttons[cat]["code"] });
                this.buttons = this.buttons[cat]["children"];
                this.drawButtons();
            }
            else {
                var id_cat = "#"+this.buttons[cat]["code"];
                this.category_text = $(id_cat).html();
                this.refresh = false;
                this.renderMap = true;
                this.model.cat = this.buttons[cat]["code"];
                this.note = this.buttons[cat]["note"];
                this.model.key = null;
                this.model.fetch();
                $(".throbber", ev.currentTarget).show();
            }
        } else {
            // Category Route
            // This is super ugly, but it works...
            this.record(this.model.cat);
            _.each(this.cat_json, function(button1){
                if(button1.hasOwnProperty('children')){
                    _.each(button1.children, function(button2){
                        if(button2.hasOwnProperty('children')){
                            _.each(button2.children, function(button3){
                                if(button3.code == this.model.cat){
                                    if(button3.hasOwnProperty('children')){
                                        this.buttons = button3.children;
                                    }
                                    else{
                                        this.buttons = button2.children;
                                    }
                                }
                            }.bind(this));
                        }
                        if(button2.code == this.model.cat){
                            if(button2.hasOwnProperty('children')){
                                this.buttons = button2.children;
                            }
                            else{
                                this.buttons = button1.children;
                            }
                        }
                    }.bind(this));
                }
                if(button1.code == this.model.cat){
                    this.buttons = button1.children;
                }
            }.bind(this));
            this.refresh = false;
            this.renderMap = true;
            this.model.fetch();
            this.drawButtons();
        }
    },

    previousCategory: function () {
        if (this.previous.length > 0) {
            var previous_buttons = this.previous.pop();
            this.buttons = previous_buttons["buttons"];
            if(this.previous.length > 0){
                this.record(_.last(this.previous)['code']);
            }
            else{
                this.record("INDEX");
            }
        }
        else {
            this.buttons = this.cat_json;
            this.record("INDEX");
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
        searchBox.addListener('places_changed', $.proxy(function () {
            var places = searchBox.getPlaces();
            if (places.length == 0) {
                return;
            }

            var bounds = new google.maps.LatLngBounds();
            places.forEach($.proxy(function (place) {
                this.lat = place.geometry.location.lat();
                this.long = place.geometry.location.lng();

                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            }, this));
            if (places.length > 1) {
                map.fitBounds(bounds);
            }
            else if (places.length == 1) {
                map.setCenter(bounds.getCenter());
                this.zoom = 50;
            }
        }, this));
    },

    addCategoryButtons: function () {
        if(this.model.cat == undefined){
            this.record("INDEX");
        }
        var cat_view = new CategoryButtonsView({ model: this.model, parent: this });
        this.cat_json = cat_json;
        this.buttons = this.cat_json;
        if (this.model.cat != null) {
            //fix buttons
            this.findCategory();
        }
    },

    drawButtons: function () {
        this.addRows(new AvoidResources());
        var hash = document.location.hash.replace("#", "");
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
        this.$('#prev_button').append('<a href="#' + hash + '" class="previous_button">' + text2 + "</a>" + text);
        for (var i = 0; i < this.buttons.length; i++) {
            var obj = this.buttons[i];
            this.$('#treemap-container').append('<div id="treemap" class="modules"></div>');
            var description = (wgLang == 'en') ? obj.description : obj.descriptionFr;
            if (obj.hasOwnProperty('children')) {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + ' (+)"/>');
                var r = $('<div class="module-3cols-outer" style="height: 2.25em;"><a style="overflow:hidden;text-overflow:ellipsis;" href="#' + hash + '" class="category program-button" id="'+obj.code+'" data-cat=' + i + ' title="' + description + '">' + obj.text + ' (+)</a></div>');
            } else {
                //var r = $('<input type="button" width="25%" class="category" data-cat=' + i + ' title="' + obj.description + '" value="' + obj.text + '"/>');
                var r = $('<div class="module-3cols-outer" style="height: 2.25em;"><a style="overflow:hidden;text-overflow:ellipsis;" href="#' + hash + '" class="category program-button" id="'+obj.code+'" data-cat=' + i + ' title="' + description + '">' +obj.text + '<span class="throbber" style="display:none;position:absolute;margin-left:5px;"></span></a></div>');
            }
            this.$('#treemap').append(r);
        }
        this.$('#treemap').append('<div class="module-3cols-outer" style="height: 2.25em;"></div>');
    },

    addRows: function (rows) {
        this.$('#listTable').hide();
        if (this.table != undefined) {
            this.table.destroy();
        }
        if(this.note == null || this.note == "No notes"){
            this.note = "No notes";
        }
        var fragment = document.createDocumentFragment();
        rows.forEach(function (p, i) {
            var row = new CommunityRowView({ model: p, parent: this, category:this.category_text, note:this.note, clipboard:this.clipboard});
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

        this.initMap();
        this.AddMarkers(rows.toJSON());
        if(rows.length > 0){
            this.$("#map").show();
        }
        else{
            this.$("#map").hide();
        }
    },

    createDataTable: function () {
        // Create the DataTable
        var options = (networkFullName == 'AVOID Australia') ? {} : {"scrollY": "650px"};
        this.table = this.$('#listTable').DataTable(options);
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        table = this.table;
    },

    rowClick: function () {
        google.maps.event.trigger(this.arrmarkers[i], "click");
    },

    refreshMap: function () {
        this.initMap();
        this.AddMarkers(this.model.toJSON());
    },


    AddMarkers: function (group) {
        var pinImage = new google.maps.MarkerImage("https://developers.google.com/static/maps/documentation/javascript/images/default-marker.png",
            new google.maps.Size(26, 37),
            new google.maps.Point(0, 0),
            new google.maps.Point(13, 37));
        var i = 0;
        var lat = 0;
        var long = 0;
        var bounds = new google.maps.LatLngBounds();
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
                        icon: pinImage
                    });
                    bounds.extend(pharmLoc);
                    this.arrmarkers.push(marker);
                    i++;
                    var phoneNumber = "";
                    if (val.PhoneNumbers.length != 0) {
                        phoneNumber = val["PhoneNumbers"][0]["Phone"];
                    }

                    var infowindow = new google.maps.InfoWindow({
                        content: "<en>Name:</en><fr>Nom:</fr> "
                            + val.PublicName
                            + "<br>"
                            + "<en>Address:</en><fr>Adresse:</fr> "
                            + val.PhysicalAddress1
                            + "<br>"
                            + "<en>Phone:</en><fr>Numéro de téléphone:</fr> "
                            + phoneNumber
                            + "<br>"
                            + "<en>Email:</en><fr>Courriel:</fr> "
                            + val.EmailAddressMain
                            + "<br>"
                            + "<en>Website:</en><fr>Site Internet:</fr> <a class='programWebsite' href='"
                            + val.WebsiteAddress
                            + "' target='_blank'>"
                            + val.WebsiteAddress
                            + "</a>"
                    });
                    this.infowindows.push(infowindow);
                    marker.addListener('click', function () {
                        for (var i = 0; i < this.infowindows.length; i++) {
                            this.infowindows[i].close();
                        }
                        for (var i=0; i < this.arrmarkers.length; i++){
                            this.arrmarkers[i].setAnimation(null);
                        }
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        infowindow.open(map, marker);
                        var input = $('input[type="search"]');
                        input.val("\"" + val.PublicName + "\"" + " " + val.PhysicalAddress1);
                        var e = $.Event("keyup", { keyCode: 13 });
                        input.trigger(e);
                    }.bind(this));


                    //close all tabs and open tab that is clicked on table
                    var tr_id = "#row_" + val.id;
                    var tr = $(tr_id).parent();
                    tr.click(function () {
                        for (var i = 0; i < this.infowindows.length; i++) {
                            this.infowindows[i].close();
                        }
                        for (var i=0; i < this.arrmarkers.length; i++){
                            this.arrmarkers[i].setAnimation(null);
                        }
                        infowindow.open(map, marker);
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        map.setZoom(10);
                        map.panTo(marker.getPosition());
                    }.bind(this));

                    //closing info tab by clicking on outside of map
                    google.maps.event.addListener(map, "click", function (event) {
                        infowindow.close();
                        marker.setAnimation(null);
                        var input = $('input[type="search"]');
                        input.val("");
                        var e = $.Event("keyup", { keyCode: 13 });
                        input.trigger(e);
                    });
                    
                    lat += parseFloat(val.Latitude);
                    long += parseFloat(val.Longitude);
                }
            }
            map.setCenter({ lat: lat/i, lng: long/i });
            if(i == 1){
                map.setZoom(15);
            }
            else{
                map.fitBounds(bounds);
            }
        }.bind(this));
    },

    render: function () {
        //this.$el.empty();
        main.set('title', showLanguage(wgLang, 'Using the Community Program Library', 'Utilisation du Répertoire des ressources communautaires'));
        var data = this.model.toJSON();
        $("#questionsDialog").remove();
        this.$el.html(this.template({
            output: data,
            findCat: this.findCat.bind(this)
        }));
        if(me.isLoggedIn()){
            this.clipboard = new PersonClipboard();
            this.clipboard.fetch({
                success: function () {
                    this.addRows(this.model);
                }.bind(this)
            });
        }
        else {
            this.addRows(this.model);
        }

        if(this.renderMap){
            this.initMap();
            this.AddMarkers(data);
            $('#body_accordion').accordion({ autoHeight: false, collapsible: true, header: '#accordionHeader'});
            $('#accordionHeader').show();
            $('#address_bar').show();
            $('#map-container').show();
        }
        $('#body_accordion .wrap').show();
        if (this.refresh) {
            $('#address_bar').hide();
            $('#map-container').hide();
            $('#table').hide();
            this.addCategoryButtons();
        }
        var title = $("#pageTitle").clone();
        $(title).attr('id', 'copiedTitle');
        this.$el.prepend(title);
        var r = $('<p class="note_small">Have you used a formal or informal program recently that you don’t see in the library and wish to share with the community?</p><a style="float:right; font-size:0.7em;" href=' + wgServer + wgScriptPath + '/index.php/Special:Report?report=SubmitProgram class="program-button" title="Submit a Program">Submit a Program</a>');
        //$('#copiedTitle').append(r);

        $('#listTable_filter').css("font-weight", "bold");
        $('#listTable_filter').css("font-size", "1.6em");
        $('#listTable_filter').css("position", "absolute");
        $(".dataTables_filter input").css("width", "230px")
            .css("vertical-align", "bottom");
        $('#listTable_filter').css("margin-left", "5px")
            .css("left", 0)
            .css("text-align", "left");
        $(".dataTables_filter input").css("margin-left", "15px");
        this.drawButtons();
        $(document).on('click', '.paginate_button', function () {
            this.refreshMap();
        }.bind(this));
        if(this.model.key != null){
            $('#keywordsearch').val(this.model.key);
            if($("#body_accordion").accordion("option", "active") !== false){
                $('#accordionHeader').click();
            }
            $(".searches").append("<br /><b>"+data.length+"</b> Results for Keyword Search: <b>"+this.model.key+"</b>");
        }
        
        return this.$el;
    }

});


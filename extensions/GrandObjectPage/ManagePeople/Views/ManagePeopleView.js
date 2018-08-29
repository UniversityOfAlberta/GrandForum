ManagePeopleView = Backbone.View.extend({

    table: null,
    subViews: new Array(),
    allPeople: null,
    people: null,
    addNewMemberDialog: null,
    addExistingMemberDialog: null,

    initialize: function(){
        this.allPeople = new People();
        this.allPeople.simple = true;
        this.allPeople.fetch();
        this.listenTo(this.allPeople, "sync", this.updateExistingMember);
        this.template = _.template($('#manage_people_template').html());
        this.listenTo(this.model, "sync", function(){            
            // First remove deleted models
            _.each(this.subViews, $.proxy(function(view){
                    view.remove();
            }, this));
            this.subViews = new Array();
            this.people = this.model;
            this.listenTo(this.people, "add", this.addRows);
            this.listenTo(this.people, "remove", this.addRows);
            this.render();
        }, this);
    },
    
    addRows: function(){
        var searchStr = "";
        var order = [2, 'asc'];
        var order = [[4, "desc"], [3, "desc"]];
        if(_.contains(allowedRoles, MANAGER)){
            order = [4, 'asc'];
        }
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        // First remove deleted models
        _.each(this.subViews, $.proxy(function(view){
            var m = view.model;
            if(this.people.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }, this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        this.people.each($.proxy(function(p, i){
            if(!_.contains(models, p.id)){
                // Person isn't in the table yet
                var row = new ManagePeopleRowView({model: p, parent: this});
                this.subViews.push(row);
                this.$("#personRows").append(row.$el);
            }
        }, this));
        _.each(this.subViews, function(row){
            row.render();
        });
        var end = new Date();
        this.createDataTable(order, searchStr);
    },
    
    invalidate: _.debounce(function(){
        this.table.rows().invalidate('dom').draw();
    }, 1),
    
    // Sanity Check 3: Check for duplicate HQP
    checkHQPDuplicates: function(){
        deleteHqp = $.proxy(function(button, id){ //global function
            $(button).parent().append("<img src='" + wgServer + wgScriptPath + "/skins/Throbber.gif' />");
            var data = 'id=' + id;
            $.ajax({
                type: 'POST',
                url: 'index.php?action=deleteDuplicates&handler=hqp',
                data: data,
                success: $.proxy(function (data) {
                    $('.hqp' + id).prev().html('DELETED - ' + $('.hqp' + id).prev().html());
                    $('.hqp' + id).prev().css('color', '#00aa00');
                    $('.hqp' + id).remove();
                    // stopListenings were required to not render twice
                    this.stopListening(this.model, "add");
                    this.stopListening(this.model, "remove");
                    this.allPeople.fetch();
                }, this)
            });
        }, this);
        
        mergeHqp = $.proxy(function(button, id1, id2){ //global function
            $(button).parent().append("<img src='" + wgServer + wgScriptPath + "/skins/Throbber.gif' />");
            var data = 'id1=' + id1 + '&id2=' + id2;
            $.ajax({
                type: 'POST',
                url: 'index.php?action=mergeDuplicates&handler=hqp',
                data: data,
                success: $.proxy(function (data) {
                    $('#hqp' + id1 + '_' + id2).prev().html('MERGED - ' + $('#hqp' + id1 + '_' + id2).prev().html());
                    $('#hqp' + id1 + '_' + id2).prev().css('color', '#00aa00');
                    $('#hqp' + id1 + '_' + id2).remove();
                    // stopListenings were required to not render twice
                    this.stopListening(this.model, "add");
                    this.stopListening(this.model, "remove");
                    this.allPeople.fetch();
                }, this)
            });
        }, this);
        
        ignoreHqp = $.proxy(function(button, id1, id2){ //global function
            $(button).parent().append("<img src='" + wgServer + wgScriptPath + "/skins/Throbber.gif' />");
            var data = 'id1=' + id1 + '&id2=' + id2;
            $.ajax({
                type: 'POST',
                url: 'index.php?action=ignoreDuplicates&handler=hqp',
                data: data,
                success: function (data) {
                    $('#hqp' + id1 + '_' + id2).prev().html('NOT DUPLICATES - ' + $('#hqp' + id1 + '_' + id2).prev().html());
                    $('#hqp' + id1 + '_' + id2).prev().css('color', '#00aa00');
                    $('#hqp' + id1 + '_' + id2).remove();
                }
            });
        }, this);
        
        var outputSoFar = '';
        var ajaxRequest;  // The variable that makes Ajax possible!
        
        try{
            // Opera 8.0+, Firefox, Safari
            ajaxRequest = new XMLHttpRequest();
        } catch (e){
            // Internet Explorer Browsers
            try{
                ajaxRequest = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try{
	                ajaxRequest = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e){

                }
            }
        }
        // Create a function that will receive data sent from the server
        var processing = false;
        ajaxRequest.onreadystatechange = function(){
            var text = ajaxRequest.responseText;
            if((ajaxRequest.readyState == 3 || ajaxRequest.readyState == 4) && (text.match(/<\/div>$/) || text.match(/<\/span>$/))){
                diffText = text.replace(outputSoFar, '');
                
                outputSoFar += diffText;
                $('#hqpDuplicates').append(diffText);
                
                var percent = parseInt($('#hqpDuplicates span:last').attr('class'));
                $('#hqpDuplicatesProgress').progressbar({
                    value: percent
                });
                
                $('#hqpDuplicatesProgress div').html('<center>' + percent + '%</center>');
                
                $.each($('#hqpDuplicates div table'), function(index, val){
                    if(!$(val).hasClass('recommended')){
                        var leftInsLength = 0;
                        var leftDelLength = 0;
                        var rightInsLength = 0;
                        var rightDelLength = 0;
                        var leftLength = 0;
                        var rightLength = 0;
                        $.each($('.left', $(val)), function(i, v){
                            leftLength += $(v).text().length;
                        });
                        $.each($('.right', $(val)), function(i, v){
                            rightLength += $(v).text().length;
                        });
                        
                        $.each($('.left ins', $(val)), function(i, v){
                            leftInsLength += $(v).text().length;
                        });
                        $.each($('.left del', $(val)), function(i, v){
                            leftDelLength += $(v).text().length;
                        });
                        $.each($('.right ins', $(val)), function(i, v){
                            rightInsLength += $(v).text().length;
                        });
                        $.each($('.right del', $(val)), function(i, v){
                            rightDelLength += $(v).text().length;
                        });
                        leftLength -= (leftInsLength + leftDelLength);
                        rightLength -= (rightInsLength + rightDelLength);
                        
                        leftConfidence = (leftInsLength/(leftInsLength + rightInsLength + 1) - leftDelLength/(leftDelLength + rightDelLength + 1));
                        rightConfidence = (rightInsLength/(leftInsLength + rightInsLength + 1) - rightDelLength/(leftDelLength + rightDelLength + 1));
                        
                        leftConfidence += (1 - Math.abs(leftConfidence)) * leftLength/Math.max(1, (leftLength + leftInsLength + leftDelLength));
                        rightConfidence += (1 - Math.abs(rightConfidence)) * rightLength/Math.max(1, (rightLength + rightInsLength + rightDelLength));
                        
                        leftConfidence *= 100;
                        rightConfidence *= 100;
                        
                        if(leftConfidence >= 100 || rightConfidence >= 100){
                            $(val).children().append('<tr><td><b>Recommendation:</b> Delete</td><td><b>Recommendation:</b> Keep</td></tr>');
                            $(val).parent().append('<b>Confidence:</b> ' + 100.00 + '%');
                            $(val).parent().prev().html('Identical (100.00%) - ' + $(val).parent().prev().html());
                            $(val).parent().prev().css('color', '#ff0000');
                        }
                        else if(leftConfidence >= 80 && rightConfidence <= leftConfidence){
                            $(val).children().append('<tr><td><b>Recommendation:</b> Keep</td><td><b>Recommendation:</b> Delete</td></tr>');
                            $(val).parent().append('<b>Confidence:</b> ' + leftConfidence.toFixed(2) + '%');
                            $(val).parent().prev().html('High Prob (' + leftConfidence.toFixed(2) + '%) - ' + $(val).parent().prev().html());
                            $(val).parent().prev().css('color', '#ff8800');
                        }
                        else if(rightConfidence >= 80 && leftConfidence <= rightConfidence){
                            $(val).children().append('<tr><td><b>Recommendation:</b> Delete</td><td><b>Recommendation:</b> Keep</td></tr>');
                            $(val).parent().append('<b>Confidence:</b> ' + rightConfidence.toFixed(2) + '%');
                            $(val).parent().prev().html('High Prob (' + rightConfidence.toFixed(2) + '%) - ' + $(val).parent().prev().html());
                            $(val).parent().prev().css('color', '#ff8800');
                        }
                        else {
                            $(val).children().append('<tr><td><b>Recommendation:</b> Ignore</td><td><b>Recommendation:</b> Ignore</td></tr>');
                            $(val).parent().append('<b>Confidence:</b> ' + Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2) + '%');
                            $(val).parent().prev().html('Low Prob (' + Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2) + '%) - ' + $(val).parent().prev().html());
                            $(val).parent().prev().css('color', '#0088ff');
                        }
                        $(val).addClass('recommended');
                        var confidence = Math.max(Math.abs(rightConfidence), Math.abs(leftConfidence)).toFixed(2);
                        $(val).parent().prev().attr('confidence', confidence);
                        
                        $.each($('#hqpDuplicates div table'), function(i, v){
                            if($(v).hasClass('recommended')){
                                conf = $(v).parent().prev().attr('confidence');
                                if(parseFloat(conf) < parseFloat(confidence)){
                                    var header = $(val).parent().prev().detach();
                                    var table = $(val).parent().detach();
                                    header.insertBefore($(v).parent().prev());
                                    table.insertBefore($(v).parent().prev());
                                    return false;
                                }
                            }
                        });
                    }
                });
            }
            if(ajaxRequest.readyState == 4){ // diable & enables button
                $('#hqpDuplicatesButton').prop('disabled', false);                   
            }  
        }
        $('#hqpDuplicates').empty(); // empties the div s.t. it does not keep appending
        $('#hqpDuplicatesButton').prop('disabled', true);   
        ajaxRequest.open('GET', 'index.php?action=getDuplicates&handler=hqp', true);
        ajaxRequest.send(null);          
    },
    
    createDataTable: function(order, searchStr){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
	                                                 'aLengthMenu': [[-1], ['All']]});
	    this.table.draw();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
    },
    
    updateExistingMember: function(){
        if(this.allPeople.length > 0){
            this.$("#addExistingMember").prop("disabled", false);
        }
        else{
            this.$("#addExistingMember").prop("disabled", true);
        }
    },
    
    addNewMember: function(){
        this.addNewMemberDialog.dialog('open');
    },
    
    addExistingMember: function(){
        if(this.$("#selectExistingMember").data("chosen") != undefined){
            this.$("#selectExistingMember").chosen('destroy');
        } 
        this.$("#selectExistingMember").empty();
        this.addExistingMemberDialog.dialog('open');
        var people = new Array();
        _.each(this.allPeople.sortBy('reversedName'), $.proxy(function(p){
            var fullname = p.get('reversedName');
            if(p.get('email') != ""){
                fullname += " (" + p.get('email').split('@')[0] + ")";
            }
            people.push("<option value='" + p.get('id') + "'>" + fullname + "</option>");
        }, this));
        $("#selectExistingMember").html(people.join());
        $("#selectExistingMember").chosen();
        this.addExistingMemberDialog.parent().css('overflow', 'visible');
    },
    
    events: {
        "click #addNewMember": "addNewMember",
        "click #addExistingMember": "addExistingMember",
        "click #hqpDuplicatesButton": "checkHQPDuplicates"
    },
    
    render: function(){
        this.$el.html(this.template());
        this.updateExistingMember();
        this.addRows();
        this.$("#hqpDuplicatesProgress").progressbar({
            value: 0
        });
        enableAddButton = function() {}; // just set this to an empty function for now
        this.addNewMemberDialog = this.$("#addNewMemberDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: "550px",
	        position: {
                my: "center center",
                at: "center center"
            },
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Add": $.proxy(function(e){
	                var addButton = e.currentTarget;
	                enableAddButton = function(){ // Used by child frame
	                    $(addButton).prop('disabled', false);
	                }
	                closeAddHQP = $.proxy(function(){ // Used by child frame
	                    this.addNewMemberDialog.dialog('close');
	                    clearSuccess();
	                    addSuccess("User created successfully");
	                    this.model.fetch();
	                }, this);
	                $(addButton).prop('disabled', true);
	                if(document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=ignore_warnings]').length > 0){
	                    document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=ignore_warnings]').click();
	                }
                    else{ 
                        document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=submit]').click();
                    }
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.addNewMemberDialog.dialog('close');
	            }, this)
	        }
	    });
        this.addExistingMemberDialog = this.$("#addExistingMemberDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: "500px",
	        position: {
                my: "center bottom",
                at: "center center"
            },
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Add": $.proxy(function(e){
	                var id = $("#selectExistingMember").val();
	                $.post(wgServer + wgScriptPath + "/index.php?action=api.people/managed", {id: id})
	                .done($.proxy(function(){
	                    var person = this.allPeople.findWhere({'id': id});
	                    person.fetch();
	                    this.people.add(person);
	                }, this))
	                .fail($.proxy(function(){
	                    addError("There was a problem adding this person");
	                }, this));
                    this.addExistingMemberDialog.dialog('close');
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.addExistingMemberDialog.dialog('close');
	            }, this)
	        }
	    });
	    this.$el.append(this.addNewMemberDialog.parent());
	    this.$el.append(this.addExistingMemberDialog.parent());
        return this.$el;
    }

});

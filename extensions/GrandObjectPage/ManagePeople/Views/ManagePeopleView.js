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
        this.listenTo(this.model, "reset", function(){
            // First remove deleted models
            _.each(this.subViews, function(view){
                    view.remove();
            }.bind(this));
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
        _.each(this.subViews, function(view){
            var m = view.model;
            if(this.people.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }.bind(this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        this.people.each(function(p, i){
            if(!_.contains(models, p.id)){
                // Person isn't in the table yet
                var row = new ManagePeopleRowView({model: p, parent: this});
                this.subViews.push(row);
                this.$("#personRows").append(row.$el);
            }
        }.bind(this));
        _.each(this.subViews, function(row){
            row.render();
        });
        var end = new Date();
        this.createDataTable(order, searchStr);
    },
    
    invalidate: _.debounce(function(){
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
        this.createDataTable(order, searchStr);
    }, 1),
    
    createDataTable: function(order, searchStr){
        var creating = true;
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'preDrawCallback': function(){
                                                        return !creating;
                                                     },
                                                     'autoWidth': false,
                                                     'fixedHeader': true,
	                                                 'aLengthMenu': [[-1], ['All']]});
	    creating = false;
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
        
        $("#selectExistingMember").html("<option></option>");
        $("#selectExistingMember").chosen({width: "99%"});
        $("#selectExistingMember").trigger("chosen:activate");
        xhr = null;
        var changeFn = function(e){
            _.defer(function(){
                if(e.keyCode == 37 ||
                   e.keyCode == 38 ||
                   e.keyCode == 39 ||
                   e.keyCode == 40 ||
                   e.keyCode == 13){
                    // Arrows/Enter key
                    return;  
                }
                if(xhr != null){
                    xhr.abort();
                }
                var searchStr = $("#selectExistingMember_chosen .chosen-search input").val();
                if(searchStr == ""){
                    // Don't search if empty string
                    return;
                }
                var url = wgServer + wgScriptPath + "/index.php?action=api.globalSearch/people/" + escape(searchStr).replace(/\//g, ' ');
                xhr = $.get(url, function(data){
                    if($("#selectExistingMember_chosen .chosen-search input").val() != searchStr){
                        // The value in the search box has changed, retry the ajax request
                        $("#selectExistingMember_chosen .chosen-search input").trigger("change");
                        return;
                    }
                    var results = data.results;
                    var people = new Array("<option></option>");
                    _.each(this.allPeople.sortBy('fullName'), function(p){
                        if(_.contains(results, parseInt(p.get('id')))){
                            var fullname = p.get('fullName');
                            if(p.get('email') != ""){
                                fullname += " (" + p.get('email').split('@')[0] + ")";
                            }
                            people.push("<option value='" + p.get('id') + "'>" + fullname + "</option>");
                        }
                    }.bind(this));
                    $("#selectExistingMember").html(people.join());
                    $("#selectExistingMember").trigger("chosen:updated");
                    $("#selectExistingMember_chosen .chosen-search input").val(searchStr);
                }.bind(this));
            }.bind(this));
        }.bind(this);
        $("#selectExistingMember_chosen .chosen-search input").keyup(changeFn)
                                                              .change(changeFn)
                                                              .on("paste", changeFn);
        this.addExistingMemberDialog.parent().css('overflow', 'visible');
    },
    
    events: {
        "click #addNewMember": "addNewMember",
        "click #addExistingMember": "addExistingMember"
    },
    
    render: function(){
        this.$el.html(this.template());
        main.bind('change:title', function(){
            $("#pageTitle").append("&nbsp;<span class='clicktooltip' title=''>&#9432;</span>");
            $("#pageTitle .clicktooltip").attr('title', this.$('#instructions').html());
            $("#pageTitle .clicktooltip").qtip({
	            position: {
	                adjust: {
		                x: -($("#pageTitle .clicktooltip").width()/25),
		                y: -($("#pageTitle .clicktooltip").height()/2)
	                }
	            },
	            style: {
	                classes: "instructions-qtip"
	            },
	            show: 'click',
                hide: 'click unfocus'
	        });
        }.bind(this));
        main.trigger('change:title');
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
	            "Add": function(e){
	                var addButton = e.currentTarget;
	                enableAddButton = function(){ // Used by child frame
	                    $(addButton).prop('disabled', false);
	                }
	                closeAddHQP = function(){ // Used by child frame
	                    this.addNewMemberDialog.dialog('close');
	                    clearSuccess();
	                    addSuccess("User created successfully");
	                    this.model.fetch();
	                }.bind(this);
	                $(addButton).prop('disabled', true);
	                if(document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=ignore_warnings]').length > 0){
	                    document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=ignore_warnings]').click();
	                }
                    else{ 
                        document.getElementById('addNewMemberFrame').contentWindow.$('form input[name=submit]').click();
                    }
	            }.bind(this),
	            "Cancel": function(){
	                this.addNewMemberDialog.dialog('close');
	            }.bind(this)
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
	            "Add": function(e){
	                var id = $("#selectExistingMember").val();
	                $.post(wgServer + wgScriptPath + "/index.php?action=api.people/managed", {id: id})
	                .done(function(){
	                    var person = this.allPeople.findWhere({'id': id});
	                    person.fetch();
	                    this.people.add(person);
	                }.bind(this))
	                .fail(function(){
	                    addError("There was a problem adding this person");
	                }.bind(this));
                    this.addExistingMemberDialog.dialog('close');
	            }.bind(this),
	            "Cancel": function(){
	                this.addExistingMemberDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    this.$el.append(this.addNewMemberDialog.parent());
	    this.$el.append(this.addExistingMemberDialog.parent());
        return this.$el;
    }

});

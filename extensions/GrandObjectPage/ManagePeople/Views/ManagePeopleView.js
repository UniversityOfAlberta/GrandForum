ManagePeopleView = Backbone.View.extend({

    table: null,
    subViews: new Array(),
    allPeople: null,
    people: null,
    addExistingMemberDialog: null,

    initialize: function(){
        this.allPeople = new People();
        if(_.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length > 0){
            this.allPeople.roles = ['all'];
        }
        else{
            this.allPeople.roles = ['allexceptstaff'];
        }
        this.allPeople.simple = true;
        this.allPeople.comparator = 'fullName';
        this.allPeople.fetch();
        this.template = _.template($('#manage_people_template').html());
        this.listenToOnce(this.model, "sync", function(){
            this.people = this.model;
            this.listenTo(this.people, "add", this.addRows);
            this.listenTo(this.people, "remove", this.addRows);
            this.render();
        }, this);
    },
    
    addRows: function(){
        var searchStr = "";
        var orderCol = 4;
        if(alumniEnabled){
            orderCol += 1;
        }
        if(_.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length > 0){
            orderCol += 3;
        }
        var order = [orderCol, 'asc'];
        
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
        this.createDataTable(order, searchStr);
    },
    
    createDataTable: function(order, searchStr){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
	                                                 'aLengthMenu': [[-1], ['All']],
	                                                 'oSearch': {"sSearch": searchStr},
	                                                 'order': order});
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
    },
    
    addExistingMember: function(){
        this.$("#selectExistingMember").empty();
        this.addExistingMemberDialog.dialog('open');
        this.allPeople.each(function(p){
            this.$("#selectExistingMember").append("<option value='" + p.get('id') + "'>" + p.get('fullName') + "</option>");
        });
        $("#selectExistingMember").chosen();
        this.addExistingMemberDialog.parent().css('overflow', 'visible');
    },
    
    events: {
        "click #addExistingMember": "addExistingMember"
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
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
	                    this.people.add(this.allPeople.findWhere({'id': id}));
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
        return this.$el;
    }

});

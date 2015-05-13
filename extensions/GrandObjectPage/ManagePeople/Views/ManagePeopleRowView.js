ManagePeopleRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    editRoles: null,
    rolesDialog: null,
    projectsDialog: null,
    relationsDialog: null,
    template: _.template($('#manage_people_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "change", this.render);
    },
    
    openRolesDialog: function(){
        this.rolesDialog.empty();
        this.rolesDialog.dialog('open');
        this.editRoles = new ManagePeopleEditRolesView({model: this.model.roles, 
                                                        person:this.model, 
                                                        el: this.rolesDialog});
    },
    
    events: {
        "click #editRoles": "openRolesDialog",
        "click #editProjects": "openProjectsDialog",
        "click #editRelations": "openRelationsDialog"
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        this.el.innerHTML = this.template(this.model.toJSON());
        if(this.parent.table != null){
            var data = new Array();
            this.$("td").each(function(i, val){
                data.push($(val).htmlClean().html());
            });
            if(this.row != null){
                this.row.data(data);
            }
        }
        if(classes.length > 0){
            this.$("td").each(function(i, val){
                $(val).addClass(classes[i]);
            });
        }
        this.rolesDialog = this.$("#rolesDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: 800,
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
	            "Save": $.proxy(function(e){
	                this.editRoles.saveAll();
                    this.rolesDialog.dialog('close');
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.rolesDialog.dialog('close');
	            }, this)
	        }
	    });
	    this.projectsDialog = this.$("#projectsDialog").dialog({
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
	            "Save": $.proxy(function(e){
                    this.projectsDialog.dialog('close');
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.projectsDialog.dialog('close');
	            }, this)
	        }
	    });
	    this.relationsDialog = this.$("#relationsDialog").dialog({
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
	            "Save": $.proxy(function(e){
                    this.relationsDialog.dialog('close');
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.relationsDialog.dialog('close');
	            }, this)
	        }
	    });
        return this.$el;
    }
    
});

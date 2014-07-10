ManageProductsView = Backbone.View.extend({

    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    products: null,
    projects: null,
    table: null,
    nProjects: 0,
    subViews: new Array(),

    initialize: function(){
        this.allProjects = new Projects();
        this.allProjects.fetch();
        this.template = _.template($('#manage_products_template').html());
        me.getProjects();
        this.model.bind('sync', function(){
            this.products = this.model.getAll();
            this.projects = me.projects.getCurrent();
            this.model.ready().then($.proxy(function(){
                this.allProjects.ready().then($.proxy(function(){
                    this.otherProjects = this.allProjects.getCurrent();
                    this.oldProjects = this.allProjects.getOld();
                    this.otherProjects.remove(this.projects.models);
                    this.oldProjects.remove(this.projects.models);
                    me.projects.ready().then($.proxy(function(){
                        this.render();
                    }, this));
                }, this));
            }, this));
        }, this);
    },
    
    addRows: function(){
        this.products.each($.proxy(function(p){
            p.dirty = false;
            var row = new ManageProductsViewRow({model: p, parent: this});
            this.subViews.push(row);
            this.$("#productRows").append(row.render());
        }, this));
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        _.each(this.subViews, function(view){
            if(target.is(":checked")){
                view.select(projectId);
            }
            else{
                view.unselect(projectId);
            }
        });
    },
    
    saveProducts: function(){
        this.products.each(function(product){
            if(product.dirty){
                product.save({}, {
                    success: function(){
                        product.dirty = false;
                        addSuccess("Saved " + product.get('id'));
                    },
                    error: function(){
                        addError("Failed Saving " + product.get('id'));
                    }
                });
            }
        });
    },
    
    events: {
        "change .selectAll": "toggleSelect",
        "click #saveProducts": "saveProducts"
    },
    
    render: function(){
        this.$el.empty();
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                _.each(this.subViews, function(view){
                    if(view.$("div.popupBox").is(":visible")){
                        // Need to defer the event so that unchecking a project is not in conflict
                        _.defer(function(){
                            view.model.trigger("change");
                        });
                    }
                });
            }
        }, this));
        this.$el.html(this.template());
        this.addRows();
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
                                                     'aoColumnDefs': [
                                                        {'bSortable': false, 'aTargets': _.range(0, this.projects.length + 1) }
                                                     ],
	                                                 'aaSorting': [ [this.projects.length + 1,'desc']],
	                                                 'aLengthMenu': [[-1], ['All']]});
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    var maxWidth = 50;
	    this.$('.angledTableText').each(function(i, e){
	        maxWidth = Math.max(maxWidth, $(e).width());
	    });
	    this.$('.angledTableHead').height(maxWidth +"px");
	    this.$('.angledTableHead').width('40px');
        return this.$el;
    }

});

ManageProductsViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(options){
        this.parent = options.parent;
        this.model.bind("change", this.render);
        this.template = _.template($('#manage_products_row_template').html());
    },
    
    setDirty: function(){
        this.model.dirty = true;
    },
    
    select: function(projectId){
        var projects = this.model.get('projects');
        if(_.where(projects, {id: projectId}).length == 0){
            projects.push({id: projectId});
        }
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change");
        }
        this.setDirty();
    },
    
    unselect: function(projectId){
        var project = _.findWhere(this.parent.projects.models, {id: projectId});
        var projects = this.model.get('projects');
        // Unselect all subprojects as well
        if(project != undefined){
            _.each(project.get('subprojects'), $.proxy(function(sub){
                projects.splice(_.indexOf(projects, _.findWhere(projects, {id: sub.id})), 1);
            }, this));
        }
        projects.splice(_.indexOf(projects, _.findWhere(projects, {id: projectId})), 1);
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change");
        }
        this.setDirty();
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        if(target.is(":checked")){
            this.select(projectId);
            if(target.attr('name') == "project"){
                //this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
            }
            else if(target.attr('name') == "subproject") {
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
        }
        else{
            this.unselect(projectId);
            if(target.attr('name') == "project"){
                // Do nothing
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
        }
    },
    
    showSubprojects: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
    },
    
    showOther: function(e){
        this.$("div.otherPopup").slideDown();
    },
    
    filterSearch: function(e){
        var target = $(e.currentTarget);
        var value = target.val();
        var block = target.parent();
        var options = $("div", block);
        options.each(function(i, el){
            var text = $(el).text();
            if(unaccentChars(text).indexOf(unaccentChars(value)) == -1){
                $(el).slideUp(150);
            }
            else{
                $(el).slideDown(150);
            }
        });
    },
    
    events: {
        "change input[type=checkbox]": "toggleSelect",
        "click div.showSubprojects": "showSubprojects",
        "click div.showOther": "showOther",
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }
    
});

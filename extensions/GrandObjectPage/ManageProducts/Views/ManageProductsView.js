ManageProductsView = Backbone.View.extend({

    allProjects: null,
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
                me.projects.ready().then(this.render);
            }, this));
        }, this);
    },
    
    addRows: function(){
        this.products.each($.proxy(function(p){
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
            
        });
    },
    
    events: {
        "change .selectAll": "toggleSelect",
        "click #saveProducts": "saveProducts"
    },
    
    render: function(){
        this.$el.empty();
        $(document).click($.proxy(function(e){
            var popup = $("div.subprojectPopup:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                _.each(this.subViews, function(view){
                    if(view.$("div.subprojectPopup").is(":visible")){
                        // Need to defer the event so that unchecking a project is not in conflict
                        _.defer(function(){
                            view.model.trigger("change:projects");
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
    
    select: function(projectId){
        var projects = this.model.get('projects');
        if(_.where(projects, {id: projectId}).length == 0){
            projects.push({id: projectId});
        }
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change:projects");
        }
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
            this.model.trigger("change:projects");
        }
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        if(target.is(":checked")){
            this.select(projectId);
            if(target.attr('name') == "project"){
                //this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
            }
            else{
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
        }
        else{
            this.unselect(projectId);
            if(target.attr('name') == "project"){
                $("div.subprojectPopup").slideUp();
            }
            else{
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
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }
    
});

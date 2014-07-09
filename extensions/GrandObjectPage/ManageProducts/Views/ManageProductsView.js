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
    
    events: {
        "change .selectAll": "toggleSelect"
    },
    
    render: function(){
        this.$el.empty();
        $(document).click(function(e){
            var popup = $("div.subprojectPopup:visible").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                $("div.subprojectPopup").not(':animated').hide();
            }
        });
        this.$el.html(this.template());
        this.addRows();
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
                                                     'aoColumnDefs': [
                                                        {'bSortable': false, 'aTargets': _.range(0, this.projects.length + 1) }
                                                     ],
	                                                 'aaSorting': [ [this.projects.length + 1,'desc']],
	                                                 'aLengthMenu': [[-1], ['All']]});
	    table = this.table;
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    var maxWidth = 30;
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
        this.model.bind("change:projects", this.render);
        this.template = _.template($('#manage_products_row_template').html());
    },
    
    select: function(projectId){
        var projects = this.model.get('projects');
        if(_.where(projects, {id: projectId}).length == 0){
            projects.push({id: projectId});
            this.model.set('projects', projects);
            this.model.trigger("change:projects");
        }
    },
    
    unselect: function(projectId){
        var project = _.findWhere(this.parent.projects.models, {id: projectId});
        var projects = this.model.get('projects');
        // Unselect all subprojects as well
        if(project != undefined){
            _.each(project.get('subprojects'), $.proxy(function(sub){
                projects = _.without(projects, _.findWhere(projects, {id: sub.id}));
            }, this));
        }
        projects = _.without(projects, _.findWhere(projects, {id: projectId}));
        this.model.set('projects', projects);
    },
    
    toggleSelect: function(e){
        _.defer($.proxy(function(){
            var target = $(e.currentTarget);
            var projectId = target.attr('data-project');
            if(target.is(":checked")){
                this.select(projectId);
                if(target.attr('name') == "project"){
                    $("div.subprojectPopup").hide();
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
        }, this));
    },
    
    showSubprojects: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
    },
    
    events: {
        "change input[type=checkbox]": "toggleSelect",
        "click div.showSubprojects": "showSubprojects"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }
    
});

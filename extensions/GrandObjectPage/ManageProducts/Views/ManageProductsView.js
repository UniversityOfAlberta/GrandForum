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
        this.listenTo(this.model, "sync", function(){
            this.products = this.model.getAll();
            me.projects.ready().then($.proxy(function(){
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
            }, this));
        }, this);
    },
    
    productChanged: function(){
        // Count how many products there are dirty
        var sum = 0;
        this.products.each(function(product){
            if(product.dirty){
                sum++;
            }
        });
        this.$("#saveN").html("(" + sum + ")");
        
        // Change the state of the 'selectAll' checkbox
        this.projects.each(function(project){
            var allFound = true;
            this.products.each(function(product){
                if(allFound && _.where(product.get('projects'), {id: project.get('id')}).length == 0){
                    allFound = false;
                }
            }, this);
            if(allFound){
                this.$("input.selectAll[data-project=" + project.get('id') + "]").prop('checked', true);
            }
            else{
                this.$("input.selectAll[data-project=" + project.get('id') + "]").prop('checked', false);
            }
        }, this);
    },
    
    addRows: function(){
        this.products.each($.proxy(function(p){
            this.listenTo(p, "dirty", this.productChanged);
            p.dirty = false;
            var row = new ManageProductsViewRow({model: p, parent: this});
            this.subViews.push(row);
            this.$("#productRows").append(row.render());
        }, this));
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        var checked = target.is(":checked");
        _.each(this.subViews, function(view){
            if(checked){
                view.select(projectId);
            }
            else{
                view.unselect(projectId);
            }
        });
    },
    
    saveProducts: function(){
        this.$("#saveProducts").prop('disabled', true);
        this.$(".throbber").show();
        var xhrs = new Array();
        this.products.each(function(product){
            if(product.dirty){
                // Save all Dirty Products
                xhrs.push(product.save({}, {
                    success: function(){
                        // Save was successful, mark it as 'clean'
                        product.dirty = false;
                    }
                }));
            }
        });
        $.when.apply(null, xhrs).done($.proxy(function(){
            // Success
            clearAllMessages();
            addSuccess("All products have been successfully saved");
            this.$("#saveProducts").prop('disabled', false);
            this.$(".throbber").hide();
            this.productChanged();
        }, this)).fail($.proxy(function(e){
            // Failure
            clearAllMessages();
            var list = new Array();
            list.push("There was a problem saving the following products:<ul>");
            this.products.each(function(product){
                if(product.dirty){
                    list.push("<li>" + product.get('title') + "</li>");
                }
            });
            list.push("</ul>");
            addError(list.join(''));
            this.$("#saveProducts").prop('disabled', false);
            this.$(".throbber").hide();
            this.productChanged();
        }, this));
    },
    
    events: {
        "click .selectAll": "toggleSelect",
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
	    table = this.table;
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    var maxWidth = 50;
	    this.$('.angledTableText').each(function(i, e){
	        maxWidth = Math.max(maxWidth, $(e).width());
	    });
	    this.$('.angledTableHead').height(maxWidth +"px");
	    this.$('.angledTableHead').width('40px');
	    this.productChanged();
        return this.$el;
    }

});

ManageProductsViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "change", this.render);
        this.template = _.template($('#manage_products_row_template').html());
    },
    
    setDirty: function(){
        this.model.dirty = true;
        this.model.trigger("dirty");
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
        var project = _.findWhere(this.parent.projects.models
                                      .concat(this.parent.otherProjects.models)
                                      .concat(this.parent.oldProjects.models), {id: projectId});
        var projects = this.model.get('projects');

        // Unselect all subprojects as well
        if(project != undefined){
            _.each(project.get('subprojects'), $.proxy(function(sub){
                var index = _.indexOf(projects, _.findWhere(projects, {id: sub.id}));
                if(index != -1){
                    projects.splice(index, 1);
                    this.$("input[data-project=" + sub.id + "]").prop('checked', false);
                }
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
            // 'Check' Project
            this.select(projectId);
            if(target.attr('name') == "project"){
                //this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideDown();
            }
        }
        else{
            // 'Uncheck' Project
            this.unselect(projectId);
            if(target.attr('name') == "project"){
                // Do nothing
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideUp();
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
        var options = $("div", block).not(".subproject");
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

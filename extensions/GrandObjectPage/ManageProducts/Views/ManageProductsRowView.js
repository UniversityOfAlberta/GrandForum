ManageProductsViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    template: _.template($('#manage_products_row_template').html()),
    otherPopupTemplate: _.template($('#other_popup_template').html()),
    projectsPopupTemplate: _.template($('#projects_popup_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.allProjects = this.parent.allProjects;
        this.otherProjects = this.parent.otherProjects;
        this.oldProjects = this.parent.oldProjects;
        this.projects = this.parent.projects;
        this.listenTo(this.model, "change", this.render);
        this.listenTo(this.model, "change:projects", this.render);
        this.listenTo(this.model, "change:access_id", this.setDirty, true);
    },
    
    setDirty: function(trigger){
        this.model.dirty = true;
        if(trigger){
            this.model.trigger("dirty");
        }
    },
    
    select: function(projectId){
        this.projectSelectorView.select(projectId);
        this.setDirty(false);
        this.model.trigger("change:projects");
    },
    
    unselect: function(projectId){
        this.projectSelectorView.unselect(projectId);
        this.setDirty(false);
        this.model.trigger("change:projects");
    },
    
    toggleSelect: function(e){
        this.projectSelectorView.toggleSelect(e);
        this.setDirty(true);
    },
    
    editProduct: function(){
        var view = new ProductEditView({el: this.parent.editDialog, model: this.model, isDialog: true});
        this.parent.editDialog.view = view;
        this.parent.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Edit " + productsTerm
        });
        this.parent.editDialog.dialog('open');
    },
    
    deleteProduct: function(){
        this.parent.deleteDialog.model = this.model;
        this.parent.deleteDialog.dialog('open');
    },
    
    events: {
        "change input[type=checkbox]": "toggleSelect",
        "click .edit-icon": "editProduct",
        "click .delete-icon": "deleteProduct"
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        var isMine = {isMine: false};
        if(_.contains(_.pluck(this.model.get('authors'), 'id'), me.get('id')) ||
           _.contains(_.pluck(this.model.get('authors'), 'name'), me.get('name')) ||
           _.contains(_.pluck(this.model.get('authors'), 'fullname'), me.get('fullName')) ||
           _.intersection(_.pluck(this.model.get('authors'), 'id'), students).length > 0 ||
           _.intersection(_.pluck(this.model.get('authors'), 'name'), studentNames).length > 0 ||
           _.intersection(_.pluck(this.model.get('authors'), 'fullname'), studentFullNames).length > 0){
            isMine.isMine = true;
        }
        this.el.innerHTML = this.template(_.extend(this.model.toJSON(), isMine));
        if(this.parent.table != null){
            // Need this so that the search functionality is updated
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
        return this.$el;
    }
    
});

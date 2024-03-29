ManageSOPViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    template: _.template($('#manage_sop_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.allProjects = this.parent.allProjects;
        this.otherProjects = this.parent.otherProjects;
        this.oldProjects = this.parent.oldProjects;
        this.projects = this.parent.projects;
        this.listenTo(this.model, "change", this.render);
        this.listenTo(this.model, "change:projects", this.render);
    },
    
    editProduct: function(){
        var view = new ProductEditView({el: this.parent.editDialog, model: this.model, isDialog: true});
        this.parent.editDialog.view = view;
        this.parent.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Edit SOP"
        });
        this.parent.editDialog.dialog('open');
    },
    
    deleteProduct: function(){
        this.parent.deleteDialog.model = this.model;
        this.parent.deleteDialog.dialog('open');
    },
    
    duplicateProduct: function(){
        if(!this.duplicating){
            // Only duplicate if there isn't already a pending one happening
            this.$(".copy-icon").css('background', 'none');
            this.$(".copy-icon .throbber").show();
            this.duplicating = true;
            var product = new Product(this.model.toJSON());
            product.set('id', null);
            product.save(null, {
                success: function(){
                    clearSuccess();
                    clearError();
                    addSuccess('The ' + product.get('category') + ' <i>' + product.get('title') + '</i> was duplicated');
                    this.parent.products.add(product);
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this),
                error: function(){
                    clearSuccess();
                    clearError();
                    addError('There was a problem duplicating the ' + product.get('category') + ' <i>' + product.get('title') + '</i>');
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this)
            });
        }
    },
    
    events: {
        "click .edit-icon": "editProduct",
        "click .copy-icon": "duplicateProduct",
        "click .delete-icon": "deleteProduct",
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        var extra = {
            isMine: (_.contains(_.pluck(this.model.get('authors'), 'id'), me.get('id')) ||
                     _.contains(_.pluck(this.model.get('authors'), 'name'), me.get('name')) ||
                     _.contains(_.pluck(this.model.get('authors'), 'fullname'), me.get('fullName'))), 
            hasProjects: !(projectsEnabled && this.model.get('projects').length == 0)
        };
        this.el.innerHTML = this.template(_.extend(this.model.toJSON(), extra));
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
        renderProductLinks(this.$el);
        return this.$el;
    }
    
});

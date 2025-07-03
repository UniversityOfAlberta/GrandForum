LIMSOpportunityEditViewPmm = Backbone.View.extend({

    subViews: [],
    saving: false,
    project: null,

    initialize: function(options){
        this.saving = false;
        this.project = options.project;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "add", this.renderTasks);
        this.listenTo(this.model.tasks, "change:toDelete", this.removeTasks);
        this.listenTo(this.model, "change:category", this.updateTasks);
        this.selectTemplate();
    },
    
    selectTemplate: function(){
        if(!this.model.get('isAllowedToEdit')){
            // Not allowed to edit, use read-only version
            this.template = _.template($('#lims_opportunity_template').html());
        }
        else{
            // Use Edit version
            this.template = _.template($('#lims_opportunity_edit_template').html());
        }
    },
    
    addTask: function(){
        this.model.tasks.add(new LIMSTaskPmm({opportunity: this.model.get('id')}));
    },
    
    deleteOpportunity: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },
    
    addDocument: function(){
        var files = this.model.get('files');
        files.push({id: null, data: ''});
        this.model.set('files', files);
        this.$("#files").append("<tr><td>" + HTML.File(this, 'files.' + (files.length - 1), {}) + "</td><td></td></tr>");
    },
    
    addProduct: function(){
        var products = this.model.get('products');
        products.push({type: "", text: ""});
        this.model.set('products', products);
        this.$("#products").append("<tr><td>" + HTML.Select(this, 'products.' + (products.length - 1) + ".type", {options: LIMSProductTypes}) + "</td>" + 
                                   "    <td>" + HTML.TextBox(this, 'products.' + (products.length - 1) + ".text", {style: "width: 100%; box-sizing: border-box;"}) + "</td>" + 
                                   "</tr>");
    },
    
    deleteProduct: function(e){
        var products = this.model.get('products');
        var id = $(e.target).attr("data-id");
        products[id].delete = 1;
        this.model.set('products', products);
        $(e.target).closest("tr").remove();
    },
    
    events: {
        "click #deleteOpportunity": "deleteOpportunity",
        "click #addTask": "addTask",
        "click #addDocument": "addDocument",
        "click #addProduct": "addProduct",
        "click #products .delete-icon": "deleteProduct"
    },
    
    removeTasks: function(){
        _.each(this.subViews, function(view){
            if(view.model.toDelete){
                // To be deleted, remove from dom
                view.remove();
            }
        }.bind(this));
    },
    
    updateTasks: function(){
        // Do deletions first
        this.removeTasks();
        // Now render the rest
        _.each(this.subViews, function(view){
            if(!view.model.toDelete){
                // Render
                view.render();
            }
        }.bind(this));
    },
    
    renderTasks: function(model){
        var view = new LIMSTaskEditViewPmm({model: model, project: this.project});
        this.$("#tasks > tbody").append(view.render());
        this.subViews.push(view);
    },
    
    render: function(){
        if(!this.saving){
            this.$el.html(this.template(this.model.toJSON()));
            this.$el.addClass("opportunity");
            this.$("#taskContainer").show();
            _.defer(function(){
                this.$('select[name=owner_id]').chosen();
            }.bind(this));
        }
        return this.$el;
    }

});

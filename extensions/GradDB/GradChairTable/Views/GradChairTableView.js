GradChairTableView = Backbone.View.extend({

    template: _.template($("#grad_chair_table_template").html()),

    initialize: function(){
        this.model.bind('sync', this.render, this);
        this.model.fetch();
        this.render();
    },
    
    renderRows: function(){
        this.model.each(function(model){
            var view = new GradChairRowView({model: model});
            this.$("#chair_table tbody").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderRows();
        this.$('#chair_table').DataTable({
            aLengthMenu: [
                [100, -1],
                [100, "All"]
            ],
            iDisplayLength: -1
        });
        return this.$el;
    }

});

GradChairRowView = Backbone.View.extend({

    tagName: "tr",
    
    edit: false,

    template: _.template($("#grad_chair_row_template").html()),

    initialize: function(){
        this.model.bind('sync', this.render, this);
    },
    
    events: {
        "click #edit": "clickEdit",
        "click #save": "clickSave"
    },
    
    clickEdit: function(){
        this.edit = true;
        this.render();
    },
    
    clickSave: function(){
        this.$("button").prop("disabled", true);
        this.$(".throbber").show();
        this.model.save(null, {
            success: function(){
                this.edit = false;
                this.render();
            }.bind(this),
            error: function(o, e){
                clearAllMessages("#dialogMessages");
                if(e.responseText != ""){
                    addError(e.responseText);
                }
                else{
                    addError("There was a problem saving the entry");
                }
                this.render();
            }.bind(this)
        });
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }
});

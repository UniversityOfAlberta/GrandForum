CSVImportView = Backbone.View.extend({

    template: _.template($("#tab_template").html()),

    initialize: function(options){
    },
    
    changeSelection: function(){
    },
    
    upload: function(){
    },
    
    events: {
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});

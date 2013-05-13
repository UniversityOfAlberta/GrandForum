MainView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change:title', this.changeTitle);
        this.template = _.template($('#main_template').html());
    },
    
    changeTitle: function(){
        $('#pageTitle').html(this.get('title'));
        document.title = this.get('title');
    },
    
    render: function(){ 
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        return this.el;
    }

});

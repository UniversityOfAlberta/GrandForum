MyThreadsView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($('#my_threads_template').html());
        this.listenTo(this.model, "sync", function(){
            this.render();
        }, this);
    },
    
    render: function(){
        this.$el.html(this.template());
        this.$("#boards").dataTable({
            bFilter: false,
            bPaginate: false,
            bSort: false
        });
        this.$("#boards_info").remove();
    }

});

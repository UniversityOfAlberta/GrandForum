MainView = Backbone.View.extend({

    initialize: function(){
        this.listenTo(this.model, 'change:title', this.changeTitle);
        this.template = _.template($('#main_template').html());
    },
    
    changeTitle: function(){
        $('#pageTitle').html(this.model.get('title'));
        document.title = $("<div>" + this.model.get('title') + "</div>").text();
        this.$('#pageTitle .tooltip').qtip({
            position: {
                adjust: {
	                x: -(this.$('#pageTitle .tooltip').width()/25),
	                y: -(this.$('#pageTitle .tooltip').height()/2)
                }
            },
            show: {
                delay: 500
            },
            hide: {
                fixed: true,
                delay: 300
            }
        });
        this.$('#pageTitle .clicktooltip').qtip({
            position: {
                adjust: {
	                x: -(this.$('#pageTitle .clicktooltip').width()/25),
	                y: -(this.$('#pageTitle .clicktooltip').height()/2)
                }
            },
            show: 'click',
            hide: 'click unfocus'
        });
    },
    
    render: function(){ 
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        return this.el;
    }

});

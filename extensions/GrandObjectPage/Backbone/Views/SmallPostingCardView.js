SmallPostingCardView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($("#small_posting_card_template").html());
        this.$el.css('display', 'none');
        var that = this;
        this.model.fetch({success: function(){
            that.$el.css('display', 'block');
        }});
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});

SmallWikiCardView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($("#small_wiki_card_template").html());
        this.model.fetch();
    },

    render: function(){
        this.$el.css('width', '0');
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});

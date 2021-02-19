SmallProductCitationCardView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#small_product_citation_card_template").html());
        this.$el.css('display', 'none');
        $.when(this.model.getCitation()).then(function(){
            this.render();
            this.$el.css('display', 'block');
        }.bind(this));
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("a").not(".card_link").each(function(i, el){
            $(el).replaceWith("<span>" + el.innerHTML + "</span>");
        });
        if(this.$(".authors").length > 0){
            this.$(".authors")[0].style['display'] = "-webkit-box";
            this.$(".authors")[0].style['-webkit-line-clamp'] = 2;
            this.$(".authors")[0].style['-webkit-box-orient'] = "vertical";
            this.$(".authors")[0].style['overflow'] = "hidden";
        }
        return this.$el;
    }

});

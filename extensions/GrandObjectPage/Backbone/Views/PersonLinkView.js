PersonLinkView = Backbone.View.extend({

    tagName: "span",
    
    timeout: null,
    cardRendered: false,

    initialize: function(){
        this.model.bind('change', this.render, this);
    },
    
    showCard: function(){
        clearTimeout(this.timeout);
        if(!this.cardRendered){
            var person = new Person({id: this.model.id});
            var card = new SmallPersonCardView({model: person});
            this.$(".card").html(card.render());
            this.cardRendered = true;
        }
        this.timeout = setTimeout(function(){
            $(".card").hide();
            this.$(".card").show();
            this.$(".card").css("position", "absolute")
                           .css("top", -this.$(".card").height() -1)
                           .css("left", 0);
            this.$(".card").offset({left: Math.max(30, this.$(".card").offset().left)});
            this.$(".card").offset({left: Math.min($(window).width() - this.$(".card").width() - 32, this.$(".card").offset().left)});
        }.bind(this), 300);
    },
    
    hideCard: function(){
        clearTimeout(this.timeout);
        this.timeout = setTimeout(function(){
            this.$(".card").hide();
        }.bind(this), 200);
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css("position", "relative");
        if(this.model.get('url') != ""){
            this.$el.append("<a id='personLink'>" + this.model.get('text') + "</a>");
            this.$("a#personLink").attr("href", this.model.get('url'))
                       .attr("target", this.model.get('target'))
                       .attr("title", this.model.get('title'));
            this.$el.append("<div class='card' style='display:none;position:absolute;border:1px solid #CCCCCC;height:51px;width:317px;background:#FFFFFF;' />");
            this.$(".card").mouseover(function(){
                clearTimeout(this.timeout);
            }.bind(this));
            this.$(".card").mouseout(this.hideCard.bind(this));
        }
        else{
            this.$el.html(this.model.get('text'));
        }
        this.$("a").mouseover(this.showCard.bind(this));
        this.$("a").mouseout(this.hideCard.bind(this));
        $(window).on("unload", function(){
            // Make sure that cards hide when navigating away
            this.$(".card").hide();
        }.bind(this));
        return this.$el;
    }

});

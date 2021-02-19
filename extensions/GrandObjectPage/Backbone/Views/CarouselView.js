CarouselView = Backbone.View.extend({
    
    index: 0,
    card: null,
    progress: 0,
    progressPaused: false,
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", function(){
            this.model.set(this.model.filter(function(person){ return (!_.isEmpty(person.get('publicProfile')) || !_.isEmpty(person.get('privateProfile'))); }));
            this.model.set(this.model.shuffle());
            this.render();
            setInterval(this.renderProgress.bind(this), 33);
        }.bind(this));
        this.template = _.template($('#carousel_template').html());
    },
    
    events: {
        "click .carouselPrev": "prev",
        "click .carouselNext": "next",
        "mouseover .carouselContainer": function(){ this.progress = 0; this.progressPaused = true; },
        "mouseout  .carouselContainer": function(){ this.progress = 0; this.progressPaused = false; }
    },
    
    prev: function(){
        this.index--;
        if(this.index < 0){
            this.index = this.model.length - 1;
        }
        this.$(".carouselMain").stop(true, true);
        this.$(".carouselMain").show();
        this.$(".carouselMain").hide("slide", {
            direction: "right",
            complete: function(){
                this.renderItem();
                this.$(".carouselMain").show("slide", {
                    direction: "left",
                    complete: function(){
                        this.$(".carouselMain").css('left', 0);
                    }.bind(this)
                }, 350);
            }.bind(this)
        }, 350);
        this.progress = 0;
    },
    
    next: function(){
        this.index++;
        if(this.index >= this.model.length){
            this.index = 0;
        }
        this.$(".carouselMain").stop(true, true);
        this.$(".carouselMain").show();
        this.$(".carouselMain").hide("slide", {
            direction: "left",
            complete: function(){
                this.renderItem();
                this.$(".carouselMain").show("slide", {
                    direction: "right",
                    complete: function(){
                        this.$(".carouselMain").css('left', 0);
                    }.bind(this)
                }, 350);
            }.bind(this)
        }, 350);
        this.progress = 0;
    },
    
    renderProgress: function(){
        if(!this.progressPaused){
            this.progress += 0.2;
        }
        this.$(".carouselProgressBar").css("width", this.progress + "%");
        if(this.progress >= 100){
            this.progress = 0;
            this.next();
        }
    },
    
    renderItem: function(){
        if(this.card != null){
            this.card.undelegateEvents();
            this.card.stopListening();
        }
        var model = this.model.at(this.index);
        this.card = new LargePersonCardView({el: this.$(".carouselContent"), model: model});
        this.card.render();
        this.card.$(".card_photo").css('height', 140);
        this.$(".carouselExtra").empty();
        if(model.get('keywords').length > 0){
            this.$(".carouselExtra").append("<b>Keywords:</b> " + model.get('keywords').join(', '));
        }
        if(model.get('privateProfile') != null && model.get('privateProfile').trim() != ""){
            this.$(".carouselExtra").append(model.get('privateProfile'));
        }
        else {
            this.$(".carouselExtra").append(model.get('publicProfile'));
        }
        this.card.$(".card_photo img").wrap("<a class='carouselUrl' href='" + model.get('url') + "'>");
        this.card.$("h1").wrap("<a class='carouselUrl' href='" + model.get('url') + "'>");
        this.$(".carouselContent").css("min-height", 185);
        this.$(".carouselExtra").css('max-height', 200)
                                .css('height', 200)
                                .css('overflow-y', 'auto');
    },
    
    render: function(){ 
        this.$el.empty();
        this.$el.html(this.template());
        this.renderItem();
        return this.el;
    }

});

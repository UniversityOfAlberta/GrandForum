ProjectCarouselView = CarouselView.extend({
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", function(){
            this.model.set(this.model.filter(function(project){ return (project.get('status') == 'Active'); }));
            //this.model.set(this.model.filter(function(project){ return (project.get('name') == 'Project 2'); }));
            this.model.set(this.model.shuffle());
            this.render();
            setInterval(this.renderProgress.bind(this), 33);
        }.bind(this));
        this.template = _.template($('#carousel_template').html());
    },
    
    renderItem: function(){
        if(this.card != null){
            this.card.undelegateEvents();
            this.card.stopListening();
        }
        var model = this.model.at(this.index);
        this.card = new LargeProjectCardView({el: this.$(".carouselContent"), model: model});
        this.card.render();
        this.$(".carouselExtra").html(model.get('description'));
        
        this.card.$("h1").wrap("<a class='carouselUrl' href='" + model.get('url') + "'>");
        this.$(".carouselContent").css("min-height", 185);
        this.$(".carouselExtra").css('max-height', 200)
                                .css('height', 200)
                                .css('overflow-y', 'auto');
    }

});

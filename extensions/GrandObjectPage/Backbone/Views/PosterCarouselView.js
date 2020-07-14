PosterCarouselView = CarouselView.extend({
    
    initialize: function(){
        this.template = _.template($('#carousel_template').html());
        this.model.set(this.model.shuffle());
        this.render();
    },
    
    renderItem: function(){
        var model = this.model.at(this.index);
        this.$(".carouselContent").html("<h1 style='display:inline-block;padding:0;vertical-align:top;border:none;width:100%;text-align:center;'><a href='" + model.get('url') + "'>" + model.get('title') + "</a></h1>");
        this.$(".carouselExtra").html('<iframe src="' + wgServer + wgScriptPath + '/scripts/ViewerJS/#' + wgServer + wgScriptPath + '/index.php?action=api.productFile/' + model.get('id') + '/file/' + model.get('data')['file']['filename'] + '" style="width:100%; height:450px;" frameborder="0" allowfullscreen="true"></iframe>');
        this.$(".carouselProgressBarContainer").hide();
        this.$(".carouselExtra").css("padding-bottom", 0)
                                .css("height", "450");
    },

});

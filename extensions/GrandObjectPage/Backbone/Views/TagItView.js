TagItView = Backbone.View.extend({

    tagName: 'div',

    initialize: function(){
        this.model.bind('change', this.render);
        this.template = _.template($('#tagit_template').html());
        var that = this;
        this.model.get('options').afterTagRemoved = function(event, ui){that.renderSuggestions();};
        this.model.get('options').afterTagAdded = function(event, ui){that.renderSuggestions();};
    },
    
    renderSuggestions: function(){
        var that = this;
        this.$(".suggestionsDiv").css('display', 'none');
        this.$(".tagit-suggestions").empty();
        if(this.model.get('suggestions').length > 0){
            var suggestions = Array();
            var currentTags = this.$("input.tagit").tagit("assignedTags");
            _.each(this.model.get('suggestions'), function(suggestion){
                if(currentTags.indexOf(suggestion) == -1){
                    this.$(".tagit-suggestions").append("<li class='tagit-suggestion ui-corner-all'>" + suggestion + " +</li>");
                    this.$(".tagit-suggestions li").last().attr('name', suggestion);
                }
            }, this);
            this.$(".tagit-suggestions li").click(function(event, ui){
                that.$("input.tagit").tagit("createTag", $(this).attr('name'));
            });
        }
        if(this.$(".tagit-suggestions li").length > 0){
            this.$(".suggestionsDiv").css('display', 'block');
        }
    },
    
    render: function(){
        this.$el.empty();
        var that = this;
        this.$el.html(this.template(this.model.toJSON()));
        this.$el.css('display', 'none');
        this.$("input.tagit").val(this.model.get('values').join(', '));
        this.$("input.tagit").tagit(this.model.get('options'));
        this.renderSuggestions();
        this.$el.slideDown(250);
        return this.$el;
    }

});

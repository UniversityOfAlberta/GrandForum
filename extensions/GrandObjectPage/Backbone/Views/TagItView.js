TagItView = Backbone.View.extend({

    tagName: 'div',

    initialize: function(){
        this.template = _.template($('#tagit_template').html());
        var that = this;
        this.model.get('options').afterTagRemoved = function(event, ui){that.renderSuggestions();};
        this.model.get('options').afterTagAdded = function(event, ui){that.renderSuggestions();};
        this.model.get('options').beforeTagAdded = function(event, ui){return that.addTag(event, ui);};
        if(this.model.get('options').tabIndex == undefined){this.model.get('options').tabIndex = 10; };
        if(this.model.get('options').caseSensitive == undefined){this.model.get('options').caseSensitive = false; };
        if(this.model.get('options').allowSpaces == undefined){this.model.get('options').allowSpaces = true; };
        if(this.model.get('options').removeConfirmation == undefined){this.model.get('options').removeConfirmation = true; };
        if(this.model.get('options').singleField == undefined){this.model.get('options').singleField = true; };
    },
    
    tagit: function(option, args){
        if(option != undefined){
            return this.$("ul.tagit").tagit(option, args);
        }
        return this.$("ul.tagit");
    },
    
    addTag: function(event, ui){
        if(this.model.get('capitalize')){
            ui.tagLabel = ui.tagLabel.toUpperCase();
        }
        if(this.model.get('strictValues')){
            if(this.model.get('options').availableTags.indexOf(ui.tagLabel) == -1 && 
               this.model.get('suggestions').indexOf(ui.tagLabel) == -1){
                this.$(".error").css('display', 'block');
                this.$(".error").html("<b>" + ui.tagLabel + "</b> could not be added.");
                return false;
            }
        }
        this.$(".error").css('display', 'none');
        if(this.model.get('capitalize')){
            this.$("ul.tagit").tagit("tagInput").val(this.$("ul.tagit").tagit("tagInput").val().toUpperCase());
        }
        return true;
    },
    
    renderSuggestions: function(){
        this.$("input[name=tags]").attr('name', this.model.get('name'));
        var that = this;
        this.$(".suggestionsDiv").css('display', 'none');
        this.$(".tagit-suggestions").empty();
        if(this.model.get('suggestions').length > 0){
            var suggestions = Array();
            var currentTags = this.$("ul.tagit").tagit("assignedTags");
            if(_.isArray(currentTags) && this.model.get('capitalize')){
                for(i in currentTags){
                    currentTags[i] = currentTags[i].toUpperCase();
                }
            }
            _.each(this.model.get('suggestions'), function(suggestion){
                if(_.isArray(currentTags) && currentTags.indexOf(suggestion) == -1){
                    this.$(".tagit-suggestions").append("<li class='tagit-suggestion ui-corner-all'>" + suggestion + " +</li>");
                    this.$(".tagit-suggestions li").last().attr('name', suggestion);
                }
            }, this);
            this.$(".tagit-suggestions li").click(function(event, ui){
                that.$("ul.tagit").tagit("createTag", $(this).attr('name'));
            });
        }
        if(this.$(".tagit-suggestions li").length > 0){
            this.$(".suggestionsDiv").css('display', 'block');
        }
    },
    
    render: function(){
        var that = this;
        this.$el.html(this.template(this.model.toJSON()));
        _.each(this.model.get('values'), $.proxy(function(val){
            this.$("ul.tagit").append("<li>" + val + "</li>");
        }, this));
        this.$("ul.tagit").tagit(this.model.get('options'));
        if(this.model.get('capitalize')){
            this.$("ul.tagit").css('text-transform', 'uppercase');
            this.$("li.tagit-new input").css('text-transform', 'uppercase');
        }
        this.renderSuggestions();
        return this.$el;
    }

});

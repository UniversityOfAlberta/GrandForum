BSIPostingEditView = PostingEditView.extend({

    template: _.template($('#bsiposting_edit_template').html()),
    
    characterCount: function(){
        return true;
    },
    
    renderTagsWidget: function(){
        var html = HTML.TagIt(this, 'discipline', {
            strictValues: false, 
            values: this.model.get('discipline').split(', '),
            options: {
                removeConfirmation: false,
                availableTags: ['asdf', 'fdsa']
            }
        });
        this.$("#discipline").html(html);
    },
    
    postRender: function(){
        this.renderTagsWidget();
    }

});

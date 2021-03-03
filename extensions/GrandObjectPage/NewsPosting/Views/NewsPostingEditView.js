NewsPostingEditView = PostingEditView.extend({

    template: _.template($('#newsposting_edit_template').html()),
    
    postRender: function(){
        this.renderTinyMCE();
    }

});

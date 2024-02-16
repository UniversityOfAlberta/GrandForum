EventPostingEditView = PostingEditView.extend({

    template: _.template($('#eventposting_edit_template').html()),
    
    postRender: function(){
        this.renderTinyMCE();
        this.$("[name=extra_event_type]").combobox();
    }

});

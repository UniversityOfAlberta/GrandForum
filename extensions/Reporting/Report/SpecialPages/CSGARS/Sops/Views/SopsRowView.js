SopsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    template: _.template($('#sops_row_template').html()),
    additionalNotesDialog: null,
    renderedOnce: false,
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
        'click #additionalNotes': 'addNotes',
        'change input[name=hidden]': 'changeHidden',
        'change input[name=favorited]': 'changeFavorited'
    },
    
    changeHidden: function(){
        this.model.set('hidden', this.$("input[name=hidden]").is(":checked"));
        this.$("#hiddenThrobber").show();
        $.post(wgServer + wgScriptPath + '/index.php?action=api.sophidden/' + this.model.get('user_id') + '/' + this.model.get('year'), {hidden: this.model.get('hidden')}, function(response){
            this.$("#hiddenThrobber").hide();
        }.bind(this));
    },
    
    changeFavorited: function(){
        this.model.set('favorited', this.$("input[name=favorited]").is(":checked"));
        this.$("#favoritedThrobber").show();
        $.post(wgServer + wgScriptPath + '/index.php?action=api.sopfavorited/' + this.model.get('user_id') + '/' + this.model.get('year'), {favorited: this.model.get('favorited')}, function(response){
            this.$("#favoritedThrobber").hide();
        }.bind(this));
    },

    addNotes: function() {
        if (this.additionalNotesDialog == null) {
            var additionalNotesDialogId = 'additionalNotes_' + this.model.attributes['id'];
            var model = this.model;
            var view = this;
            var previousAdditional;
            var notesView = new NotesView({model: this.model, el: this.$("#" + additionalNotesDialogId)});
            this.additionalNotesDialog = notesView.render();
            this.additionalNotesDialog.dialog({
                autoOpen: false,
                resizable: false,
                //height: 400,
                open: function(event, ui) {
                    previousAdditional = _.clone(model.get('additional'));
                },
                closeOnEscape: true,
                buttons: {
                    "Save": function() {
                        model.save();
                        $(this).dialog("close");
                        view.$('#notes .throbber').show();
                    },
                    "Cancel": function() {
                        $(this).dialog("close");
                    }
                },
                close: function() {
                    model.set('additional', previousAdditional);
                    $('textarea', $(this)).val(previousAdditional.notes[me.get('lastname').replace(/[^\wA-zÀ-ÿ]/gi, '')]);
                },
                show: { effect: "drop", direction: "down", duration: 250 }
            });
            this.additionalNotesDialog.parent().appendTo(this.$("#notes"));
        }
        this.additionalNotesDialog.parent().css({position:"fixed"}).end().dialog('open');
    },

    render: function(){
        var mod = _.extend(this.model.toJSON());
        this.el.innerHTML = this.template(mod);
        for(m=0;m<mod.annotations.length;m++){
            if(mod.annotations[m].tags != null){
                for(n=0;n<mod.annotations[m].tags.length;n++){
                    var comment_column = "#span"+mod.sop_id;
                    if(m == mod.annotations.length-1){
                        $(comment_column).append(mod.annotations[m].tags[n]);
                        break;
                    }
                    $(comment_column).append(mod.annotations[m].tags[n]+", ");
                }
            }
        }
        this.additionalNotesDialog = null;
        if(this.renderedOnce){
            this.parent.renderRoles();
        }
        this.renderedOnce = true;
        return this.$el;
    }
});

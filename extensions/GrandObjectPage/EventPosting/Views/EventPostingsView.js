EventPostingsView = PostingsView.extend({

    template: _.template($('#eventpostings_template').html()),
    
    duplicateEvent: function(e){
        if(!this.duplicating){
            // Only duplicate if there isn't already a pending one happening
            this.$(".copy-icon").css('background', 'none');
            this.$(".copy-icon .throbber").show();
            this.duplicating = true;
            var id = $(e.currentTarget).closest("tr").attr("data-id");
            var model = this.model.get(id);
            var event = new EventPosting(model.toJSON());
            event.set('id', null);
            event.set('created', "");
            event.set('modified', "");
            event.save(null, {
                success: function(){
                    clearSuccess();
                    clearError();
                    addSuccess('Event Posting copied');
                    this.model.fetch();
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this),
                error: function(){
                    clearSuccess();
                    clearError();
                    addError('There was a problem copying the Event Posting');
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this)
            });
        }
    },
    
    events: _.extend(PostingsView.prototype.events, {
        "click .copy-icon": "duplicateEvent"
    })

});

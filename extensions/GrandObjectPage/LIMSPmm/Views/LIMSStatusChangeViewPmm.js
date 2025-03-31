LIMSStatusChangeViewPmm = Backbone.View.extend({
    tagName: 'div',
    
    events: {
        'click #updateStatusButton': 'updateStatus', // Button to update status
        'click #cancelButton': 'closeDialog' // Button to cancel
    },

    initialize: function(options) {
        
        this.isDialog = options.isDialog || false;
        this.template = _.template($('#lims_status_change_template').html());
        this.model.startTracking();
        this.render();
        
        
    },

    render: function() {

          
        this.$el.html(this.template(this.model.toJSON())); 
        return this.$el;
    },

    updateStatus: function() {
        this.closeDialog();
    },

    closeDialog: function() {
        this.$el.dialog('close');
    }
});
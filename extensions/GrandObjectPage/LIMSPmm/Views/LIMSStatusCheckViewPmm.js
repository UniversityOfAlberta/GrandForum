LIMSStatusCheckViewPmm = Backbone.View.extend({
    tagName: 'div',
    // project: null,
    
    events: {
        'click #cancelButton': 'closeDialog' // Button to cancel
    },

    initialize: function(options) {
        // this.project = options.project;
        this.isDialog = options.isDialog || false;
        this.selectTemplate();
        
        this.model.startTracking();
        this.render();
        
        
    },

    selectTemplate: function(){

        this.template = _.template($(
            '#lims_status_check_template').html());
    },
    


    render: function() {

          
        this.$el.html(this.template(this.model.toJSON())); 
        return this.$el;
    },



    closeDialog: function() {
        this.$el.dialog('close');
    }
});
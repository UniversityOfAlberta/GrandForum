LIMSStatusChangeViewPmm = Backbone.View.extend({
    tagName: 'div',
    project: null,
    
    events: {
        'click #updateStatusButton': 'updateStatus', // Button to update status
        'click #cancelButton': 'closeDialog' // Button to cancel
    },

    initialize: function(options) {
        this.project = options.project;
        this.isDialog = options.isDialog || false;
        this.selectTemplate();
        
        this.model.startTracking();
        this.render();
        
        
    },

    selectTemplate: function(){

        
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){ 
            return el.title == this.project.get("name") ||  el.role !== PL; 
        }.bind(this)), 'role');
    
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0;
    
        var isMemberAllowed = !isPLAllowed && (me.get);
    
        console.log(isMemberAllowed);
        this.model.set('isLeaderAllowedToEdit', isPLAllowed);
        this.model.set('isMemberAllowedToEdit', isMemberAllowed);
        
    
        this.model.set('isEditableStatus', isPLAllowed || isMemberAllowed);
    
        this.template = _.template($(
            '#lims_status_change_template').html());
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
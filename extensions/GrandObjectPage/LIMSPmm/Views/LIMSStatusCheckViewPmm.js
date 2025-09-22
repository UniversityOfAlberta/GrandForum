LIMSStatusCheckViewPmm = Backbone.View.extend({
    tagName: 'div',
    project: null,
    
    events: {
        'click #cancelButton': 'closeDialog', // Button to cancel
        "click .view-comment-history": "showCommentHistory"
    },

    initialize: function(options) {
        this.project = options.project;
        this.isDialog = options.isDialog || false;
        this.selectTemplate();
        
        if (this.project) {
            var members = this.project.members;
            if (members) {
                this.listenTo(members, 'sync', this.render);
            }
        }

        this.model.startTracking();
        this.render();
    },

    showCommentHistory: function(e) {
        LIMSPmmHelper.showCommentsHistory(e, this.model, this.project);
    },

    selectTemplate: function(){
        this.template = _.template($('#lims_status_check_template').html());
    },
    
    render: function() {
        var templateData = this.model.toJSON();

        if (this.project) {
            var projectDataForTemplate = this.project.toJSON();
            if (this.project.members) {
                projectDataForTemplate.members = this.project.members.toJSON();
            }
            templateData.project = projectDataForTemplate;
        }

        this.$el.html(this.template(templateData)); 
        return this.$el;
    },

    closeDialog: function() {
        this.$el.dialog('close');
    }
});
LIMSEmailNotificationViewPmm = Backbone.View.extend({
    template: null,
    events: {
        'click button': 'sendEmailNotification'
    },
    
    initialize: function(options) {
        this.projectId = options.projectId;
        this.tasks = options.tasks;
        
        this.model = new Backbone.Model({
            taskName: '',
            taskType: '',
            assigneeStatus: '',
            emailContent: ''
        });
                
        this.listenTo(this.tasks, "sync add remove", this.render);
        
        this.template = _.template($('#lims_email_notification_view_template').html());
    },
    
    getFilterOptions: function(filterType) {
        var options = [{value: '', option: 'Select...'}];
        
        if (!filterType) return options;

        var allFilterOptions = {
            'taskName': this.tasks.pluck('task') || [],
            'taskType': ['Planning', 'Screening', 'Data Extraction', 'Analysis and Report Writing'],
            'assigneeStatus': ['Assigned', 'Done', 'Closed']
        };

        var filterOptions = allFilterOptions[filterType];
        
        _.each(filterOptions, function(value) {
            options.push({value: value, option: value});
        });
        
        return options;
    },

    sendEmailNotification: function(e) {
        e.preventDefault();
        
        var taskNameOption = this.model.get('taskName');
        var taskTypeOption = this.model.get('taskType');
        var assigneeStatusOption = this.model.get('assigneeStatus');

        var filters = {}

        if (taskNameOption !== ''){ filters['taskName'] = taskNameOption }
        if (taskTypeOption !== ''){ filters['taskType'] = taskTypeOption }
        if (assigneeStatusOption !== ''){ filters['assigneeStatus'] = assigneeStatusOption }

        var emailContent = this.model.get('emailContent');
        
        if (Object.keys(filters).length === 0 || !emailContent) {
            alert('Please fill in all fields before sending.');
            return;
        }
        
        var payload = {
            projectId: this.projectId,
            filters: filters,
            emailContent: emailContent
        };
        
        var $button = this.$('button');
        var originalText = $button.text();
        $button.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: 'index.php?action=api.notifications',
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(response) {
                var successMessage = (response && response.message) ? response.message : 'Request completed successfully.';
                alert(successMessage);
                this.resetForm();
            }.bind(this),
            error: function(xhr, status, error) {
                alert('Error sending email notification: ' + error);
            }.bind(this),
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    },
    
    resetForm: function() {
        this.model.set({
            taskName: '',
            taskType: '',
            assigneeStatus: '',
            emailContent: ''
        });
        
        this.render();
    },
    
    render: function() {
        var templateData = this.model.toJSON();
        templateData.getFilterOptions = this.getFilterOptions.bind(this); 

        // to prevent accordion from closing when it re renders
        var wasActive = this.$('.email-accordion').accordion('option', 'active');
        var isInitialized = this.$('.email-accordion').hasClass('ui-accordion');
        
        this.$el.html(this.template(templateData));
        
        this.$('.email-accordion').accordion({
            collapsible: true,
            active: isInitialized ? wasActive : false,
            heightStyle: "content"
        });
        
        this.$el.attr('style', 'margin-bottom: 20px;');
        return this;
    },
});
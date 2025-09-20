LIMSOpportunityViewPmm = Backbone.View.extend({
    project: null,
    events: {
        'click button': 'sendEmailNotification'
    },

    initialize: function(options){
        this.project = options.project;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.listenTo(this.model, "change:filterType", this.updateFilterValueOptions);
        this.template = _.template($('#lims_opportunity_template').html());
    },
    
    renderTasks: function(){
        if(this.model.tasks.length > 0){
            this.$("#taskContainer").show();
        }
        this.$("#tasks > tbody").empty();
        this.model.tasks.each(function(model){
            var view = new LIMSTaskViewPmm({model: model, project: this.project});
            this.$("#tasks > tbody").append(view.render());
        }.bind(this));
    },

    sendEmailNotification: function(e) {
        e.preventDefault();
        
        var filterType = this.model.get('filterType');
        var filterValue = this.model.get('filterValue');
        var emailContent = this.model.get('emailContent');
        
        if (!filterType || !filterValue || !emailContent) {
            alert('Please fill in all fields before sending.');
            return;
        }
        
        var payload = {
            action: "send_notification",
            filterType: filterType,
            filterValue: filterValue,
            emailContent: emailContent
        };
        
        $.ajax({
            url: 'index.php?action=api.limsopportunitypmm/' + this.model.get('id'),
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(response) {
                alert('Email notification sent successfully!');
                this.model.set({
                    'filterType': '',
                    'filterValue': '', 
                    'emailContent': ''
                });
            }.bind(this),
            error: function(xhr, status, error) {
                alert('Error sending email notification: ' + error);
            }
        });
    },

    updateFilterValueOptions: function() {
        var filterType = this.model.get('filterType');
        var filterOptions = {
            'Task Name': this.model.tasks.pluck('task') || [],
            'Task Type': ['Planning', 'Screening', 'Data Extraction', 'Analysis and Report Writing'],
            'Assignee Status': ['Assigned', 'Done', 'Closed']
        };
        
        var filterValueOptions = filterOptions[filterType] || [];
        this.model.set('filterValue', '');

        var filterValueSelect = this.$('select[name="' + HTML.Name('filterValue') + '"]');
        filterValueSelect.empty();
        
        _.each(filterValueOptions, function(option) {
            filterValueSelect.append('<option value="' + option + '">' + option + '</option>');
        });
    },
    
    render: function(){
        var templateData = this.model.toJSON();
    
        if (this.project) {
            templateData.project = this.project.toJSON();
        }
        templateData.filterOptions = {
            'Task Name': this.model.tasks.pluck('name') || [],
            'Task Type': ['Planning', 'Screening', 'Data Extraction', 'Analysis and Report Writing'],
            'Assignee Status': ['Assigned', 'Done', 'Closed']
        };
        this.$el.html(this.template(templateData));
        this.$("#emailAccordion").accordion({
            collapsible: true,
            active: false,
            heightStyle: "content"
        });
        this.$el.addClass("opportunity");
        return this.$el;
    }

});

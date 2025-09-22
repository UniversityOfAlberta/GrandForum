LIMSEmailNotificationViewPmm = Backbone.View.extend({
    tagName: 'div',
    id: 'emailAccordion',
    
    template: null,
    events: {
        'click button': 'sendEmailNotification'
    },
    
    initialize: function(options) {
        this.opportunity = options.opportunity;
        this.project = options.project;
        
        this.model = new Backbone.Model({
            filterType: '',
            filterValue: '',
            emailContent: ''
        });
        
        this.listenTo(this.model, 'change:filterType', this.onFilterTypeChange);
        
        this.listenTo(this.opportunity.tasks, "sync add remove", this.render);
        
        this.template = _.template(this.getTemplateString());
    },
    
    getTemplateString: function() {
        return `
            <h3>Send an Email</h3>
            <div>
                <div style="display: flex; margin-bottom: 10px;">
                    <div style="width: 100px;">Filter Type:</div>
                    <div style="flex: 1;">
                        <%
                        var filterTypeOptions = [
                            {value: '', option: 'Select...'},
                            {value: 'Task Name', option: 'Task Name'},
                            {value: 'Task Type', option: 'Task Type'},
                            {value: 'Assignee Status', option: 'Assignee Status'}
                        ];
                        %>
                        <%= HTML.Select(this, 'filterType', {
                            options: filterTypeOptions
                        }) %>
                    </div>
                </div>
                <div style="display: flex; margin-bottom: 10px;">
                    <div style="width: 100px;">Filter Value:</div>
                    <div style="flex: 1;">
                        <%= HTML.Select(this, 'filterValue', {
                            options: filterValueOptions
                        }) %>
                    </div>
                </div>
                <div style="margin-bottom: 10px;">
                    <div style="margin-bottom: 5px;">Email Content:</div>
                    <%= HTML.TextArea(this, 'emailContent', {
                        style: "height: 63px;width:100%;box-sizing:border-box;margin:0;"
                    }) %>
                </div>
                <button>Send</button>
            </div>
        `;
    },
    
    onFilterTypeChange: function() {
        this.model.set('filterValue', '');
        this.render();
    },
    
    getFilterTypeOptions: function() {
        return [
            {value: '', option: 'Select...'},
            {value: 'Task Name', option: 'Task Name'},
            {value: 'Task Type', option: 'Task Type'},
            {value: 'Assignee Status', option: 'Assignee Status'}
        ];
    },
    
    getFilterValueOptions: function() {
        var filterType = this.model.get('filterType');
        var options = [{value: '', option: 'Select...'}];
        
        if (!filterType) return options;
        
        var filterOptions = this.getFilterOptionsData();
        var values = filterOptions[filterType] || [];
        
        _.each(values, function(value) {
            options.push({value: value, option: value});
        });
        
        return options;
    },
    
    getFilterOptionsData: function() {
        return {
            'Task Name': this.opportunity.tasks.pluck('task') || [],
            'Task Type': ['Planning', 'Screening', 'Data Extraction', 'Analysis and Report Writing'],
            'Assignee Status': ['Assigned', 'Done', 'Closed']
        };
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
        
        var $button = this.$('button');
        var originalText = $button.text();
        $button.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: 'index.php?action=api.limsopportunitypmm/' + this.opportunity.get('id'),
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(response) {
                alert('Email notification sent successfully!');
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
            filterType: '',
            filterValue: '',
            emailContent: ''
        });
        
        this.render();
    },
    
    render: function() {
        var templateData = {
            filterValueOptions: this.getFilterValueOptions(),
            HTML: HTML
        };
        
        this.$el.html(this.template(templateData));
        this.$el.attr('style', 'margin-bottom: 20px;');
        
        this.$el.accordion({
            collapsible: true,
            active: false,
            heightStyle: "content"
        });
        
        return this.$el;
    },
});
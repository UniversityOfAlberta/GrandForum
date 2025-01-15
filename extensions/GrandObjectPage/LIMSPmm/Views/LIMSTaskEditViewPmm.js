LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",
    
    transactionTree: {
        '': [''],
        'Industry': ['', 'GN Funding', 'Licensing', 'Project Funding','Core Services','Others'],
        'Government': ['', 'Networking/Lobbying','Project Funding','Legacy','Collaboration'],
        'Not-for-Profit': ['', 'Training','Funding','Partnership','Licensing'], 
        'NIs/Board/Committees': ['', 'Existing Partnership','New Project Opportunity','Company Creation','IP','Licensing'], 
        'Media': ['', 'Publications','Social Media','News Media']
    },

    initialize: function(){
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.selectTemplate();
    },
    
    selectTemplate: function(){
        if(!this.model.get('isAllowedToEdit')){
            // Not allowed to edit, use read-only version
            this.template = _.template($('#lims_task_template').html());
        }
        else{
            // Use Edit version
            this.template = _.template($('#lims_task_edit_template').html());
        }
    },
    
    events: {
        "click #deleteTask": "deleteTask",
        "click #addTransaction": "addTransaction",
        "click #transactions .delete-icon": "deleteTransaction"
    },
    
    addTransaction: function(){
        var transactions = this.model.get('transactions');
        transactions[this.model.get('transactions').length] = {type: '', date: ''};
        
        this.model.set('transactions', transactions);
        this.renderTransactions();
    },
    
    deleteTask: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },
    
    deleteTransaction: function(el){
        var id = $(el.currentTarget).attr('data-id');
        this.model.get('transactions').splice(id, 1);
        this.renderTransactions();
    },
    
    renderTransactions: function(){
        this.$("#transactions").empty();
        _.each(this.model.get('transactions'), function(transaction, i){
            this.$("#transactions").append("<div>");
            this.$("#transactions div").last().append(HTML.Select(this, 'transactions.' + i + '.type', {options: this.transactionTree[this.model.opportunity.get('category')] }));
            this.$("#transactions div").last().append("&nbsp;" + HTML.DatePicker(this, 'transactions.' + i + '.date', {format: 'yy-mm-dd', style: 'width:5em'}));
            this.$("#transactions div").last().append("<span data-id='" + i + "' class='delete-icon' style='vertical-align: middle; margin-left:5px;' title='Delete Transaction'></span>");
        }.bind(this));
        this.delegateEvents();
        this.afterRender();
    },
    
    render: function(){
        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            _.defer(function(){
                this.$('select[name=assignee_id]').chosen();
            }.bind(this));
            if(this.model.get('isAllowedToEdit')){
                this.renderTransactions();
            }
        }
        return this.$el;
    }

});

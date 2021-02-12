CRMTaskEditView = Backbone.View.extend({

    tagName: "li",
    
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
        this.listenTo(this.model, "change:transactions", this.renderTransaction);
        this.template = _.template($('#crm_task_edit_template').html());
    },
    
    events: {
        "click #addTransaction": "addTransaction"
    },
    
    addTransaction: function(){
        var transactions = this.model.get('transactions');
        transactions[this.model.get('transactions').length] = {type: '', date: ''};
        
        this.model.set('transactions', transactions);
        this.renderTransactions();
    },
    
    renderTransactions: function(){
        this.$("#transactions").empty();
        _.each(this.model.get('transactions'), function(transaction, i){
            this.$("#transactions").append("<div>");
            this.$("#transactions div").last().append(HTML.Select(this, 'transactions.' + i + '.type', {options: this.transactionTree[this.model.opportunity.get('category')] }));
            this.$("#transactions div").last().append(HTML.DatePicker(this, 'transactions.' + i + '.date', {format: 'yy-mm-dd', style: 'width:5em'}));
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
            this.renderTransactions();
        }
        return this.$el;
    }

});

CRMTaskEditView = Backbone.View.extend({

    tagName: "li",

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
            this.$("#transactions div").last().append(HTML.Select(this, 'transactions.' + i + '.type', {options: ['GN Funding', 'Licensing', 'Project Funding','Core Services','Others']}));
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

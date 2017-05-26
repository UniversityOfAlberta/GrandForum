ProductHistoriesView = Backbone.View.extend({

    initialize: function(){
        Backbone.Subviews.add(this);
        this.model.bind('sync', this.render, this);
        this.productHistories = new ProductHistories();
        this.template = _.template($("#product_histories_template").html());
        this.render();
    },
    
    subviewCreators: {
        "personSelect": function() {
            return new PersonSelectView({model: this.model});
        },
        "productHistories": function() {
            return new HistoriesView({model: this.productHistories});
        }
    },
    
    updatePerson: function(e){
        var personId = $(e.currentTarget).val();
        this.productHistories.personId = personId;
        this.productHistories.fetch();
    },
    
    events: {
        "change select#personSelect": "updatePerson"
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

PersonSelectView = Backbone.View.extend({
    initialize: function(){
        this.template = _.template($("#person_select_template").html());
        this.model.fetch();
        this.render();
    },
    
    render: function(){
        this.$el.html(this.template(this.model));
        this.$('select').chosen();
        return this.$el;
    }
});

HistoriesView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('sync', this.render, this);
        this.template = _.template($("#histories_template").html());
    },
    
    add: function(rule){
        rule.bind('change', this.render, this);
        this.render();
    },
    
    remove: function(rule){
        rule.unbind('change', this.render, this);
        this.render();
    },
    
    deleteHistory: function(e){
        $(e.currentTarget).attr("disabled", true);
        clearAllMessages();
        var ruleId = $(e.currentTarget).parent().parent().attr('id');
        var rule = this.model.rules.get(ruleId);
        if(rule.isNew()){
            rule.destroy();
            return;
        }
        $.when(rule.destroy()).then($.proxy(function(){
            addSuccess("Mailing List Rule Deleted");
        }, this),$.proxy(function(){
            addError("There was an error deleting the rule");
            $(e.currentTarget).removeAttr("disabled");
        }, this));
    },
    
    addNewHistory: function(e){
        this.model.rules.push(new MailingListRule({listId: this.model.get('id')}));
    },
    
    saveHistory: function(e){
        this.$("button#saveRules").attr("disabled", true);
        clearAllMessages();
        var ajax = Array();
        this.model.rules.each(function(r){
            ajax.push(r.save());
        });
        $.when.apply($, ajax).then($.proxy(function(){
            addSuccess("Mailing List Rules Saved");
            this.$("button#saveRules").removeAttr("disabled");
        }, this),$.proxy(function(){
            addError("There was an error saving the rules");
            this.$("button#saveRules").removeAttr("disabled");
        }, this));
    },
    
    events: {
        "click button#addNewHistory": "addNewHistory",
        "click button#saveRules": "saveRules"
    },
    
    updateHistories: function(){
        this.model.each($.proxy(function(productHistory){
            var view = new HistoryView({model: productHistory});
            this.$("#histories").append(view.render());
        }, this));
    },
    
    render: function(){
        if(this.model.personId != null){
            this.$el.html(this.template(this.model));
            this.updateHistories();
            this.delegateEvents(this.events);
        }
        else{
            this.$el.empty();
        }
        return this.$el;
    }
});

HistoryView = Backbone.View.extend({
    
    tagName: "tr",
    
    initialize: function(){
        this.template = _.template($("#history_template").html());
    },
    
    events: {
        "click button.deleteHistory": "deleteHistory"
    },
    
    render: function(){
        this.$el.html(this.template(this.model));
        this.$("input[name=value]").forceNumeric({min: 0, max: 9999});
        this.$("input[name=value]").attr('type', 'number');
        this.$("input[name=value]").attr('min', '0');
        this.$("input[name=value]").attr('max', 9999);
        
        this.$("input[name=year]").forceNumeric({min: 1980, max: new Date().getFullYear()});
        this.$("input[name=year]").attr('type', 'number');
        this.$("input[name=year]").attr('min', '1980');
        this.$("input[name=year]").attr('max', new Date().getFullYear());
        return this.$el;
    },
});

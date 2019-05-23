MailingListRulesView = Backbone.View.extend({

    initialize: function(){
        Backbone.Subviews.add(this);
        this.model.bind('sync', this.render, this);
        this.list = new MailingList();
        this.template = _.template($("#mailing_list_rules_template").html());
        this.render();
    },
    
    subviewCreators: {
        "listSelect": function() {
            return new ListSelectView({model: this.model});
        },
        "listRules": function(){
            return new ListRulesView({model: this.list});
        }
    },
    
    updateList: function(e){
        var listId = $(e.currentTarget).val();
        this.list.set('id', listId);
        this.list.fetch();
    },
    
    events: {
        "change select#listSelect": "updateList"
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

ListSelectView = Backbone.View.extend({
    initialize: function(){
        this.template = _.template($("#list_select_template").html());
        this.model.fetch();
        this.render();
    },
    
    render: function(){
        this.$el.html(this.template(this.model));
        return this.$el;
    }
});

ListRulesView = Backbone.View.extend({
    initialize: function(){
        this.model.bind('sync', this.updateRules, this);
        this.template = _.template($("#list_rules_template").html());
    },
    
    updateRules: function(){
        var rules = this.model.getRules();
        rules.bind('sync', this.render, this);
        rules.bind('add', this.add, this);
        rules.bind('remove', this.remove, this);
    },
    
    add: function(rule){
        rule.bind('change', this.render, this);
        this.render();
    },
    
    remove: function(rule){
        rule.unbind('change', this.render, this);
        this.render();
    },
    
    updateType: function(e){
        var value = $(e.currentTarget).val();
        var ruleId = $(e.currentTarget).parent().parent().attr('id');
        var rule = this.model.rules.get(ruleId);
        rule.set('type', value);
    },
    
    updateValue: function(e){
        var value = $(e.currentTarget).val();
        var ruleId = $(e.currentTarget).parent().parent().attr('id');
        var rule = this.model.rules.get(ruleId);
        rule.set('value', value);
    },
    
    deleteRule: function(e){
        $(e.currentTarget).attr("disabled", true);
        clearAllMessages();
        var ruleId = $(e.currentTarget).parent().parent().attr('id');
        var rule = this.model.rules.get(ruleId);
        if(rule.isNew()){
            rule.destroy();
            return;
        }
        $.when(rule.destroy()).then(function(){
            addSuccess("Mailing List Rule Deleted");
        }.bind(this), function(){
            addError("There was an error deleting the rule");
            $(e.currentTarget).removeAttr("disabled");
        }.bind(this));
    },
    
    addNewRule: function(e){
        this.model.rules.push(new MailingListRule({listId: this.model.get('id')}));
    },
    
    saveRules: function(e){
        this.$("button#saveRules").attr("disabled", true);
        clearAllMessages();
        var ajax = Array();
        this.model.rules.each(function(r){
            ajax.push(r.save());
        });
        $.when.apply($, ajax).then(function(){
            addSuccess("Mailing List Rules Saved");
            this.$("button#saveRules").removeAttr("disabled");
        }.bind(this), function(){
            addError("There was an error saving the rules");
            this.$("button#saveRules").removeAttr("disabled");
        }.bind(this));
    },
    
    events: {
        "change select[name=type]": "updateType",
        "change select[name=value]": "updateValue",
        "click button.deleteRule": "deleteRule",
        "click button#addNewRule": "addNewRule",
        "click button#saveRules": "saveRules"
    },
    
    render: function(){
        if(this.model.get('id') != null){
            this.$el.html(this.template(this.model.rules));
            this.delegateEvents(this.events);
        }
        else{
            this.$el.empty();
        }
        return this.$el;
    }
});

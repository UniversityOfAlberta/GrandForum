MailingList = Backbone.Model.extend({

    initialize: function(){
        this.rules = new MailingListRules();
    },
    
    getRules: function(){
        this.rules.url = this.urlRoot + '/' + this.get('id') + '/rules';
        this.rules.fetch();
        return this.rules;
    },

    urlRoot: 'index.php?action=api.mailingList',
    
    defaults: {
        id: null,
        name: ""
    }
});

MailingLists = Backbone.Collection.extend({
    model: MailingList,
    
    url: 'index.php?action=api.mailingList'
});

MailingListRule = RelationModel.extend({
    initialize: function(){
        this.on("change:type", this.changeValues);
        this.on("change:possibleValues", function(){
            if(this.get('value') == ""){
                this.set('value', _.first(this.get('possibleValues')));
            }
        });
        this.on("sync", this.changeValues);
        this.changeValues(this);
    },
    
    changeValues: function(){
        var type = this.get('type');
        switch(type){
            case "ROLE":
                this.set('possibleValues', wgRoles);
                break;
            case "PROJ":
                this.set('possibleValues', new Array());
                break;
            case "PHASE":
                this.set('possibleValues', new Array());
                break;
            case "LOC":
                this.set('possibleValues', new Array());
                break;
        }
    },
    
    urlRoot: function(){
        return 'index.php?action=api.mailingList/' + this.get('listId') + '/rules'
    },
    
    getOwner: function(){
        var list = new MailingList({id: this.get('listId')});
        return list;
    },
    
    getTarget: function(){
        var rule = new MailingListRule({id: parseInt(this.get('ruleId'))});
        return rule;
    },
    
    defaults: {
        listId: "",
        ruleId: "",
        type: "ROLE",
        value: "",
        possibleValues: Array()
    }
});

MailingListRules = Backbone.Collection.extend({
    model: MailingListRule,
    
    url: 'index.php?action=api.mailingList'
});

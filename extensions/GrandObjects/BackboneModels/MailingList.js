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
            var found = false;
            _.each(this.get('possibleValues').ids, function(val){
                if(val == this.get('value')){
                    found = true;
                }
            }.bind(this));
            if(!found){
                this.set('value', _.first(this.get('possibleValues').ids));
            }
        });
        this.on("sync", this.changeValues);
        this.changeValues(this);
    },
    
    changeValues: function(){
        var type = this.get('type');
        switch(type){
            case "ROLE":
                var candidates = _.map(wgRoles, function(role){ return role + "-Candidate"; });
                this.set('possibleValues', {ids: wgRoles.concat(candidates).concat("Stakeholder"), names: wgRoles.concat(candidates).concat("Stakeholder")});
                break;
            case "SUB-ROLE":
                this.set('possibleValues', {ids: subRoles, names: subRoles});
                break;
            case "PROJ":
                var projects = new Projects();
                $.when(projects.fetch()).then(function(){
                    this.set('possibleValues', {ids: projects.pluck('id'), names: projects.pluck('name')});
                }.bind(this));
                break;
            case "PHASE":
                var phases = _.range(1, projectPhase+1);
                this.set('possibleValues', {ids: phases, names: phases});
                break;
            case "LOC":
                var unis = new Universities();
                $.when(unis.fetch()).then(function(){
                    this.set('possibleValues', {ids: unis.pluck('id'), names: unis.pluck('name'), groups: unis.pluck('province')});
                }.bind(this));
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
        possibleValues: {ids: new Array(), names: new Array()}
    }
});

MailingListRules = Backbone.Collection.extend({
    model: MailingListRule,
    
    url: 'index.php?action=api.mailingList'
});

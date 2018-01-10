Contribution = Backbone.Model.extend({

    initialize: function(){
        this.on("change:partners", this.updateTotals);
        this.on("delete:partners", this.updateTotals);
        this.on("add:partners", this.updateTotals);
    },
    
    updateTotals: function(){
        var partners = this.get('partners');
        var cash = _.reduce(_.pluck(partners, 'cash'), function(ret, a){ 
            if(_.isFinite(a)){ 
                return parseInt(ret) + parseInt(a);
            }
            return ret;
        }, 0);
        var inkind = _.reduce(_.pluck(partners, 'inkind'), function(ret, a){ 
            if(_.isFinite(parseInt(a))){ 
                return parseInt(ret) + parseInt(a);
            }
            return ret;
        }, 0);
        var total = cash + inkind;
        this.set('cash', cash);
        this.set('inkind', inkind);
        this.set('total', total);
    },

    url: function(){
        if(this.get('revId') != ""){
            return 'index.php?action=api.contribution/' + this.get('id') + '/' + this.get('revId');
        }
        else{
            return 'index.php?action=api.contribution/' + this.get('id');
        }
    },
    
    addPartner: function(){
        var partners = this.get('partners');
        var partner = {
            name:	  "",
            contact:  "",	
            industry: "",
            level:	  "",
            type:	  "",
            subtype:  "",
            other_subtype: "",
            cash:	  0,
            inkind:	  0,
            total:	  0
        };  
        partners.push(partner);
        this.set('partners', _.clone(partners));
        this.trigger("add:partners");
    },

    defaults: function() {
        return{
            id: null,
            revId: "",
            name: "",
            description: "",
            start: "",
            end: "",
            authors: new Array(),
            partners: new Array(),
            projects: new Array(),
            cash: 0,
            inkind: 0,
            total: 0,
            url: ""
        };
    }

});

Contributions = Backbone.Collection.extend({

    model: Contribution,

    url: function(){
        return 'index.php?action=api.contribution';
    }

});

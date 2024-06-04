ProductHistoriesView = Backbone.View.extend({

    initialize: function(){
        Backbone.Subviews.add(this);
        this.productHistories = new ProductHistories();
        this.template = _.template($("#product_histories_template").html());
        _.defer(this.render.bind(this));
    },
    
    subviewCreators: {
        "personSelect": function() {
            return new PersonSelectView();
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
        this.render();
    },
    
    render: function(){
        this.$el.html(this.template());
        this.$('select').chosen();
        return this.$el;
    }
});

HistoriesView = Backbone.View.extend({

    views: {},

    initialize: function(){
        this.model.bind('sync', this.render, this);
        //this.model.bind('add', this.render, this);
        this.model.bind('remove', this.removeHistory, this);
        this.template = _.template($("#histories_template").html());
    },
    
    addNewHistory: function(e){
        var newProductHistory = new ProductHistory({user_id: this.model.personId, type: 'Refereed', value: 0, year: new Date().getFullYear()})
        this.model.add(newProductHistory);
        var view = new HistoryView({model: newProductHistory});
        this.$("#histories").append(view.render());
        this.views[newProductHistory.cid] = view;
    },
    
    removeHistory: function(e){
        this.views[e.cid].remove();
    },
    
    saveHistory: function(e){
        this.$("button#saveHistory").attr("disabled", true);
        clearAllMessages();
        var ajax = Array();
        this.model.each(function(history){
            ajax.push(history.save());
        });
        $.when.apply($, ajax).then(function(){
            addSuccess("Product Histories Saved");
            this.$("button#saveHistory").removeAttr("disabled");
        }.bind(this), function(){
            addError("There was an error saving the product histories");
            this.$("button#saveHistory").removeAttr("disabled");
        }.bind(this));
    },
    
    events: {
        "click button#addNewHistory": "addNewHistory",
        "click button#saveHistory": "saveHistory"
    },
    
    updateHistories: function(){
        _.each(this.views, function(v){
            v.remove();
        });
        this.views = {};
        this.model.each(function(productHistory){
            var view = new HistoryView({model: productHistory});
            this.$("#histories").append(view.render());
            this.views[productHistory.cid] = view;
        }.bind(this));
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
    
    deleteHistory: function(e){
        $(e.currentTarget).attr("disabled", true);
        clearAllMessages();
        if(this.model.isNew()){
            this.model.destroy();
            return;
        }
        $.when(this.model.destroy()).then(function(){
            addSuccess("Product History Rule Deleted");
        }.bind(this), function(){
            addError("There was an error deleting the history");
            $(e.currentTarget).removeAttr("disabled");
        }.bind(this));
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

function between(object, start, end){
    var start1 = object.get('startDate');
    var end1 = object.get('endDate');
    return ((start1 <= start && end1   >= end)   ||             // ---s1----s----e----e1---
            (end1   >= start && end1   <= end)   ||             // ---s----e1----e---
            (start1 >= start && start1 <= end)   ||             // ---s----s1----e---
            (start1 <= start && (end1 == ZOTT || end1 == ZOT))  // ---s----s1----infinity=e1
           );
}

Backbone.Model.prototype.getTarget = function(){
    return this;
}

RangeCollection = Backbone.Collection.extend({

    xhrs: new Array(),
    
    multiUrl: function(){
        return "";
    },
    
    fetch: function(options){
        var xhr = Backbone.Collection.prototype.fetch.call(this, options);
        this.xhrs.push(xhr);
        return xhr;
    },

    /**
     * Returns a new Collection
     * (Should be overriden)
     */
    newModel: function(){
        return new RelationModel();
    },
    
    /**
     * Returns a collection of all the Models
     */
    getAll: function(){
        var allModels = this.newModel();
        if(this.multiUrl() != ""){
            allModels.url = new Array(this.multiUrl()).concat(_.without(_.pluck(this.models, 'id'), null));            
            this.xhrs.push(allModels.fetch({silent: true}));
            return allModels;
        }
        else{
            _.each(this.models, function(model){
                var target = model.getTarget();
                if(target != model){
                    this.xhrs.push(target.fetch());
                }
                allModels.add(target);
            }, this);
        }
        return allModels;
    },

    /**
     * Returns a collection of Models which the Model is currently
     */
    getCurrent: function(){
        var now = new Date();
        var date = Date.format(now, 'yyyy-MM-dd HH:mm:ss');
        return this.getDuring(date, '5000');  
    },
    
    /**
     * Returns a collection of Models which were from the past (no longer active or whatever)
     * TODO: Make this work with ajax requests.
     */
    getOld: function(){
        var oldModels = this.newModel();
        var all = this.getAll();
        var current = this.getCurrent();
        all.remove(current.models);
        return all;
    },
    
    fetching: function(){
        return (this.xhrs.length > 0 && this.ready().state() != "resolved");
    },
    
    ready: function(){
        return $.when.apply(null, this.xhrs);
    },
    
    /**
     * Returns a collection of Models which fall between startDate and endDate
     */
    getDuring: function(startDate, endDate){
        var modelsDuring = this.newModel();
        _.each(this.models, function(model){
            if(between(model, startDate, endDate)){
                var target = model.getTarget();
                if(target != model){
                    this.xhrs.push(target.fetch());
                }
                modelsDuring.add(target);
            }
        }, this);
        return modelsDuring;
    }

});

function between(object, start, end){
    var start1 = object.get('startDate');
    var end1 = object.get('endDate');
    return ((start1 <= start && end1   >= end)   ||             // ---s1----s----e----e1---
            (end1   >= start && end1   <= end)   ||             // ---s----e1----e---
            (start1 >= start && start1 <= end)   ||             // ---s----s1----e---
            (start1 <= start && end1 == '0000-00-00 00:00:00')  // ---s----s1----infinity=e1
           );
}

RangeCollection = Backbone.Collection.extend({

    xhrs: Array(),

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
        allModels = this.newModel();
        _.each(this.models, function(model){
            var target = model.getTarget();
            this.xhrs.push(target.fetch());
            allModels.add(target);
        }, this);
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
    
    ready: function(){
        return $.when.apply(null, this.xhrs);
    },
    
    /**
     * Returns a collection of Models which fall between startDate and endDate
     */
    getDuring: function(startDate, endDate){
        modelsDuring = this.newModel();
        _.each(this.models, function(model){
            if(between(model, startDate, endDate)){
                var target = model.getTarget();
                this.xhrs.push(target.fetch());
                modelsDuring.add(target);
            }
        }, this);
        return modelsDuring;
    }

});

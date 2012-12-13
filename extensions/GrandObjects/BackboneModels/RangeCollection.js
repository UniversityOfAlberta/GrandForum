function between(object, startDate, endDate){
    return ((object.get('endDate') >= endDate && object.get('startDate') <= startDate) ||
            (object.get('startDate') <= startDate && object.get('endDate') >= startDate) ||
            (object.get('startDate') <= endDate && object.get('startDate') >= startDate) ||
            (object.get('endDate') >= endDate && object.get('startDate') <= endDate));
}

RangeCollection = Backbone.Collection.extend({

    /**
     * Returns a new Collection
     * (Should be overriden)
     */
    newModel: function(){
        return new RelationModel();
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
     * Returns a collection of Models which fall between startDate and endDate
     */
    getDuring: function(startDate, endDate){
        modelsDuring = this.newModel();
        _.each(this.models, function(model){
            if(between(model, startDate, endDate)){
                modelsDuring.add(model.getTarget());
            }
        });        
        return modelsDuring;
    }

});

DataCollection = Backbone.Model.extend({

    initialize: function(){
        this.fetch();
        this.on('change:data', _.throttle(function(){
            this.save();
        }.bind(this), 3000));
    },

    url: function(){
        if(this.get('id') != null){
            return "index.php?action=api.datacollection/" + this.get('id');
        }
        if(this.get('userId') != "" && this.get('page')){
            return "index.php?action=api.datacollection/" + this.get('userId') + "/" + this.get('page');
        }
        return "index.php?action=api.datacollection";
    },
    
    getField: function(field){
        var data = this.get('data');
        return data[field];
    },
    
    setField: function(field, value){
        var data = this.get('data');
        data[field] = value;
        this.set('data', data);
        this.trigger("change:data");
    },
    
    increment: function(field){
        var fieldValue = this.getField(field);
        if(fieldValue == undefined){
            fieldValue = 0;
        }
        fieldValue++;
        this.setField(field, fieldValue);
    },

    defaults: function() {
        return{
            id: null,
            userId: "",
            page: "",
            data: {}
        };
    }

});

DataCollections = Backbone.Collection.extend({

    model: DataCollection

});

DataCollection = Backbone.Model.extend({

    initialize: function(){
        var xhr = this.fetch();
        $.when(xhr).always(function(){
            this.on('change:data', _.throttle(function(){
                this.save();
            }.bind(this), 3000));
        }.bind(this));
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
    
    getField: function(field, def){
        var data = this.get('data');
        if(data[field] == undefined && def != undefined){
            data[field] = def;
        }
        return data[field];
    },
    
    setField: function(field, value){
        var data = this.get('data');
        data[field] = value;
        this.set('data', data);
        this.trigger("change:data");
    },
    
    // Increments a value, useful for keeping track of counters
    increment: function(field){
        var fieldValue = this.getField(field, 0);
        fieldValue++;
        this.setField(field, fieldValue);
    },
    
    // Initializes an array which can be used for tracking how much of a video has been watched
    video: function(field, duration){
        return this.getField(field, _.omit([].fill.call({length: Math.ceil(duration)}, 0), 'length'));
    },
    
    // Used to sum one of the fields (if it is an array)
    sum: function(field){
        return _.reduce(this.video(field, 1), function(memo, num){ return memo + num; });
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

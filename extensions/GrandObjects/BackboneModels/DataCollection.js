DataCollection = Backbone.Model.extend({

    xhr: false,
    dataCollectionInterval: null,
    
    ready: function(){
        return $.when(this.xhr);
    },

    initialize: function(){
        
    },
    
    init: function(userId, page){
        clearInterval(this.dataCollectionInterval);
        if(this.get('userId') != userId ||
           this.get('page') != page){
            this.off('change:data');
            this.set('id', null);
            this.set('userId', userId);
            this.set('page', page);
            this.set('data', {});
            this.xhr = this.fetch();
            
            this.ready().always(function(){
                this.once('change:data', function(){
                    this.on('change:data', _.throttle(function(){
                        this.save();
                    }.bind(this), 3000));
                    this.trigger("change:data");
                }.bind(this));
            }.bind(this));
        }
    },

    url: function(){
        if(this.get('id') != null){
            return "index.php?action=api.datacollection/" + this.get('id');
        }
        if(this.get('userId') != "" && this.get('page')){
            return "index.php?action=api.datacollection/" + this.get('userId') + "/" + btoa(unaccentChars(this.get('page'))).replace("/", "-slash-");
        }
        return "index.php?action=api.datacollection";
    },
    
    getField: function(field, def){
        var data = this.get('data');
        if(data[field] == undefined && def != undefined){
            return def;
        }
        return data[field];
    },
    
    setField: function(field, value){
        this.ready().always(function(){
            var data = this.get('data');
            data[field] = value;
            this.set('data', data);
            this.trigger("change:data");
        }.bind(this));
    },
    
    // Increments a value, useful for keeping track of counters
    increment: function(field){
        this.ready().always(function(){
            var fieldValue = this.getField(field, 0);
            fieldValue++;
            this.setField(field, fieldValue);
        }.bind(this));
    },
    
    // Appends a value to an array
    append: function(field, value, unique){
        this.ready().always(function(){
            var data = this.getField(field, []);
            data.push(value);
            if(typeof unique != 'undefined' && unique === true){
                data = _.uniq(data);
            }
            this.setField(field, data);
        }.bind(this));
    },
    
    // Adds an event listener to the radio buttons specified by the selector
    radio: function(field, selector){
        $(selector).change(function(e){
            this.setField(field, $(e.target).val());
        }.bind(this));
    },
    
    // Starts a timer (can only run 1 at a time)
    timer: function(field){
        this.dataCollectionInterval = setInterval(function(){
            this.increment(field);
        }.bind(this), 1000);
    },
    
    // Initializes an array which can be used for tracking how much of a video has been watched
    video: function(field, duration){
        return this.getField(field, _.values(_.omit([].fill.call({length: Math.ceil(duration)}, 0), 'length')));
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
            data: {},
            created: "",
            modified: ""
        };
    }

});

var dc = new DataCollection();

DataCollections = Backbone.Collection.extend({

    model: DataCollection

});

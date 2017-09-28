GrantAwardsView = Backbone.View.extend({

    table: null,

    initialize: function(){
        this.model.fetch();
        this.template = _.template($('#grantawards_template').html());
        this.model.bind('partialSync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', this.removeThrobber, this);
    },
    
    processData: function(start){
        // This method is purposely not using Backbone views for performance reasons
        var data = Array();
        var i = -1;
        _.each(this.model.toJSON(), function(model, index){
            i++;
            if(i < start){
                return;
            }
            var row = new Array("<a href='" + model.url + "'>" + model.application_title + "</a>", 
                                model.start_year + " - " + model.end_year,
                                model.competition_year,
                                number_format(model.amount));
            data.push(row);
        }, this);
        return data;
    },
    
    removeThrobber: function(){
        this.$(".throbber").hide();
    },
    
    renderPartial: function(start){
        if(start == undefined){
            start = 0;
        }
        if(this.table != undefined){
            _.defer($.proxy(function(){
                var data = this.processData(start);
                this.table.rows.add(data);
                this.table.draw();
            }, this));
            return this.$el;
        }
        return this.render();
    },

    render: function(){
        main.set('title', "Grant Awards");
        this.$el.css('display', 'none');
        this.$el.html(this.template());
        var data = this.processData(0);
        this.table = this.$("#grantawards").DataTable({
            iDisplayLength: 100,
            autoWidth: false,
            aaData : data,
            deferRender: true
        });
        this.$el.css('display', 'block');
        return this.$el;
    }

});

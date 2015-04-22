ManagePeopleView = Backbone.View.extend({

    table: null,
    subViews: new Array(),
    allPeople: null,
    people: null,

    initialize: function(){
        this.allPeople = new People();
        this.allPeople.fetch();
        this.template = _.template($('#manage_people_template').html());
        this.listenTo(this.model, "sync", function(){
            this.people = this.model;
            this.listenTo(this.people, "add", this.addRows);
            this.listenTo(this.people, "remove", this.addRows);
            this.render();
        }, this);
    },
    
    addRows: function(){
        var searchStr = "";
        var order = [4, 'desc'];
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        // First remove deleted models
        _.each(this.subViews, $.proxy(function(view){
            var m = view.model;
            if(this.people.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }, this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        this.people.each($.proxy(function(p, i){
            if(!_.contains(models, p.id)){
                // Person isn't in the table yet
                var row = new ManagePeopleRowView({model: p, parent: this});
                this.subViews.push(row);
                this.$("#personRows").append(row.$el);
            }
        }, this));
        _.each(this.subViews, function(row){
            row.render();
        });
        var end = new Date();
        this.createDataTable(order, searchStr);
    },
    
    createDataTable: function(order, searchStr){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
	                                                 'aLengthMenu': [[-1], ['All']]});
	    this.table.draw();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
    },
    
    events: {

    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

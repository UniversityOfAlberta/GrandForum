ProductListView = Backbone.View.extend({

    productTag: null,
    table: null,

    initialize: function(){
        this.model.fetch();
        this.model.bind('partialSync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', this.removeThrobber, this);
        this.template = _.template($('#product_list_template').html());
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
            var authors = Array();
            var projects = Array();
            _.each(model.authors, function(author, aId){
                if(author.url != ''){
                    authors.push("<a href='" + author.url + "' target='_blank'>" + author.name + "</a>");
                }
                else{
                    authors.push(author.name);
                }
            });
            var row = new Array("<span style='white-space: nowrap;'>" + model.date + "</span>", 
                                "<span style='white-space: nowrap;'>" + model.type + "</span>",
                                "<a href='" + model.url + "'>" + model.title + "</a>", authors.join('; '));
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
            _.defer(function(){
                var data = this.processData(start);
                this.table.rows.add(data);
                this.table.draw();
            }.bind(this));
            return this.$el;
        }
        return this.render();
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        var templateData = {'url' : '', 'title' : ''};
        if(Backbone.history.fragment.indexOf('nonGrand') == -1){
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment + '/nonGrand';
            templateData.name = 'Non ' + main.get('title');
        }
        else{
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment.replace('/nonGrand', '');
            templateData.name = main.get('title').replace('Non ', '');
        }
        this.$el.html(this.template(templateData));
        var showButton = this.$("#showButton").detach();
        var throbber = this.$(".throbber").detach();
        var data = this.processData(0);
        this.table = this.$('#listTable').DataTable({'iDisplayLength': 100,
	                                    'aaSorting': [[0,'desc'], [1,'asc']],
	                                    'autoWidth': false,
	                                    'aaData' : data,
	                                    'deferRender': true,
	                                    'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	    this.$("#listTable_length").append(showButton);
	    this.$("#listTable_length").append(throbber);
        this.$el.css('display', 'block');
        return this.$el;
    }

});

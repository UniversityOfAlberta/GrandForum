ProductListView = Backbone.View.extend({

    productTag: null,
    table: null,

    initialize: function(){
        this.model.fetch();
        this.model.bind('partialSync', this.render, this);
        this.model.bind('sync', this.render, this);
        this.model.bind('sync', this.removeThrobber, this);
        this.template = _.template($('#product_list_template').html());
    },
    
    processData: function(){
        // This method is purposely not using Backbone views for performance reasons
        var data = Array();
        _.each(this.model.toJSON(), function(model, index){
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
            _.each(model.projects, function(project, aId){
                if(project.url != ''){
                    projects.push("<a href='" + project.url + "' target='_blank'>" + project.name + "</a>");
                }
                else{
                    projects.push(project.name);
                }
            });
            var row = new Array("<span style='white-space: nowrap;'>" + model.date + "</span>", 
                                "<span style='white-space: nowrap;'>" + model.type + "</span>",
                                "<a href='" + model.url + "'>" + model.title + "</a>", authors.join(', '));
            if(projectsEnabled){
                row.push(projects.join(', '));
            }
            data.push(row);
        }, this);
        return data;
    },
    
    removeThrobber: function(){
        this.$(".throbber").hide();
    },
    
    render: function(){
        if(this.table != undefined){
            _.defer($.proxy(function(){
                var data = this.processData();
                this.table.rows().remove();
                this.table.rows.add(data);
                this.table.draw();
            }, this));
            return this.$el;
        }
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
        var data = this.processData();
        this.table = this.$('#listTable').DataTable({'iDisplayLength': 100,
	                                    'aaSorting': [ [0,'desc'], [1,'asc']],
	                                    'autoWidth': false,
	                                    'aaData' : data,
	                                    'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	    this.$("#listTable_length").append(showButton);
	    this.$("#listTable_length").append(throbber);
        this.$el.css('display', 'block');
        return this.$el;
    }

});

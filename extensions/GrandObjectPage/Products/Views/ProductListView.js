ProductListView = Backbone.View.extend({

    productTag: null,

    initialize: function(){
        this.model.fetch();
        this.model.bind('reset', this.render, this);
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
            data.push(new Array("<span style='white-space: nowrap;'>" + model.date + "</span>", 
                                "<span style='white-space: nowrap;'>" + model.type + "</span>",
                                "<a href='" + model.url + "'>" + model.title + "</a>", authors.join(', '), projects.join(', ')));
        }, this);
        return data;
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        var templateData = {'url' : '', 'title' : ''};
        if(Backbone.history.fragment.indexOf('nonGrand') == -1){
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment + '/nonGrand';
            templateData.name = 'Non-' + main.get('title');
        }
        else{
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment.replace('/nonGrand', '');
            templateData.name = main.get('title').replace('Non-', '');
        }
        this.$el.html(this.template(templateData));
        var showButton = this.$("#showButton").detach();
        var data = this.processData();
        this.$el.find('#listTable').dataTable({'iDisplayLength': 100,
	                                           'aaSorting': [ [0,'desc'], [1,'asc'], [4, 'asc'] ],
	                                           'aaData' : data,
	                                           'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	    this.$("#listTable_length").append(showButton);
        this.$el.css('display', 'block');
        return this.$el;
    }

});
/*
ProductRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.template = _.template($('#product_row_template').html());;
    },
    
    renderAuthors: function(){
        var views = Array();
        _.each(this.model.get('authors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name,
                                 url: author.url,
                                 target: '_blank'});
            views.push(new PersonLinkView({model: link}).render());
        });
        csv = new CSVView({el: this.$('#productAuthors'), model: views}).render();
    },
    
    renderProjects: function(){
        var views = Array();
        _.each(this.model.get('projects'), function(project, index){
            var link = new Link({id: project.id,
                                 text: project.name,
                                 url: project.url,
                                 target: '_blank'});
            views.push(new ProjectLinkView({model: link}).render());
        });
        csv = new CSVView({el: this.$('#productProjects'), model: views}).render();
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderProjects();
        return this.$el;
    }
    
});
*/

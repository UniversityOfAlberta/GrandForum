ManageProductsView = Backbone.View.extend({

    products: null,
    projects: null,
    nProjects: 0,

    initialize: function(){
        this.template = _.template($('#manage_products_template').html());
        me.getProjects();
        this.model.bind('sync', function(){
            this.products = this.model.getAll();
            this.projects = me.projects.getCurrent();
            this.model.ready().then($.proxy(function(){
                me.projects.ready().then(this.render);
            }, this));
        }, this);
    },
    
    addRows: function(){
        this.products.each(function(p){
            var row = new ManageProductsViewRow({model: p});
            this.$("#productRows").append(row.render());
        });
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        this.$('#listTable').dataTable({'bPaginate': false,
                                        'autoWidth': false,
                                        "aoColumnDefs": [
                                            {'bSortable': false, 'aTargets': _.range(0, this.projects.length + 1) }
                                        ],
	                                    'aaSorting': [ [this.projects.length + 1,'desc']],
	                                    'aLengthMenu': [[-1], ['All']]});
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    var maxWidth = 30;
	    this.$('.angledTableText').each(function(i, e){
	        maxWidth = Math.max(maxWidth, $(e).width());
	    });
	    this.$('.angledTableHead').height(maxWidth +"px");
        return this.$el;
    }

});

ManageProductsViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.template = _.template($('#manage_products_row_template').html());
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }
    
});

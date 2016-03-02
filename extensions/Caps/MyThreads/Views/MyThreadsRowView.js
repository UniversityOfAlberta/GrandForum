MyThreadsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    template: _.template($('#my_threads_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    renderAuthors: function(){
	console.log(this.model.get('authors'));
        var views = Array();
        var that = this;
        _.each(this.model.get('authors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/&quot;/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#threadUsers'+this.model.id), model: views});
        csv.separator = ', ';
        csv.render();
    },

    events: {
    },

    render: function(){
        var isMine = {"isMine": false};
	if(this.model.get('author').id == me.id){
             isMine.isMine = true;
	}
        var mod = _.extend(this.model.toJSON(), isMine);
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});

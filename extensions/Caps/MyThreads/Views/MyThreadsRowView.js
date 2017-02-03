MyThreadsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    template: _.template($('#my_threads_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    renderAuthors: function(){
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
    
    deleteThread: function(){
        var doDelete = false;
        if(wgLang == "en"){
            doDelete = confirm("Are you sure you want to delete this thread?");
        }
        else if(wgLang == "fr"){
            doDelete = confirm("Voulez-vous vraiment supprimer ce fil?");
        }
        if(doDelete){
            this.model.destroy({success: $.proxy(function(model, response){
                this.$el.remove();
            }, this)});
        }
    },

    events: {
        "click .delete-icon": "deleteThread"
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

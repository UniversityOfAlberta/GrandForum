BibliographiesView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#bibliographies_template').html());
        main.set('title', 'Bibliographies');
    },
       
    events: {
        "click #add": "addBibliography"
    },
    
    render: function(){
        this.model.each(function(bib){
            var editors = new Array();
            _.each(bib.get('editors'), function(editor){
                editors.push("<a style='white-space: nowrap;' href=" + editor.url + ">" + editor.fullname + "</a>");
            });
            bib.set('editorsHTML', editors.join(", "));
        });
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#bibliographies").DataTable({
            "autoWidth": true
        });

        return this.$el;
    }

});

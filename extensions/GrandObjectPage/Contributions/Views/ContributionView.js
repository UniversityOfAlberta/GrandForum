ContributionView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Contribution does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#contribution_template').html());
    },
    
    events: {
        "click #editContribution": "editContribution",
        "click #deleteContribution": "deleteContribution"
    },
    
    editContribution: function(){
        document.location = document.location + '/edit';
    },
    
    deleteContribution: function(){
        if(confirm("Are you sure you want to delete this Contribution?")){
            this.model.destroy({
                success: function(model, response) {
                    model.set(response);
                    clearSuccess();
                    clearError();
                    addSuccess('The Contribution <i>' + response.name + '</i> was deleted sucessfully');
                    this.$("#deleteContribution").prop('disabled', true);
                    this.$("#editContribution").prop('disabled', true);
                    
                },
                error: function(model, response) {
                    clearSuccess();
                    clearError();
                    addError('The Contribution <i>' + response.name + '</i> was not deleted sucessfully');
                }
            });
        }
    },
    
    renderAuthors: function(){
        var views = Array();
        var that = this;
        _.each(this.model.get('authors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/"/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#contributionAuthors'), model: views});
        csv.separator = ', ';
        csv.render();
    },
    
    render: function(){
        main.set('title', this.model.get('name'));
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
        this.renderAuthors();
        return this.$el;
    }

});

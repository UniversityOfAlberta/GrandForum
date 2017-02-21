BibliographyEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Bibliography does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#bibliography_edit_template').html());
        
        this.allProducts = new Products();
        this.allProducts.fetch();
        this.listenTo(this.allProducts, "sync", this.renderProductsWidget);
    },
    
    saveBibliography: function(){
        this.$(".throbber").show();
        this.$("#saveBibliography").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Bibliography", true);
                }
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    events: {
        "click #saveBibliography": "saveBibliography",
        "click #cancel": "cancel"
    },
    
    renderProductsWidget: function(){
        
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProductsWidget();
        return this.$el;
    }

});

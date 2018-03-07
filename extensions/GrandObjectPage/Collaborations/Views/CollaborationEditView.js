CollaborationEditView = Backbone.View.extend({

    isDialog: false,
    timeout: null,
    productView: null,
    spinner: null,
    allPeople: null,
    bibtexDialog: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Collaboration does not exist");
            }, this)
        });
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        this.template = _.template($('#collaboration_edit_template').html());
        
        this.allProducts = new Products();
        this.allProducts.category = _.first(_.keys(productStructure.categories));
        this.allProducts.fetch();
        
        this.allPeople = new People();
        this.allPeople.fetch();
        
        this.listenTo(this.allProducts, "sync", this.renderProductsWidget);
        this.listenTo(this.allProducts, "reset", this.renderProductsWidget);
        this.listenTo(this.allPeople, "sync", this.renderEditorsWidget);
        $(document).mousedown(this.hidePreview);
    },
    
    saveCollaboration: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Organization name must not be empty', true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveCollaboration").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Collaboration", true);
                }
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    events: {
        "click #saveCollaboration": "saveCollaboration",
        "click #cancel": "cancel",
        "click .collab_check": "checkCollabItem",
    },

    checkCollabItem: function(data) {
        if ($(data.target).prop("tagName") != "INPUT") {
            var checkbox = $('input[type=checkbox]', data.currentTarget);
            var checked = checkbox.is(':checked');
            checkbox.prop('checked', !checked).change();
        }
    },
    
    
    render: function(){
        if(this.model.isNew()){
            main.set('title', 'New Collaboration');
        }
        else {
            main.set('title', 'Edit Collaboration');
        }
        this.$el.html(this.template(this.model.toJSON()));
        this.$('[name=sector]').chosen({width: "400px"});
        this.$('[name=country]').chosen({width: "400px"});

        return this.$el;
    }

});

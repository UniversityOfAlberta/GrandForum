ProductView = Backbone.View.extend({

    deleteDialog: null,

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Product does not exist");
            }.bind(this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#product_template').html());
    },
    
    events: {
        "click #editProduct": "editProduct",
        "click #deleteProduct": "deleteProduct",
        "click #undeleteProduct": "undeleteProduct"
    },
    
    editProduct: function(){
        document.location = document.location + '/edit';
    },
    
    deleteProduct: function(){
        if(this.model.get('deleted') != true){
	        this.deleteDialog.dialog('open');
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    undeleteProduct: function(){
        this.model.set('deleted', 0);
        this.$("#deleteProduct").prop("disabled", true);
        this.$("#undeleteProduct").prop("disabled", true);
        this.model.save(null, {
            success: function(model, response) {
                clearSuccess();
                clearError();
                addSuccess('The ' + response.category + ' <i>' + response.title + '</i> was un-deleted sucessfully');
                this.$("#deleteProduct").prop("disabled", false);
                this.$("#undeleteProduct").prop("disabled", false);
            }.bind(this),
            error: function(model, response) {
                clearSuccess();
                clearError();
                addError('The ' + response.category + ' <i>' + response.title + '</i> was not un-deleted sucessfully');
                this.$("#deleteProduct").prop("disabled", false);
                this.$("#undeleteProduct").prop("disabled", false);
            }.bind(this)
        });
    },
    
    renderAuthors: function(){
        var views = Array();
        var that = this;
        var lead = this.model.get('data')['lead'];
        _.each(this.model.get('authors'), function(author, index){
            var text = author.name.replace(/&quot;/g, '');
            if(lead != null && (lead.fullname == author.fullname || lead.id == author.id)){
                text += "*";
            }
            var link = new Link({id: author.id,
                                 text: text,
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#productAuthors'), model: views});
        csv.separator = '; ';
        csv.render();
    },
    
    renderContributors: function(){
        var views = Array();
        var that = this;
        _.each(this.model.get('contributors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/&quot;/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#productContributors'), model: views});
        csv.separator = '; ';
        csv.render();
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        this.renderAuthors();
        this.renderContributors();
        if(this.model.get('deleted') == true){
            this.$("#deleteProduct").prop('disabled', true);
            clearInfo();
            addInfo('This ' + this.model.get('category') + ' has been deleted, and will not show up anywhere else on the ' + siteName + '.  You may still edit the ' + this.model.get('category') + '.');
        }
        this.deleteDialog = this.$("#deleteDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            open: function(){
	                $("html").css("overflow", "hidden");
	                $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	                $("#deleteCheck", this.deleteDialog).prop("checked", false);
	                $("#deleteCheck", this.deleteDialog).change(function(e){
	                    var isChecked = $(e.currentTarget).is(":checked");
	                    if(isChecked){
	                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", false);
	                    }
	                    else{
	                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	                    }
	                }.bind(this));
	            }.bind(this),
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	            },
	            buttons: {
	                "Yes": function(){
	                    var model = this.model;
	                    if(model.get('deleted') != true){
	                        $("div.throbber", this.deleteDialog).show();
                            model.destroy({
                                success: function(model, response) {
                                    this.deleteDialog.dialog('close');
                                    $("div.throbber", this.deleteDialog).hide();
                                    if(response.deleted == true){
                                        model.set(response);
                                        clearSuccess();
                                        clearError();
                                        addSuccess('The ' + response.category + ' <i>' + response.title + '</i> was deleted sucessfully');
                                    }
                                    else{
                                        clearSuccess();
                                        clearError();
                                        addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                                    }
                                }.bind(this),
                                error: function(model, response) {
                                    clearSuccess();
                                    clearError();
                                    addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                                }
                            });
                        }
                        else{
                            this.deleteDialog.dialog('close');
                            clearAllMessages();
                            addError('This ' + model.get('category') + ' is already deleted');
                        }
	                }.bind(this),
	                "No": function(){
	                    this.deleteDialog.dialog('close');
	                }.bind(this)
	            }
	        });
        return this.$el;
    }

});

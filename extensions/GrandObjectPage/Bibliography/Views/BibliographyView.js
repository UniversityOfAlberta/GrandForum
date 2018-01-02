BibliographyView = Backbone.View.extend({
    mention: null,
    searchTerm: null,
    products: null,

    initialize: function(){
        this.mention = new Array();
        this.products = new Array();
        Backbone.Subviews.add(this);
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Bibliography does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#bibliography_template').html());
    },

    subviewCreators: {
        "listComments": function(){
            return new ThreadView({model: new Thread({id: this.model.get('thread_id')}), isComment: true, tinyMCEMention: this.mention});
        }
    },
    
    editBibliography: function(){
        document.location = this.model.get('url') + "/edit";
    },
    
    events: {
        "click #editBibliography": "editBibliography",
        "keyup #filterAuthors": "filterAuthors",
        "submit #formFilterAuthors": "filterAuthors",
        "click #submitFilterAuthor": "filterAuthors",
        "change #filterOperand": "filterAuthors"
    },

    filterAuthors: function() {
        var operand = this.$("#filterOperand").val();
        var searchTerms = unaccentChars(this.$("#filterAuthors").val()).split(",");
        var lis = this.$("#products li");

        _.each(this.products, function(prod, index){
            var authors = unaccentChars(_.pluck(prod.get('authors'), 'fullname').join(", "));
            var show = null;
            _.each(searchTerms, function(term, index) { // for each search term.

                if (operand == "AND") {
                    if (authors.indexOf(term) == -1) {
                        show = false;
                    } else if ((show == null) && (index == searchTerms.length - 1)) {
                        show = true
                    }
                } else if (operand == "OR") {
                    if (authors.indexOf(term) != -1) {
                        show = true;
                    } else if ((show == null) && (index == searchTerms.length - 1)) {
                        show = false
                    }
                } else { // NOT
                    if (authors.indexOf(term) != -1) {
                        show = false;
                    } else if ((show == null) && (index == searchTerms.length - 1)) {
                        show = true
                    }
                    if (term == "") {
                        show = true
                    }
                }
            });

            if (show) {
                $(lis.get(index)).slideDown();
            } else {
                 $(lis.get(index)).slideUp();
            }
            
        });

    },
    
    renderProducts: function(){
        var xhrs = new Array();
        var products = new Array();
        var citations = new Array();
        _.each(this.model.get('products'), function(prod){
            var product = new Product({id: prod.id});
            products.push(product);
            xhrs.push(product.fetch());
        });
        this.products = products;
        $.when.apply(null, xhrs).done($.proxy(function(){
            var xhrs2 = new Array();
            var tags = new Array();
            _.each(products, $.proxy(function(product){
                xhrs2.push(product.getCitation());
                this.mention.push({"name": product.get('title')});
                var listTags = product.get('tags');
                for (i = 0; i < listTags.length; i++) {
                    this.mention.push({"name": listTags[i]});
                }
            }, this));

            $.when.apply(null, xhrs2).done($.proxy(function(){
                _.each(products, $.proxy(function(product){
                    this.$('#products ol').append("<li>" + product.get('citation') + "<br />");
                    if (product.get('description'))
                    {
                        var id = product.get('id');
                        this.$('#products li').last().append("<p style='text-align:left;'><a id='abstract" + id + 
                                                      "' style='cursor:pointer;'>Show/Hide Abstract</a><span style='float:right;'>" + 
                                                      product.get('tags').join(", ") + "</span></p></li>");
                        this.$('#products li').last().append("<div id='desc" + id + "' style='display:none;'>" + 
                                                  product.get('description') + "</div></br>");
                        $("#abstract" + id).click(function() {
                            $("#desc" + id).slideToggle("slow");
                        });
                    } else {
                        this.$('#products li').last().append("<p><span style='float:right;'>" + 
                                                      product.get('tags').join(", ") + "</span></p></li>");
                    }
                }, this));
                $(".pdfnodisplay").remove();
                this.filterAuthors();
            }, this));
        }, this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProducts();
        return this.$el;
    }

});

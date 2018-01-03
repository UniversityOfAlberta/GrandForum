BibliographyView = Backbone.View.extend({
    mention: null,
    searchTerm: null,
    products: null,
    tags: null,

    initialize: function(){
        this.mention = new Array();
        this.products = new Array();
        this.tags = new Array();
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
        "click #filtersBtn": "showFilterOptions",
        "keyup #filterAuthors": "filterAuthors",
        "change #filterOperand": "filterAuthors",
        "keyup #filterTags": "filterTags",
        "change #filterTagOperand": "filterTags",
    },

    showFilterOptions: function() {
        if(this.$('#filters').css('display') == "none") {
            this.$("#filters").slideDown();
        } else {
            this.$("#filters").slideUp();
        }
    },

    filterAuthors: function() {
        var operand = this.$("#filterOperand").val();
        var searchTerms = unaccentChars(this.$("#filterAuthors").val()).split(",");
        var version = 'authors';
        this.filterOptions(searchTerms, version, operand);
    },

    filterTags: function() {
        var operand = this.$("#filterTagOperand").val();
        var searchTerms = unaccentChars(this.$("#filterTags").val()).split(",");
        var version = 'tags';
        this.filterOptions(searchTerms, version, operand);
    },

    filterOptions: function(searchTerms, version, operand) {
        console.log(this.tags);
        var lis = this.$("#products li");

        _.each(this.products, function(prod, index){
            if (version == "tags") {
                var target = unaccentChars(prod.get("tags").join(", "));
            } else if (version == "authors") {
                var target = unaccentChars(_.pluck(prod.get("authors"), 'fullname').join(", "));
            }
            var show = null;
            _.each(searchTerms, function(term, index) {

                if (operand == "AND") {
                    if (target.indexOf(term) == -1) {
                        show = false;
                    } else if ((show == null) && (index == searchTerms.length - 1)) {
                        show = true
                    }
                } else if (operand == "OR") {
                    if (target.indexOf(term) != -1) {
                        show = true;
                    } else if ((show == null) && (index == searchTerms.length - 1)) {
                        show = false
                    }
                } else { // NOT
                    if (target.indexOf(term) != -1) {
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
                    this.tags.push(listTags[i]);
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

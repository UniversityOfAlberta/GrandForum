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
        $(document).click(function(e) {
            if ((!$.contains($("#filters")[0], e.target)) 
                && (e.target != $("#filtersBtn")[0])
                && (!$.contains($("#filtersBtn")[0], e.target))) {
                $("#filters").slideUp();
            }
        });
    },

    subviewCreators: {
        "listComments": function(){
            return new ThreadView({model: new Thread({id: this.model.get('thread_id')}), isComment: true, tinyMCEMention: this.mention});
        }
    },
    
    editBibliography: function(){
        document.location = this.model.get('url') + "/edit";
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this bibliography?")) {
            this.model.destroy({success: function() {
                document.location = wgServer + wgScriptPath + "/index.php/Special:BibliographyPage#";
                _.defer(function() {
                    clearAllMessages();
                    addSuccess("Bibliography deleted")
                });
            }, error: function() {
                clearAllMessages();
                addError("Bibliography failed");
            }});
        }
    },
    
    events: {
        "click #editBibliography": "editBibliography",
        "click #filtersBtn": "showFilterOptions",
        "keyup #filterAuthors": "filter",
        "input #filterAuthors": "filter",
        "change #filterOperand": "filter",
        "change #filterSelectTags": "filter",
        "change #filterTagOperand": "filter",
        "keyup #search": "filter",
        "click #deleteBibliography": "delete",
        "click #exportBib": "exportBibliography",
    },

    showFilterOptions: function() {
        if(this.$('#filters').css('display') == "none") {
            this.$("#filters").slideDown();
        } else {
            this.$("#filters").slideUp();
        }
    },

    filter: function() {
        this.$("#products li").show();
        this.filterAuthors();
        this.filterTags();
        this.search();
    },

    filterAuthors: function() {
        var operand = this.$("#filterOperand").val();
        var searchTerms = unaccentChars(this.$("#filterAuthors").val()).split(",");
        var version = 'authors';
        this.filterOptions(searchTerms, version, operand);
    },

    filterTags: function() {
        var operand = this.$("#filterTagOperand").val();
        var searchTerms = this.$("#filterSelectTags").val();
        var version = 'tags';
        this.filterOptions(searchTerms, version, operand);
    },

    filterOptions: function(searchTerms, version, operand) {
        if ((searchTerms.length == 0) || (searchTerms == "")){
            return;
        }
        var lis = this.$("#products li");
        _.each(this.products, function(prod, index){
            if (version == "tags") {
                var target = unaccentChars(prod.get("tags").join(", "));
            } else if (version == "authors") {
                var target = unaccentChars(_.pluck(prod.get("authors"), 'fullname').join(", "));
            }
            var show = true;

            if ($(lis.get(index))[0].style.display == 'none') {
                show = false;
            } 

            if (show) {

                for (i = 0; i < searchTerms.length; ++i) {
                    term = searchTerms[i].toLowerCase();
                    if (operand == "AND") {
                        if (target.indexOf(term) == -1) { // if we didn't find one
                            show = false;
                            break; // onto the next product
                        } else if (i == searchTerms.length - 1) {
                            show = true;
                        }
                    } else if (operand == "OR") {
                        if (target.indexOf(term) != -1) { // found one
                            show = true;
                            break; // onto the next product
                        } else if (i == searchTerms.length - 1) { // didn't find one
                            show = false;
                        }
                    } else { // NOT
                        if (target.indexOf(term) != -1) { // found one
                            show = false;
                            break; // onto the next product
                        } else if (i == searchTerms.length - 1) {
                            show = true;
                        }
                        if (term == "") {
                            show = true;
                        }
                    }
                }   
            }

            if (show) {
                //$(lis.get(index)).slideDown();
                $(lis.get(index)).show();
            } else {
                //$(lis.get(index)).slideUp();
                $(lis.get(index)).hide();
            }
            
        });
    },

    search: function() {
        var searchTerm = this.$("#search").val();
        if (searchTerm == "") {
            return;
        }
        var lis = this.$("#products li");
        _.each(this.products, function(prod, index){
            var v = $(lis.get(index));
            if (v.css('display') != "none") {
                var pub = prod.get("citation").replace(/<\/?(.|\n)*?>/g, "");
                var tags = prod.get("tags").join(", ");
                pub = pub.replace(/&nbsp;/g, " ").toLowerCase() + tags;

                if (pub.indexOf(searchTerm.toLowerCase()) != -1) {
                    $(lis.get(index)).show();
                } else {
                    $(lis.get(index)).hide();
                }   
            }
        });

    },

    exportBibliography: function() {
        var lis = this.$("#products ol > li:visible");
        var prods = new Products(this.products);
        var xhrs = new Array();
        $.each(lis,function(index, value) {
            var prod = prods.get($(value).attr('product-id'));
            xhrs.push(prod.getBibTeX());
        });
        var outputBib = "";
        this.$('#bibExportThrobber').show();
        $.when.apply(null, xhrs).done($.proxy(function() {
            $.each(lis,function(index, value) {
                var prod = prods.get($(value).attr('product-id'));
                outputBib += prod.get('bibtex') + "\n\n";
            });
            if (outputBib != "") {
                this.openBibTexDialog(outputBib);
            }
            else {
                this.showNoBibsErrMsg();
            }
            this.$('#bibExportThrobber').hide();
        },this));
    },

    openBibTexDialog: function(text) {
        this.$("#bibtexDialog > div > textarea").val(text);
        var height = $(window).height() * 0.75;
        this.bibtexDialog = this.$("#bibtexDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            width: "800px",
            height: height,
            open: function(){
                $("html").css("overflow", "hidden");
            },
            beforeClose: function(){
                $("html").css("overflow", "auto");
            },
            buttons: {
                "Close": $.proxy(function(){
                    this.bibtexDialog.dialog('close');
                }, this)    
            }
        });
        this.$el.append(this.bibtexDialog.parent());
        this.bibtexDialog.dialog("open");
    },

    showNoBibsErrMsg: function() {
        alert("There are no publications to export.");
    },
    
    renderProducts: function(){
        spinner("loadPublicationsSpinner", 40, 75, 12, 10, '#888');
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
            this.tags = this.unique(this.tags);
            _.each(this.tags, $.proxy(function(tag) {
                var option = '<option value="' + tag + '">' + tag + '</option>';
                this.$('#filterSelectTags').append(option);
            }, this));
            this.$('#filterSelectTags').trigger("chosen:updated");

            $.when.apply(null, xhrs2).done($.proxy(function(){
                _.each(products, $.proxy(function(product){
                    this.$('#products ol').append("<li product-id='" + product.get('id') + "'>" + product.get('citation') + "<br />");
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
                this.$('#loadPublicationsSpinner').remove();
            }, this));
        }, this));
    },

    unique: function (array) {
        return $.grep(array, function(el, index) {
            return index === $.inArray(el, array);
        }).sort();
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProducts();
        this.$('#filterSelectTags').chosen({ placeholder_text_multiple: 'Select tags', width: "98%" });   
        return this.$el;
    }

});

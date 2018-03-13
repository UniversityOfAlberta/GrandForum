CollaborationView = Backbone.View.extend({
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
                this.$el.html("This Collaboration does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#collaboration_template').html());
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
    
    editCollaboration: function(){
        document.location = this.model.get('url') + "/edit";
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this collaboration?")) {
            this.model.destroy({success: function() {
                document.location = wgServer + wgScriptPath + "/index.php/Special:CollaborationPage#";
                _.defer(function() {
                    clearAllMessages();
                    addSuccess("Collaboration deleted")
                });
            }, error: function() {
                clearAllMessages();
                addError("Collaboration failed");
            }});
        }
    },
    
    events: {
        "click #editCollaboration": "editCollaboration",
        "click #deleteCollaboration": "delete",
        "click #exportBib": "exportCollaboration",
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

    
    // renderProducts: function(){
    //     spinner("loadPublicationsSpinner", 40, 75, 12, 10, '#888');
    //     var xhrs = new Array();
    //     var products = new Array();
    //     var citations = new Array();
    //     _.each(this.model.get('products'), function(prod){
    //         var product = new Product({id: prod.id});
    //         products.push(product);
    //         xhrs.push(product.fetch());
    //     });
    //     this.products = products;
    //     $.when.apply(null, xhrs).done($.proxy(function(){
    //         var xhrs2 = new Array();
    //         var tags = new Array();
    //         _.each(products, $.proxy(function(product){
    //             xhrs2.push(product.getCitation());
    //             this.mention.push({"name": product.get('title')});
    //             var listTags = product.get('tags');
    //             for (i = 0; i < listTags.length; i++) {
    //                 this.mention.push({"name": listTags[i]});
    //                 this.tags.push(listTags[i]);
    //             }
    //         }, this));
    //         this.tags = this.unique(this.tags);
    //         _.each(this.tags, $.proxy(function(tag) {
    //             var option = '<option value="' + tag + '">' + tag + '</option>';
    //             this.$('#filterSelectTags').append(option);
    //         }, this));
    //         this.$('#filterSelectTags').trigger("chosen:updated");

    //         $.when.apply(null, xhrs2).done($.proxy(function(){
    //             _.each(products, $.proxy(function(product){
    //                 this.$('#products ol').append("<li product-id='" + product.get('id') + "'>" + product.get('citation') + "<br />");
    //                 if (product.get('description'))
    //                 {
    //                     var id = product.get('id');
    //                     this.$('#products li').last().append("<p style='text-align:left;'><a id='abstract" + id + 
    //                                                   "' style='cursor:pointer;'>Show/Hide Abstract</a><span style='float:right;'>" + 
    //                                                   product.get('tags').join(", ") + "</span></p></li>");
    //                     this.$('#products li').last().append("<div id='desc" + id + "' style='display:none;'>" + 
    //                                               product.get('description') + "</div></br>");
    //                     $("#abstract" + id).click(function() {
    //                         $("#desc" + id).slideToggle("slow");
    //                     });
    //                 } else {
    //                     this.$('#products li').last().append("<p><span style='float:right;'>" + 
    //                                                   product.get('tags').join(", ") + "</span></p></li>");
    //                 }
    //             }, this));
    //             $(".pdfnodisplay").remove();
    //             this.filterAuthors(); 
    //             this.$('#loadPublicationsSpinner').remove();
    //         }, this));
    //     }, this));
    // },

    unique: function (array) {
        return $.grep(array, function(el, index) {
            return index === $.inArray(el, array);
        }).sort();
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        
        var formType = this.model.getType();
        if(this.model.isNew()){
            main.set('title', 'New ' + formType);
        }
        else {
            main.set('title', 'Edit ' + formType);
        }
        this.$el.html(this.template(_.extend({formType:formType}, this.model.toJSON())));
        //this.renderProducts();
        this.$('#filterSelectTags').chosen({ placeholder_text_multiple: 'Select tags', width: "98%" });   
        return this.$el;
    }

});

CollaborationsView = Backbone.View.extend({
    selectedTags: null,

    initialize: function(){
        this.selectedTags = new Array();
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "sync", this.renderProductsAndTags);
        this.template = _.template($('#collaborations_template').html());
        main.set('title', 'Collaborations');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addCollaboration",
        "click .delete-icon": "delete",
    },

    clearFilter: function() {
        document.getElementById("titleInput").value = "";
        document.getElementById("editorInput").value = "";
        document.getElementById("descInput").value = "";
        $("#tags-select").multipleSelect('uncheckAll');
        this.showAllRows();
    },

    showAllRows: function() {

        table = document.getElementById("collaborations");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this collaboration?")) {
            this.model.get(e.target.id).destroy({success: $.proxy(function(model, response) {
                console.log(model);
                if (response.id != null) {
                    this.model.add(model);
                    clearAllMessages();
                    addError("Collaboration deletion failed");
                } else {
                    clearAllMessages();
                    addSuccess("Collaboration deleted");
                }
            }, this), error: function() {
                clearAllMessages();
                addError("Collaboration deletion failed");
            }, wait: true});
        }
    },
    
    renderProducts: function(){
        var xhrs = new Array();
        var titles = new Array();
        var descriptions = new Array();
        var tags = new Array();
        $.when.apply(null, xhrs).done($.proxy(function(){
            this.model.each(function(collab){
                _.each(collab.get('products'), function(product){
                    if (!_.include(titles, product.title)) {
                        titles.push(product.title);    
                    }
                    if (!_.include(descriptions, product.description) && product.description != "") {
                        descriptions.push(product.description);
                    }
                });
                _.each(collab.get('tags'), function(listTags){
                    for (i = 0; i < listTags.length; i++) {
                        if (!_.include(tags, "<option>" + listTags[i] + "</option>")) {
                            tags.push("<option>" + listTags[i] + "</option>");
                        }
                    }
                });

            });
            main.set('tagsFilterHTML', tags.sort().join(" "));
        }, this));
    },

    filterTags: function(view) {
        
        var table, tr, td, i, j, show, tags, tag;

        table = document.getElementById("collaborations");
        tr = table.getElementsByTagName("tr");

        if (view.checked) {
            this.selectedTags.push(view.label);
        }
        else if (!view.checked) {
            this.selectedTags.splice(this.selectedTags.indexOf(view.label), 1);
        }

        if (this.selectedTags.length == 0)
        {
            this.showAllRows();
        }
        else
        {
            for (i = 1; i < tr.length; i++) {
                show = !document.getElementById("filterByTags").checked;
                td = tr[i].getElementsByTagName("td")[3];
                if (td) {
                    tags = td.innerHTML.replace(/<\/?[^>]+(>|$)/g, "").split(", ");
                    for (j = 0; j < tags.length; j++) {
                        tag = tags[j].replace(/^\s+|\s+$/gm, '').replace('//', '').toLowerCase();
                        if (!(tag === "")) {
                            if ($.inArray(tag, this.selectedTags) !== -1)
                            {
                                // console.log(tag);
                                show = true;
                            }
                        }
                    }
                }
            
                if (!show) {
                    tr[i].style.display = "none";
                }
            }
        }
    },
    
    render: function(){
        this.model.each(function(collab){
            var editors = new Array();
            _.each(collab.get('editors'), function(editor){
                editors.push("<a style='white-space: nowrap;' href=" + editor.url + ">" + editor.fullname + "</a>");
            });
            collab.set('editorsHTML', editors.join(", "));
            var tags = new Array();
            _.each(collab.get('tags'), function(listTags){
                for (i = 0; i < listTags.length; i++) {
                    if (!_.include(tags, listTags[i])) {
                        tags.push(listTags[i]);
                    }
                }
            });
            collab.set('tagsHTML', tags.sort().join(", "));
        });
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProducts();
        this.$("table#collaborations").DataTable({
            "autoWidth": true
        });
        this.$("#tags-select").multipleSelect({
            width: 140,
            filter: true,
            placeholder: "Select Keywords",
            onClick: $.proxy(function(view) {
                // console.log("beginning of on click: ", view.label, view.checked);
                // console.log("before filtering: ", this.selectedTags);
                this.filterTags(view);
                // console.log("after filtering: ", view.label, ": ", this.selectedTags);
            }, this),
            onUncheckAll: $.proxy(function() {
                this.showAllRows();
                this.selectedTags = new Array();
                // console.log("onUncheckAll cleared array: ", this.selectedTags);
            }, this),
            onCheckAll: $.proxy(function() {
                var tags = $("#tags-select").multipleSelect("getSelects");
                this.selectedTags = new Array();
                
                for (i = 0; i < tags.length; i++) {
                    this.filterTags({"label": tags[i], "checked": true});
                }
                // console.log("onCheckAll after filtering: ", this.selectedTags);
            }, this),
        });
        $("#filterByTags").click($.proxy(function() {
            if (document.getElementById("filterByTags").checked)
            {
                $("#tags-select").multipleSelect("enable");
                $(".placeholder").text('Select Keywords');
            }
            else {
                $("#tags-select").multipleSelect('uncheckAll');
                $("#tags-select").multipleSelect("disable");
                $(".placeholder").text('');
            }
        }, this));
        $("#clearFiltersButton").click($.proxy(function() {
            this.clearFilter();
        }, this));
        return this.$el;
    }

});

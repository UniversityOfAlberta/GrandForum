ClipboardListView = Backbone.View.extend({
    template: _.template($('#clipboardlist_template').html()),
    initialize: function () {
        this.model.bind('sync', this.render);//change to on
    },

    events: {
	"click #printMap": "printMap",
	"click #clearList": "clearList",
    },

    clearList: function(){
        this.model.set({
                "objs": [],
        });
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveEvent").prop('disabled', false);
                clearAllMessages();
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveEvent").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Event", true);
                }
            }.bind(this)
        });
    },

    printMap: function () {
        window.print();
    },

    addRows: function (cat,rows) {
        this.$('#listTable').hide();
        if (this.table != undefined) {
            this.table.destroy();
        }
        var fragment = document.createDocumentFragment();
        rows.forEach(function (p, i) {
            var row = new ClipboardListRowView({ model: p, parent: this });
            row.render();
            fragment.appendChild(row.el);
        }.bind(this));
        this.$("#sopRows"+cat).html(fragment);

        // Show the DataTable
        this.$('#listTable').show();
        this.$('.dataTables_scrollHead table').show();
        this.$('.DTFC_LeftHeadWrapper table').show();

    },


    addTable: function(category, notes, category_str){
	if(notes != "No notes"){
            var content = "<table id=\"listTable\" frame=\"box\" rules=\"all\" style=\"float: left;\"><thead style=\"background-color:#c7c7c7;\"><tr><th>"+category_str+"<br />Notes: "+notes+"</th></tr></thead><tbody id=\"sopRows"+category+"\"></tbody></table>";
	}
	else{
            var content = "<table id=\"listTable\" frame=\"box\" rules=\"all\" style=\"float: left;\"><thead style=\"background-color:#c7c7c7;\"><tr><th>"+category_str+"</th></tr></thead><tbody id=\"sopRows"+category+"\"></tbody></table>";

	}
	this.$("#tables").append(content)
    },

    render: function () {
        //this.$el.empty();
        main.set('title', 'Clipboard');
        var data = this.model.get('objs');
        this.$el.html(this.template({
            output: data,
        }));
	
        //creating an array from the model TODO:figure out why this isnt working
	var newModel = [];
	var keys = _.keys(this.model.get('objs'));
        if(keys.length == 0){
            this.$('#empty').show();
        }
	var newJSON = {};
	for(var i = 0; i < keys.length; i++){
	    var object = data[keys[i]];
            var category = object.Category;
	    var category_key = category.replace(" ", "");
	    if(newJSON.hasOwnProperty(category_key)){
		newJSON[category_key].push(object);
	    }
	    else{
		newJSON[category_key] = [];
		newJSON[category_key].push(object);
	    }
	    newModel.push(object);
	}
	for (var key in newJSON) {
	    notes = newJSON[key][0]["Notes"];
	    category_str = newJSON[key][0]["Category"];
	    this.addTable(key, notes, category_str);
	    this.addRows(key, newJSON[key]);

	}

        var title = $("#pageTitle").clone();
        $(title).attr('id', 'copiedTitle');
        this.$el.prepend(title);
        return this.$el;
    }

});


CommunityRowView = Backbone.View.extend({
    tagName: 'tr',
    parent: null,
    category: null,
    clipboard: null,
    newModel: [],
    clipboardids: [],
    bkmarked: false,
    note: "",
    template: _.template($('#community_row_template').html()),
    
    initialize: function(options){
	    this.parent = options.parent;
	    this.category = options.category;
	    this.note = options.note;
            this.listenTo(this.model, "sync", this.render);
    },

    events: {
	"click #bkmark" : "clipboard_add",
    },

    checkBookmarked:function(){
        if(this.newModel.length == 0){
            var keys = this.clipboard.keys();
            for(var i = 0; i < keys.length; i++){
                var obj = this.clipboard.toJSON();
                var object = obj[keys[i]];
                this.newModel.push(object);
		this.clipboardids.push(object.id);
            }
        }
	return this.clipboardids.includes(this.model.toJSON().id);
    },

    clipboard_add: function(){
	if(this.newModel.length == 0){
            var keys = this.clipboard.keys();
            for(var i = 0; i < keys.length; i++){
	        var obj = this.clipboard.toJSON();
                var object = obj[keys[i]];
                this.newModel.push(object);
            }
	}
	var newObj = {};
	var clicked = this.model.toJSON();
	//check if this is in the bookmarked delete it if it is.. otherwise add it to list
	if(this.checkBookmarked()){
            this.newModel = this.newModel.filter(function(el) { return el.id != clicked["id"]; }); 
	    this.clipboardids = this.clipboardids.filter(function(el) { return el != clicked["id"]; });
            var bkmarkid = "#bkmarked_"+clicked["id"];
            $(bkmarkid).attr('src', wgServer+wgScriptPath+"/skins/bookmark-plus.svg");
	    $('#dialog_title_span').text("Removed from Clipboard");
	    $('.alert-msg').fadeIn();
            setTimeout(function () { $('.alert-msg').fadeOut()}, 1000);	
	}
	
	else{
	//create new model 
	    this.clipboardids.push(clicked["id"]);
	    var bkmarkid = "#bkmarked_"+clicked["id"];
            $(bkmarkid).attr('src', wgServer+wgScriptPath+"/skins/bookmark-star-fill.svg");
	    $('#dialog_title_span').text("Added to Clipboard");
            $('.alert-msg').fadeIn();
            setTimeout(function () { $('.alert-msg').fadeOut()}, 1000);

	    newObj["id"] = clicked["id"];
            newObj["PublicName"] = clicked["PublicName"];
            newObj["AgencyDescription"] = clicked["AgencyDescription"];
            newObj["Eligibility"] = clicked["Eligibility"];
            newObj["ParentAgency"] = clicked["ParentAgency"];
            newObj["PhysicalAddress1"] = clicked["PhysicalAddress1"];
            newObj["EmailAddressMain"] = clicked["EmailAddressMain"];
	    newObj["Category"] = this.category;
	    newObj["Notes"] = this.note;
            if(clicked["PhoneNumbers"].length != 0){
                newObj["Phone"] = clicked["PhoneNumbers"][0].Phone;
            }
	    else{
		newObj["Phone"] = "";
	    }
            newObj["WebsiteAddress"] = clicked["WebsiteAddress"];
	    this.newModel.push(newObj);
	}

	//save it to by calling API?	
	this.clipboard.set({
                "clipboard": this.newModel,
        });
        var isNew = this.model.isNew();
        this.clipboard.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveEvent").prop('disabled', false);
                if(isNew){
                    this.parent_location.reload();
                }
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

    render: function(){
        var self = this;
        this.clipboard = new PersonClipboard();
        this.clipboard.fetch({
            success: function () {
		if(self.checkBookmarked()){
		    var bkmarkid = "#bkmarked_"+self.model.toJSON().id;
		    $(bkmarkid).attr('src', wgServer+wgScriptPath+"/skins/bookmark-star-fill.svg");
		}
            }
        });
        var i = this.model.toJSON();
        this.$el.html(this.template({
            output: i,
            bookmarked: this.bkmarked,
        }));
        return this.$el;
    }   

});

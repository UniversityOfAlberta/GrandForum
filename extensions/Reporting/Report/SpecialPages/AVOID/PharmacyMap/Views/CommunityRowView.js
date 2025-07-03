CommunityRowView = Backbone.View.extend({
    tagName: 'tr',
    parent: null,
    category: null,
    clipboard: null,
    newModel: null,
    clipboardids: [],
    bkmarked: false,
    note: "",
    template: _.template($('#community_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.category = options.category;
        this.note = options.note;
        this.clipboard = options.clipboard;
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
        "click #bkmark" : "clipboard_add",
    },

    checkBookmarked:function(){
        return this.clipboardids.includes(this.model.toJSON().id);
    },

    clipboard_add: function(){
        var newObj = {};
        var clicked = this.model.toJSON();
        //check if this is in the bookmarked delete it if it is.. otherwise add it to list
        var self = this;
        self.newModel = [];
        self.clipboardids = [];
        var cliparray = self.clipboard.get("objs");
        for(var i = 0; i < cliparray.length; i++){
            var object = cliparray[i];
            self.newModel.push(object);
            self.clipboardids.push(object.id);
        }
        if(self.checkBookmarked()){
            self.newModel = self.newModel.filter(function(el) { return el.id != clicked["id"]; }); 
            self.clipboardids = self.clipboardids.filter(function(el) { return el != clicked["id"]; });
            var bkmarkid = "#bkmarked_"+clicked["id"];
            $(bkmarkid).attr('src', wgServer+wgScriptPath+"/skins/bookmark-plus.svg");
            $('#dialog_title_span').text("Removed from Clipboard");
            $('.alert-msg').fadeIn();
            setTimeout(function () { $('.alert-msg').fadeOut()
            $(bkmarkid).attr('src', wgServer+wgScriptPath+"/skins/bookmark-plus.svg");
                }, 1000);
            self.render();
        }
        else{
            //create new model 
            self.clipboardids.push(clicked["id"]);
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
            newObj["Category"] = self.category;
            newObj["Notes"] = self.note;
            if(clicked["PhoneNumbers"].length != 0){
                newObj["Phone"] = clicked["PhoneNumbers"][0].Phone;
            }
            else{
                newObj["Phone"] = "";
            }
            newObj["WebsiteAddress"] = clicked["WebsiteAddress"];
            self.newModel.push(newObj);
        }

        //save it to by calling API?
        self.clipboard.set({
            "objs": self.newModel,
        });
        var isNew = self.model.isNew();
        self.clipboard.save(null, {
            success: function(){
                self.$(".throbber").hide();
                self.$("#saveEvent").prop('disabled', false);
                if(isNew){
                    self.parent_location.reload();
                }
                clearAllMessages();
            }.bind(self),
            error: function(o, e){
                self.$(".throbber").hide();
                self.$("#saveEvent").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Clipboard", true);
                }
            }.bind(self)
        });

    },

    render: function(){
        if(this.newModel == null){
            this.newModel = [];
            if(this.clipboard != null){
                _.each(this.clipboard.get('objs'), function(obj, i){
                    this.newModel.push(obj);
                    this.clipboardids.push(obj.id);
                }.bind(this));
            }
        }
        var self = this;
        var i = this.model.toJSON();
        this.$el.html(this.template({
            output: i,
            bookmarked: this.checkBookmarked(),
        }));
        return this.$el;
    }   

});

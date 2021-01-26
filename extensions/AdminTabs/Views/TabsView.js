TabsView = Backbone.View.extend({

    template: _.template($("#tabs_template").html()),
    currentRoles: null,

    initialize: function(options){
        var self = this;
        me.getRoles();
        me.roles.ready().then(function(){
            this.currentRoles = me.roles.getCurrent();
            me.roles.ready().then(function(){
                Backbone.Subviews.add(this);
                var intervalId = setInterval(function(){ //Intervals are set to check if the tab is visible to render all items
                    if($('#tabs-1').is(':visible') && self.subviews.studentImport != undefined){
                        self.subviews.studentImport.render();
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                 }, 100);
                var intervalId1 = setInterval(function(){
                    if($('#tabs-2').is(':visible') && self.subviews.reviewerImport != undefined){
                        self.subviews.reviewerImport.render();
                        clearInterval(intervalId1);
                        intervalId1 = null;
                    }
                 }, 100);
                var intervalId2 = setInterval(function(){
                    if($('#tabs-3').is(':visible') && self.subviews.editBio != undefined){
                        self.subviews.editBio.render();
                        clearInterval(intervalId2);
                        intervalId2 = null;
                    }
                 }, 100);
                var intervalId3 = setInterval(function(){
                    if($('#tabs-4').is(':visible') && self.subviews.gsmsOutcomeImport != undefined){
                        self.subviews.gsmsOutcomeImport.render();
                        clearInterval(intervalId3);
                        intervalId3 = null;
                    }
                 }, 100);
                 var intervalId4 = setInterval(function(){
                    if($('#tabs-5').is(':visible') && self.subviews.gsmsNotesImport != undefined){
                        self.subviews.gsmsNotesImport.render();
                        clearInterval(intervalId4);
                        intervalId4 = null;
                    }
                 }, 100);
                 this.render();
            }.bind(this))
        }.bind(this));
    },

    subviewCreators: {
        "studentImport" : function(){
             return new StudentImportView({parent: this, model: new AdminTabsModel()});
        },
        "reviewerImport" : function(){
             return new ReviewerImportView({parent: this, model: new AdminTabsModel()});
        },
        "editBio" : function(){
             return new EditBioView({parent: this, model: new AdminTabsModel()});
        },
        "gsmsOutcomeImport" : function(){
             return new GsmsOutcomeImportView({parent: this, model: new AdminTabsModel()});
        },
        "gsmsNotesImport" : function(){
             return new GsmsNotesImportView({parent: this, model: new AdminTabsModel()});
        },
    },


    events: {
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        $( "#tabs" ).tabs();
        return this.$el;
    }

});

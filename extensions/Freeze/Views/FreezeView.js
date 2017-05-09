FreezeView = Backbone.View.extend({

    projects: null,
    toDelete: null,

    initialize: function(){
        this.projects = new Projects();
        this.toDelete = new Freezes();
        
        $.when(this.model.fetch()).then($.proxy(function(){
            return $.when(this.projects.fetch());
        }, this)).then($.proxy(function(){
            this.render();
        }, this));
        
        this.template = _.template($("#freeze_template").html());
    },
    
    checkAll: function(e){
        var element = e.currentTarget;
        var feature = $(element).attr('data-feature');
        this.$("input[data-feature='" + feature + "']:not(:checked)").prop("checked", true).trigger("change");
    },
    
    uncheckAll: function(e){
        var element = e.currentTarget;
        var feature = $(element).attr('data-feature');
        this.$("input[data-feature='" + feature + "']:checked").prop("checked", false).trigger("change");
    },
    
    update: function(e){
        var element = e.currentTarget;
        var id = $(element).attr('data-id');
        var projectId = $(element).attr('data-projectid');
        var feature = $(element).attr('data-feature');
        
        var freeze = this.model.findWhere({projectId: projectId, feature: feature});
        var freezeDel = this.toDelete.findWhere({projectId: projectId, feature: feature});
        
        if(!$(element).is(":checked")){
            // Uncheck
            this.toDelete.add(freeze);
            this.model.remove(freeze);
        }
        else{
            // Check
            freeze = new Freeze({projectId: projectId, feature: feature});
            this.toDelete.remove(freezeDel);
            this.model.add(freeze);
        }
    },
    
    save: function(){
        var xhrs = new Array();
        this.$(".throbber").show();
        this.$("#save").prop("disabled", true);
        this.model.each(function(freeze){
            if(freeze.isNew()){
                _.defer(function(){ xhrs.push(freeze.save()) });
            }
        });
        this.toDelete.each(function(freeze){
            if(!freeze.isNew()){
                _.defer(function(){ xhrs.push(freeze.destroy()) });
            }
        });
        _.defer($.proxy(function(){
            $.when.apply(null, xhrs).then($.proxy(function(){
                this.$(".throbber").hide();
                this.$("#save").prop("disabled", false);
            }, this));
        }, this));
    },
    
    events: {
        "click .check": "checkAll",
        "click .uncheck": "uncheckAll",
        "change input[type=checkbox]": "update",
        "click #save": "save"
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

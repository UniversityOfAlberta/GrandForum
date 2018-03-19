CollaborationEditView = Backbone.View.extend({

    isDialog: false,
    timeout: null,
    productView: null,
    spinner: null,
    projects: null,
    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    parent: null,

    initialize: function(){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:projects", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        this.template = _.template($('#collaboration_edit_template').html());

        this.otherPopupTemplate = _.template($('#manage_products_other_popup_template').html());
        this.projectsPopupTemplate = _.template($('#manage_products_projects_popup_template').html());
        
        this.allProjects = new Projects();
        this.allProjects.fetch();
        me.getProjects();
        me.projects.ready().then($.proxy(function(){
            this.projects = me.projects.getCurrent();
            this.allProjects.ready().then($.proxy(function(){
                var other = new Project({id: "-1", name: "Other"});
                other.id = "-1";
                this.otherProjects = new Projects(this.allProjects.getCurrent().where({status: 'Active'}));
                this.otherProjects.add(other);
                this.oldProjects = this.allProjects.getOld();
                this.otherProjects.remove(this.projects.models);
                this.oldProjects.remove(this.projects.models);
                if(!this.model.isNew() && !this.isDialog){
                    this.model.fetch();
                }
                else{
                    _.defer(this.render);
                }
            }, this));
        }, this));
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                this.model.trigger("change:projects");
            }
        }, this));
    },
    
    saveCollaboration: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Organization name must not be empty', true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveCollaboration").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Collaboration", true);
                }
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    events: {
        "click #saveCollaboration": "saveCollaboration",
        "click #cancel": "cancel",
        "click .collab_check": "checkCollabItem",
        "change input[name=fund]": "toggleFunding",
        "click div.showOther": "showOther",
        "click div.showSubprojects": "showSubprojects",
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch",
        "change div#productProjects input[type=checkbox]": "toggleSelect"
    },

    checkCollabItem: function(data) {
        if ($(data.target).prop("tagName") != "INPUT") {
            var checkbox = $('input[type=checkbox]', data.currentTarget);
            var checked = checkbox.is(':checked');
            checkbox.prop('checked', !checked).change();
        }
    },
    
    toggleFunding: function(data) {
        var funded = this.$('input:radio[name=fund]:checked').val();
        var fundAmtDiv = this.$('#fundingAmount');
        this.model.attributes['funding'] = $('input[name=funding]').val();
        if (funded == "yes") {
            fundAmtDiv.slideDown();
        } else {
            this.model.attributes['funding'] = 0;
            fundAmtDiv.slideUp();
        }
    },

    select: function(projectId){
        var projects = this.model.get('projects');
        if(_.where(projects, {id: projectId}).length == 0){
            projects.push({id: projectId});
        }
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change:projects");
        }
    },
    
    unselect: function(projectId){
        var project = _.findWhere(this.parent.projects.models
                                      .concat(this.parent.otherProjects.models)
                                      .concat(this.parent.oldProjects.models), {id: projectId});
        var projects = this.model.get('projects');

        // Unselect all subprojects as well
        if(project != undefined){
            _.each(project.get('subprojects'), $.proxy(function(sub){
                var index = _.indexOf(projects, _.findWhere(projects, {id: sub.id}));
                if(index != -1){
                    projects.splice(index, 1);
                    this.$("input[data-project=" + sub.id + "]").prop('checked', false);
                }
            }, this));
        }
        projects.splice(_.indexOf(projects, _.findWhere(projects, {id: projectId})), 1);
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change:projects");
        }
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        if(target.is(":checked")){
            // 'Check' Project
            this.select(projectId);
            if(target.attr('name') == "project"){
                //this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideDown();
            }
        }
        else{
            // 'Uncheck' Project
            this.unselect(projectId);
            if(target.attr('name') == "project"){
                // Do nothing
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideUp();
            }
        }
    },

    filterSearch: function(e){
        var target = $(e.currentTarget);
        var value = target.val();
        var block = target.parent();
        var options = $("div.popupMainProject", block);
        options.each(function(i, el){
            var text = $(el).text();
            if(unaccentChars(text).indexOf(unaccentChars(value)) == -1){
                $(el).slideUp(150);
            }
            else{
                $(el).slideDown(150);
            }
        });
    },
    
    showOther: function(e){
        this.$("div.otherPopup").html(this.otherPopupTemplate(this.model.toJSON()));
        var lastHeight = this.$el.prop("scrollHeight")
        var interval = setInterval($.proxy(function(){
            if(this.$el.prop("scrollHeight") > lastHeight){
                this.$el.scrollTop(this.$el.scrollTop() + Math.abs(lastHeight - this.$el.prop("scrollHeight")));
                lastHeight = this.$el.prop("scrollHeight");
            }
        }, this), 16);
        this.$("div.otherPopup").slideDown($.proxy(function(){
            clearInterval(interval);
            if(this.$el.prop("scrollHeight") > lastHeight){
                this.$el.scrollTop(this.$el.scrollTop() + Math.abs(lastHeight - this.$el.prop("scrollHeight")));
                lastHeight = this.$el.prop("scrollHeight");
            }
        }, this));
    },
    
    showSubprojects: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        var project = _.findWhere(this.parent.projects.models, {id: projectId});
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").html(this.projectsPopupTemplate(_.extend(project.toJSON(), {projects: this.model.get('projects')})));
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
    },

    render: function(){
        var formType = this.model.getType();
        if(this.model.isNew()){
            main.set('title', 'New ' + formType);
        }
        else {
            main.set('title', 'Edit ' + formType);
        }
        this.$el.html(this.template(_.extend({formType:formType}, this.model.toJSON())));
        this.$('[name=sector]').chosen({width: "400px"});
        this.$('[name=country]').chosen({width: "400px"});

        return this.$el;
    },
});

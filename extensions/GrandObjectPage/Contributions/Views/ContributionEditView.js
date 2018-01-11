ContributionEditView = Backbone.View.extend({

    projects: null,
    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    parent: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:projects", this.render);
        this.listenTo(this.model, "change:total", this.render);
        this.listenTo(this.model, "add:partners", this.render);
        this.listenTo(this.model, "delete:partners", this.render);
        this.listenTo(this.model, "change:partners", this.renderPartners);
        this.listenTo(this.model, "change:name", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('name'));
            }
        });
        this.template = _.template($('#contribution_edit_template').html());
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
                if(!this.model.isNew()){
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
    
    events: {
        "click #saveContribution": "saveContribution",
        "click #cancel": "cancel",
        "click div.showOther": "showOther",
        "click div.showSubprojects": "showSubprojects",
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch",
        "change div#contributionProjects input[type=checkbox]": "toggleSelect",
        "click button#addPartner": "addPartner",
        "click button.deletePartner": "deletePartner"
    },
    
    deletePartner: function(e){
        var el = $(e.target);
        var id = el.attr('data-id');
        var partners = this.model.get('partners');
        partners.splice(id, 1);
        this.model.set('partners', _.clone(partners));
        this.model.trigger('delete:partners');
    },
    
    addPartner: function(){
        this.model.addPartner();
    },
    
    validate: function(){
        if(this.model.get('name').trim() == ""){
            return "The Contribution must have a title";
        }
        return "";
    },
    
    saveContribution: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveContribution").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveContribution").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#saveContribution").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Contribution", true);
                }
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        var left = _.pluck(this.model.get('authors'), 'fullname');
        var right = _.difference(this.allPeople.pluck('fullName'), left);
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });
        var html = HTML.Switcheroo(this, 'authors.fullname', {name: 'author',
                                                          'left': left,
                                                          'right': right,
                                                          'objs': objs
                                                          });
        this.$("#contributionAuthors").html(html);
        createSwitcheroos();
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            var spin = spinner("contributionAuthors", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                }
            }, this);
        }
    },
    
    renderPartners: function(){
        this.$("#saveContribution").prop('disabled', false);
        _.each(this.model.get('partners'), $.proxy(function(partner, i){
            var type = partner.type;
            var subtype = partner.subtype;
            if(type == 'In-Kind'){
                this.$("#partner" + i + " #inkind").show();
                this.$("#partner" + i + " #cash").hide();
                this.$("#partner" + i + " #subtype").show();
            }
            else if(type == 'Cash and In-Kind'){
                this.$("#partner" + i + " #inkind").show();
                this.$("#partner" + i + " #cash").show();
                this.$("#partner" + i + " #subtype").show();
            }
            else{
                this.$("#partner" + i + " #inkind").hide();
                this.$("#partner" + i + " #cash").show();
                this.$("#partner" + i + " #subtype").hide();
            }
            if(subtype == "Other"){
                this.$("#partner" + i + " #other_subtype").show();
            }
            else{
                this.$("#partner" + i + " #other_subtype").hide();
            }
            
            // Warnings
            this.$("#warning" + i).empty();
            var reg = /^\d*$/;
            if(partner.name.trim() == ''){
                this.$("#warning" + i).append("This partner is missing a name<br />");
            }
            if(partner.type.trim() == ''){
                this.$("#warning" + i).append("Missing contribution type<br />");
            }
            if(this.$("#partner" + i + " #subtype").is(":visible") && partner.subtype.trim() == ''){
                this.$("#warning" + i).append("Missing contribution sub-type<br />");
            }
            if(this.$("#partner" + i + " #other_subtype").is(":visible") && partner.other_subtype.trim() == ''){
                this.$("#warning" + i).append("Missing contribution sub-type<br />");
            }
            if(this.$("#partner" + i + " #cash").is(":visible") && !reg.test(partner.cash)){
                this.$("#warning" + i).append("Cash is not an integer<br />");
            }
            if(this.$("#partner" + i + " #inkind").is(":visible") && !reg.test(partner.inkind)){
                this.$("#warning" + i).append("In-Kind is not an integer<br />");
            }
            
            if(this.$("#warning" + i).text() != ""){
                this.$("#warning" + i).show();
                this.$("#saveContribution").prop('disabled', true);
            }
            else{
                this.$("#warning" + i).hide();
            }
        }, this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderPartners();
        return this.$el;
    }

});

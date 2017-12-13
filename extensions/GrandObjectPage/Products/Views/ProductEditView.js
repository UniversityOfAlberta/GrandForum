ProductEditView = Backbone.View.extend({

    isDialog: false,
    projects: null,
    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    parent: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:projects", this.render);
        this.listenTo(this.model, "change:category", this.render);
        this.listenTo(this.model, "change:type", this.render);
        this.listenTo(this.model, "change:access", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#product_edit_template').html());
        this.otherPopupTemplate = _.template($('#manage_products_other_popup_template').html());
        this.projectsPopupTemplate = _.template($('#manage_products_projects_popup_template').html());
        
        this.allProjects = new Projects();
        this.allProjects.fetch();
        me.getProjects();
        me.projects.ready().then($.proxy(function(){
            this.projects = me.projects.getCurrent();
            this.allProjects.ready().then($.proxy(function(){
                this.otherProjects = this.allProjects.getCurrent();
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
        "click #saveProduct": "saveProduct",
        "click #cancel": "cancel",
        "click div.showOther": "showOther",
        "click div.showSubprojects": "showSubprojects",
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch",
        "change div#productProjects input[type=checkbox]": "toggleSelect"
    },
    
    validate: function(){
        if(this.model.get('title').trim() == ""){
            return "The Product must have a title";
        }
        else if(this.model.get('category').trim() == ""){
            return "The Product must have a category";
        }
        else if(this.model.get('type').trim() == ""){
            return "The Product must have a type";
        }
        return "";
    },
    
    saveProduct: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveProduct").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                addError("There was a problem saving the Product", true);
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        //var left = _.pluck(this.model.get('authors'), 'fullname');
        //var right = _.difference(this.allPeople.pluck('fullName'), left);
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });
        /*
        var html = HTML.Switcheroo(this, 'authors.fullname', {name: 'author',
                                                          'left': left,
                                                          'right': right,
                                                          'objs': objs
                                                          });
        this.$("#productAuthors").html(html);
        createSwitcheroos();
        */
        
        // Testing Tagit
        var delimiter = ';';
        var html = HTML.TagIt(this, 'authors.fullname', {
            values: _.pluck(this.model.get('authors'), 'fullname'),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter authors here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                availableTags: this.allPeople.pluck('fullName'),
                tagSource: function(search, showChoices) {
                    if(search.term.length < 2){ showChoices(); return; }
                    var filter = search.term.toLowerCase();
                    var choices = $.grep(this.options.availableTags, function(element) {
                        return (element.toLowerCase().match(filter) !== null);
                    });
                    showChoices(this._subtractArray(choices, this.assignedTags()));
                }
            }
        });
        this.$("#productAuthors").html(html);
        this.$("#productAuthors .tagit").sortable({
            stop: function(event,ui) {
                $('input[name=authors_fullname]').val(
                    $(".tagit-label",$(this))
                        .clone()
                        .text(function(index,text){ return (index == 0) ? text : delimiter + text; })
                        .text()
                ).change();
            }
        });
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            var spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                }
            }, this);
        }
    },
    
    renderJournalsAutocomplete: function(){
        if(this.$("input[name=data_published_in]").length > 0){
            var autoComplete = {
                source: $.proxy(function(request, response){
                    var journals = new Journals();
                    journals.search = request.term;
                    journals.fetch({success: function(collection){
                        var data = _.map(collection.toJSON(), function(journal){
                            return {id: journal.id, 
                                    label: journal.title + " " + journal.year + " (" + journal.description + ")", 
                                    value: journal.title,
                                    journal: journal.title,
                                    impact_factor: journal.impact_factor,
                                    category_ranking: journal.category_ranking,
                                    eigen_factor: journal.eigenfactor,
                                    issn: journal.issn
                            };
                        });
                        response(data);
                    }});
                }, this),
                
                minLength: 2,
                select: $.proxy(function(event, ui){
                    _.defer($.proxy(function(){
                        this.$("input[name=data_published_in]").val(ui.item.journal).change();
                        this.$("input[name=data_impact_factor]").val(ui.item.impact_factor).change();
                        this.$("input[name=data_category_ranking]").val(ui.item.category_ranking).change();
                        this.$("input[name=data_eigen_factor]").val(ui.item.eigen_factor).change();
                        this.$("input[name=data_issn]").val(ui.item.issn).change();
                    }, this));
                }, this)
            };
            
            this.$("input[name=data_issn]").autocomplete(autoComplete);
            this.$("input[name=data_published_in]").autocomplete(autoComplete);
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderJournalsAutocomplete();
        
        if(productStructure.categories[this.model.get('category')] != undefined &&
           (productStructure.categories[this.model.get('category')].types[this.model.get('type')] != undefined &&
            _.size(productStructure.categories[this.model.get('category')].types[this.model.get('type')].titles) > 0) ||
            (_.size(_.first(_.values(productStructure.categories[this.model.get('category')].types)).titles) > 0)){
            this.$("select[name=title]").combobox();
        }
        return this.$el;
    }

});

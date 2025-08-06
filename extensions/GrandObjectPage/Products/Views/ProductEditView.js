ProductEditView = Backbone.View.extend({

    isDialog: false,
    projectWarning: null,
    doiWarning: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:category", this.render);
        this.listenTo(this.model, "change:type", this.render);
        this.listenTo(this.model, "change:projects", this.updateProjectWarning);
        this.listenTo(this.model, "change:data", this.updateDOIWarning);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#product_edit_template').html());
        
        var tagsGet = $.get(wgServer + wgScriptPath + '/index.php/index.php?action=api.product/tags');
        tagsGet.then(function(availableTags){
            this.availableTags = availableTags;
            if(!this.model.isNew() && !this.isDialog){
                this.model.fetch({silent: true});
            }
            else{
                _.defer(this.render);
            }
        }.bind(this));
    },
    
    events: {
        "click #saveProduct": "saveProduct",
        "click #cancel": "cancel",
        "click #btnViewAvailableTags": "toggleTagsList"
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
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Product", true);
                }
            }.bind(this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },

    toggleTagsList: function() {
        var tagsDiv = $('#availableTagsDiv');
        if (tagsDiv.css('display') == 'none') {
            tagsDiv.slideDown(300);
            $('#btnViewAvailableTags').text('Hide Tags');
        } else {
            tagsDiv.slideUp(300);
            $('#btnViewAvailableTags').text('View Tags');
        }
    },
    
    renderAuthorsWidget: function(){
        var objs = {};
        var availableTags = {};
        this.allPeople.each(function(p){
            var fullname = p.get('fullName');
            if(p.get('email') != ""){
                fullname += " (" + p.get('email').split('@')[0] + ")";
            }
            objs[fullname] = {id: p.get('id'),
                              name: p.get('name'),
                              fullname: fullname};
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: fullname};
            availableTags[fullname] = fullname;
        });
        availableTags = _.values(availableTags);
        var delimiter = ';';
        var tagLimit = 1000;
        var placeholderText = (tagLimit == 1) ? 'Enter ' + this.model.getAuthorsLabel().toLowerCase() + ' here...'
                                              : 'Enter ' + this.model.getAuthorsLabel().pluralize().toLowerCase() + ' here...';
        var html = HTML.TagIt(this, 'authors.fullname', {
            values: _.pluck(this.model.get('authors'), 'fullname'),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: placeholderText,
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                tagLimit: tagLimit,
                availableTags: availableTags,
                afterTagAdded: function(event, ui){
                    var authors = this.model.get('authors');
                    var index = $("li.tagit-choice", event.target).length-1;
                    var author = authors[index];
                    var lead = this.model.get('data')['lead'];

                    // Lead Author
                    if(lead != null && (lead.fullname == author.fullname || (lead.id == author.id && author.id != undefined))){
                        $(".tagit-label", ui.tag).after("<span>*</span>");
                    }
                    
                    // UofA Author
                    if(objs[ui.tagLabel] != undefined){
                        ui.tag[0].style.setProperty('background', hyperlinkColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
                        if(ui.tag.children("span").length > 1){
                            ui.tag.children("span")[1].style.setProperty("color", "white", 'important');
                        }
                    }
                }.bind(this),
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
        if(tagLimit > 1){
            this.$("#productAuthors").append("<p><i>Drag to re-order each " + this.model.getAuthorsLabel().toLowerCase() + "</i></p>");
        }
        this.$("#productAuthors").append("<p><i>Right-Click " + this.model.getAuthorsLabel().toLowerCase() + " to toggle between non-" + networkName + " and " + networkName + " member (if they are known).</i></p>");
        this.$("#productAuthors").append("<p><i>If the lead " + this.model.getAuthorsLabel().toLowerCase() + " is defined, you can indicate them by double-clicking on their name.</i></p>");
        this.$("#productAuthors").append("<p><i>Colour Background: " + this.model.getAuthorsLabel().toLowerCase() + " is <b>known</b> to " + networkName + ".<br />" + 
                                         "   <i>White Background: " + this.model.getAuthorsLabel().toLowerCase() + " is <b>not known</b> to " + networkName + ".</i></p>");
        
        // Ordering authors
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
        
        // Setting UofA/Non-UofA
        this.$el.on('contextmenu', "#productAuthors .tagit-choice", function(e){
            e.preventDefault();
            var origText = $(".tagit-label",$(this)).text();
            var newText = $(".tagit-label",$(this)).text();
            if($(".tagit-label",$(this)).text().includes('"')){
                newText = newText.replace(/"/g, '');
            }
            else{
                newText = '"' + newText + '"';
            }
            var assignedTags = $('div[name=authors_fullname] ul.tagit').tagit('assignedTags');
            $('div[name=authors_fullname] ul.tagit').tagit('removeAll');
            _.each(assignedTags, function(tag){
                if(tag == origText){
                    tag = newText;
                }
                $('div[name=authors_fullname] ul.tagit').tagit('createTag', tag);
            });
        });
        
        // Setting lead author
        this.$el.on('dblclick', "#productAuthors .tagit-choice", function(e){
            var el = $(".tagit-label", e.currentTarget);
            var index = $("ul[name=authors_fullname] li").index(e.currentTarget);
            var authors = this.model.get('authors');
            var author = authors[index];
            var data = this.model.get('data');
            var lead = data['lead'];
            if(lead == null || (lead.fullname != author.fullname && (lead.id != author.id || author.id == undefined))){
                data['lead'] = author;
            }
            else{
                data['lead'] = null;
            }
            this.model.set('data', data);
            var assignedTags = $('div[name=authors_fullname] ul.tagit').tagit('assignedTags');
            $('div[name=authors_fullname] ul.tagit').tagit('removeAll');
            _.each(assignedTags, function(tag){
                $('div[name=authors_fullname] ul.tagit').tagit('createTag', tag);
            });
        }.bind(this));
        
        // Mouse over
        this.$el.on('mouseover', 'div[name=authors_fullname] li.tagit-choice', function(){
            $(this).css('cursor', 'move');
        });
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.simple = true;
            this.allPeople.fetch();
            var spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                }
            }, this);
        }
    },
    
    renderTagsWidget: function(){
        var html = HTML.TagIt(this, 'tags', {
            strictValues: false, 
            values: this.model.get('tags'),
            options: {
                removeConfirmation: false,
                availableTags: this.availableTags
            }
        });
        this.$("#productTags").html(html);
    },
    
    updateProjectWarning: function(){
        if(projectsEnabled && this.model.get('projects').length == 0){
            this.projectsWarning.show();
        } else {
            this.projectsWarning.hide();
        }
    },
    
    updateDOIWarning: function(){
        if(networkName != "FES"){
            return;
        }
        if((this.model.get('category') == "Publication" || 
            this.model.get('category') == "IP Management") &&
           (_.isEmpty(this.model.get('data')['doi']) && 
            _.isEmpty(this.model.get('data')['url']) &&
            _.isEmpty(this.model.get('data')['citations_url']))){
            this.doiWarning.show();
            $(".ui-dialog-buttonset button", this.$el.parent()).prop('disabled', true);
        } else {
            this.doiWarning.hide();
            $(".ui-dialog-buttonset button", this.$el.parent()).prop('disabled', false);
        }
    },
    
    renderJournalsAutocomplete: function(){
        if(this.$("input[name=data_published_in]").length > 0){
            var autoComplete = {
                source: function(request, response){
                    var journals = new Journals();
                    journals.search = request.term;
                    journals.fetch({success: function(collection){
                        var data = _.map(collection.toJSON(), function(journal){
                            var label = journal.title;
                            if(journal.description != null){
                                label += " (" + journal.description + ")";
                            }
                            return {id: journal.id, 
                                    label: label, 
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
                }.bind(this),
                minLength: 2,
                select: function(event, ui){
                    _.defer(function(){
                        this.$("input[name=data_published_in]").val(ui.item.journal).change();
                        this.$("input[name=data_impact_factor]").val(ui.item.impact_factor).change();
                        this.$("input[name=data_category_ranking]").val(ui.item.category_ranking).change();
                        this.$("input[name=data_eigen_factor]").val(ui.item.eigen_factor).change();
                        this.$("input[name=data_issn]").val(ui.item.issn).change();
                    }.bind(this));
                }.bind(this)
            };
            
            this.$("input[name=data_issn]").autocomplete(autoComplete);
            this.$("input[name=data_published_in]").autocomplete(autoComplete);
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.projectsWarning = this.$("#projectsWarning");
        this.doiWarning = this.$("#doiWarning");
        if(this.isDialog){
            $(".ui-dialog-buttonset #projectsWarning", this.$el.parent()).remove();
            this.projectsWarning.css('display', 'inline-block')
                                .css('margin', 0)
                                .css('margin-top', '0')
                                .css('margin-bottom', '5px')
                                .css('font-size', '1em')
                                .css('float', 'left')
                                .css('clear', 'both')
                                .css('padding-right', '15px');
            this.projectsWarning.detach();
            $(".ui-dialog-buttonset", this.$el.parent()).prepend(this.projectsWarning);
            this.projectsWarning.hide();
            
            $(".ui-dialog-buttonset #doiWarning", this.$el.parent()).remove();
            this.doiWarning.css('display', 'inline-block')
                                .css('margin', 0)
                                .css('margin-top', '0')
                                .css('margin-bottom', '5px')
                                .css('font-size', '1em')
                                .css('float', 'left')
                                .css('clear', 'both')
                                .css('padding-right', '15px');
            this.doiWarning.detach();
            $(".ui-dialog-buttonset", this.$el.parent()).prepend(this.doiWarning);
            this.doiWarning.hide();
        }
        this.renderAuthors();
        this.renderJournalsAutocomplete();
        
        this.$("input[name=data_category_ranking]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_impact_factor]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_eigen_factor]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_category_ranking_override]").css('width', '94px').attr('placeholder', 'Override...');;
        this.$("input[name=data_impact_factor_override]").css('width', '94px').attr('placeholder', 'Override...');
        this.$("input[name=data_category_ranking]").after(this.$("input[name=data_category_ranking_override]"));
        this.$("input[name=data_impact_factor]").after(this.$("input[name=data_impact_factor_override]"));
        this.$("input[name=data_category_ranking]").parents("tr").next().remove();
        this.$("input[name=data_impact_factor]").parents("tr").next().remove();
        this.$(".multiselect").css("width", "100%");
        this.$(".multiselect").chosen();
        
        this.renderTagsWidget();
        this.updateProjectWarning();
        this.updateDOIWarning();
        this.$(".integer").forceNumeric({min: 0, max: 99999999999});
        return this.$el;
    }

});

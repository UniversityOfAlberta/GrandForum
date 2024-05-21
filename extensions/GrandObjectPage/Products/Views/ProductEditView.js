ProductEditView = Backbone.View.extend({

    isDialog: false,
    parent: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#product_edit_template').html());

        if(!this.model.isNew() && !this.isDialog){
            // Model exists
            this.model.fetch({
                success: function(){
                    this.listenTo(this.model, "change:projects", this.render);
                    this.listenTo(this.model, "change:category", this.render);
                    this.listenTo(this.model, "change:type", this.render);
                    this.listenTo(this.model, "change:access", this.render);
                }.bind(this)
            });
        }
        else{
            // New Model
            _.defer(this.render);
            this.listenTo(this.model, "change:projects", this.render);
            this.listenTo(this.model, "change:category", this.render);
            this.listenTo(this.model, "change:type", this.render);
            this.listenTo(this.model, "change:access", this.render);
        }
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=date]").val('0000-00-00').change();
        //this.model.set('date', '0000-00-00');
    },
    
    events: {
        "click #infinity": "setInfinite",
        "click #saveProduct": "saveProduct",
        "click #cancel": "cancel",
        "change [name=acceptance_date]": "changeStart",
        "change [name=date]": "changeEnd",
        "change [name=data_start_date]": "changeDataStart",
        "change [name=data_end_date]": "changeDataEnd"
    },
    
    updateStatus: function(){
        _.defer(function(){
            var currentDate = new Date().toISOString().substr(0, 10);
            if(this.model.get('category') == "Publication" && this.model.get('date') != "0000-00-00" && 
                                                              this.model.get('date') != ""){
                if(currentDate < this.model.get('date')){
                    this.$("[name=status]").val("Accepted").change();
                    this.$("[name=status]").prop("disabled", true);
                }
                else{
                    this.$("[name=status]").val("Published").change();
                    this.$("[name=status]").prop("disabled", true);
                }
            }
            else if(this.model.get('category') == "Publication" && this.model.get('acceptance_date') != "0000-00-00" && 
                                                                   this.model.get('acceptance_date') != ""){
                this.$("[name=status]").val("Accepted").change();
                this.$("[name=status]").prop("disabled", true);
            }
            else{
                this.$("[name=status]").prop("disabled", false);
            }
        }.bind(this));
    },
    
    changeStart: function(){
        var start_date = this.$("[name=acceptance_date]").val();
        var end_date = this.$("[name=date]").val();
        if(start_date != "" && start_date != "0000-00-00"){
            if(end_date != "" && end_date != "0000-00-00"){
                this.$("[name=date]").datepicker("option", "minDate", start_date);
            }
            else{
                this.$("[name=date]").datepicker("option", "minDate", "");
                this.$("[name=date]").val(end_date).change();
            }
        }
        this.updateStatus();
    },
    
    changeEnd: function(){
        var start_date = this.$("[name=acceptance_date]").val();
        var end_date = this.$("[name=date]").val();
        if(end_date != "" && end_date != "0000-00-00"){
            this.$("[name=acceptance_date]").datepicker("option", "maxDate", end_date);
        }
        else{
            this.$("[name=acceptance_date]").datepicker("option", "maxDate", null);
        }
        this.updateStatus();
    },
    
    changeDataStart: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("[name=data_start_date]").val();
        var end_date = this.$("[name=data_end_date]").val();
        if(start_date != "" && start_date != "0000-00-00"){
            this.$("[name=data_end_date]").datepicker("option", "minDate", start_date);
        }
    },
    
    changeDataEnd: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("[name=data_start_date]").val();
        var end_date = this.$("[name=data_end_date]").val();
        if(end_date != "" && end_date != "0000-00-00"){
            this.$("[name=data_start_date]").datepicker("option", "maxDate", end_date);
        }
        else{
            this.$("[name=data_start_date]").datepicker("option", "maxDate", null);
        }
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
            error: function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                addError("There was a problem saving the Product", true);
            }.bind(this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
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
        var tagLimit = (this.model.isSingleAuthor()) ? 1 : 1000;
        var placeholderText = (this.model.isSingleAuthor()) ? 'Enter ' + this.model.getAuthorsLabel().toLowerCase() + ' here...'
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
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
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
        if(!this.model.isSingleAuthor()){
            this.$("#productAuthors").append("<p><i>Drag to re-order each " + this.model.getAuthorsLabel().toLowerCase() + "</i></p>");
        }
        this.$("#productAuthors").append("<p><i>Right-Click " + this.model.getAuthorsLabel().toLowerCase() + " to toggle between non-UofA and UofA member (if they are known)</i></p>");
        this.$("#productAuthors").append("<p><i>Double-Click " + this.model.getAuthorsLabel().toLowerCase() + " to specify who is the lead author</i></p>");
        this.$("#productAuthors").append("<p><i>Green Background: " + this.model.getAuthorsLabel().toLowerCase() + " is <b>known</b> to the UofA.<br />" + 
                                         "   <i>White Background: " + this.model.getAuthorsLabel().toLowerCase() + " is <b>not known</b> to the UofA.</i></p>");
        
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
    
    renderContributorsWidget: function(){
        var objs = {};
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
        });

        var availableTags = _.uniq(_.pluck(objs, 'fullname'));
        var delimiter = ';';
        var html = HTML.TagIt(this, 'contributors.fullname', {
            values: _.pluck(this.model.get('contributors'), 'fullname'),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter ' + this.model.getContributorsLabel().pluralize().toLowerCase() + ' here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                availableTags: availableTags,
                afterTagAdded: function(event, ui){
                    if(objs[ui.tagLabel] != undefined){
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
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
        this.$("#productContributors").html(html);
        if(!this.model.isSingleAuthor()){
            this.$("#productContributors").append("<p><i>Drag to re-order each " + this.model.getContributorsLabel().toLowerCase() + "</i></p>");
        }
        this.$("#productContributors .tagit").sortable({
            stop: function(event,ui) {
                $('input[name=contributors_fullname]').val(
                    $(".tagit-label",$(this))
                        .clone()
                        .text(function(index,text){ return (index == 0) ? text : delimiter + text; })
                        .text()
                ).change();
            }
        });
        this.$el.on('mouseover', 'div[name=contributors_fullname] li.tagit-choice', function(){
            $(this).css('cursor', 'move');
        });
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
            if(this.model.hasContributors()){
                this.renderContributorsWidget();
            }
        }
        else{
            this.allPeople = new People();
            this.allPeople.simple = true;
            this.allPeople.fetch();
            var spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
            if(this.model.hasContributors()){
                var spin = spinner("productContributors", 10, 20, 10, 3, '#888');
            }
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                    if(this.model.hasContributors()){
                        this.renderContributorsWidget();
                    }
                }
            }, this);
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
                                    snip: journal.snip,
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
                        this.$("input[name=data_snip]").val(ui.item.snip).change();
                        this.$("input[name=data_issn]").val(ui.item.issn).change();
                    }.bind(this));
                }.bind(this)
            };
            
            this.$("input[name=data_issn]").autocomplete(autoComplete);
            this.$("input[name=data_published_in]").autocomplete(autoComplete);
        }
    },
    
    teardown: function(){
        this.$el.off('contextmenu', "#productAuthors .tagit-choice");
        this.$el.off('dblclick', "#productAuthors .tagit-choice");
        this.$el.off('mouseover', 'div[name=contributors_fullname] li.tagit-choice');
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderJournalsAutocomplete();
        if(productStructure.categories[this.model.get('category')] != undefined &&
           (productStructure.categories[this.model.get('category')].types[this.model.get('type')] != undefined &&
            _.size(productStructure.categories[this.model.get('category')].types[this.model.get('type')].titles) > 0 ||
            _.size(_.first(_.values(productStructure.categories[this.model.get('category')].types)).titles) > 0)){
            this.$("select[name=title]").combobox();
        }
        this.$("input[name=data_category_ranking]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_impact_factor]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_eigen_factor]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_snip]").prop('disabled', true).css('width', '94px');
        this.$("input[name=data_category_ranking_override]").css('width', '94px').attr('placeholder', 'Override...');;
        this.$("input[name=data_impact_factor_override]").css('width', '94px').attr('placeholder', 'Override...');
        this.$("input[name=data_impact_factor]").after("<div>The IFs reported are based on the data available on July 1, " + (YEAR) + "</div>");
        this.$("input[name=data_category_ranking]").after(this.$("input[name=data_category_ranking_override]"));
        this.$("input[name=data_impact_factor]").after(this.$("input[name=data_impact_factor_override]"));
        this.$("input[name=data_category_ranking]").parents("tr").next().remove();
        this.$("input[name=data_impact_factor]").parents("tr").next().remove();

        _.defer(function(){
            this.$("[name=acceptance_date]").change();
            this.$("[name=date]").change();
        }.bind(this));
        //this.updateStatus();
        return this.$el;
    }

});

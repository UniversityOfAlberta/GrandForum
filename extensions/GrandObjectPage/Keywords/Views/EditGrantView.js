EditGrantView = Backbone.View.extend({

    person: null,
    timeout: null,

    initialize: function(){
        $.get(wgServer + wgScriptPath + "/index.php?action=api.keyword/keywords", function(response){
            allKeywords = response;
        });
        $.get(wgServer + wgScriptPath + "/index.php?action=api.keyword/partners", function(response){
            allPartners = response;
        });
        if(!this.model.isNew()){
            this.model.fetch({
                error: function(e){
                    this.$el.html("This Keyword does not exist");
                }.bind(this)
            });
        }
        this.listenTo(this.model, "change:project_id", function(){
            if(this.model.get('scientific_title') != ''){
                main.set('title', "<a href='#'>Keywords</a> > " + this.model.get('project_id'));
            }
            else if(this.model.isNew()){
                main.set('title', "<a href='#'>Keywords</a> > " + 'New Keyword');
            }
            else if(!this.model.isNew()){
                main.set('title', "<a href='#'>Keywords</a> > " + 'Editing Keyword');
            }
        });
        this.allPeople = new People();
        this.allPeople.simple = true;
        this.allPeople.roles = [NI,"ATS"];
        var xhr1 = this.allPeople.fetch();
        this.listenTo(this.model, 'sync', function(){
            this.person = new Person({id: this.model.get('user_id')});
            if(this.person.get('id') != 0){
                var xhr2 = this.person.fetch();
                $.when.apply(null, [xhr1, xhr2]).then(this.render);
            }
            else{
                $.when(xhr1).then(this.render);
            }
        });
        
        this.template = _.template($('#edit_grant_template').html());
        if(this.model.isNew()){
            this.model.trigger("sync");
        }
    },
    
    save: function(){
        this.$(".throbber").show();
        this.$("#save").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Keyword", true);
                }
            }.bind(this)
        });
    },
    
    hidePreview: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        $("#preview").hide();
    },
    
    changePI: function(){
        _.defer(function(){
            this.model.set('user_id', this.model.get('pi').id);
        }.bind(this));
    },
    
    events: {
        "click #save": "save"
    },
    
    renderCoapplicantsWidget: function(){
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });

        var delimiter = ';';
        var tagLimit = 1000;
        var placeholderText = 'Enter Co-Applicants here...';
        var that = this;
        var html = HTML.TagIt(this, 'copi.fullname', {
            values: _.pluck(this.model.get('copi'), 'fullname'),
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
                availableTags: this.allPeople.pluck('fullName'),
                beforeTagAdded: function(event, ui) {
                    if(that.allPeople.pluck('fullName').indexOf(ui.tagLabel) == -1){
                        return false;
                    }
                    if(ui.tagLabel == "not found"){
                        return false;
                    }
                    if(that.allPeople.pluck('fullName').indexOf(ui.tagLabel) >= 0){
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
                    }
                },
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
        this.$("#coapplicants").html(html);
        this.$("#coapplicants").append("<p><i>Drag to re-order each co-applicant</i></p>");
        this.$("#coapplicants .tagit").sortable({
            stop: function(event,ui) {
                $('input[name=copi_fullname]').val(
                    $(".tagit-label",$(this))
                        .clone()
                        .text(function(index,text){ return (index == 0) ? text : delimiter + text; })
                        .text()
                ).change();
            }
        });
        this.$el.on('mouseover', 'div[name=coapplicants_fullname] li.tagit-choice', function(){
            $(this).css('cursor', 'move');
        });
    },
    
    renderCoapplicants: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderCoapplicantsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            var spin = spinner("coapplicants", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderCoapplicantsWidget();
                }
            }, this);
        }
    },
    
    renderKeywords: function(){
        var placeholderText = "Enter Keywords Here...";
        var delimiter = ";";
        var tagLimit = 1000;
        var availableTags = allKeywords;
        var html = HTML.TagIt(this, 'keywords', {
            values: this.model.get('keywords'),
            strictValues: false, 
            options: {
                placeholderText: placeholderText,
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                tagLimit: tagLimit,
                availableTags: availableTags
            }
        });
        this.$("#keywords").html(html);
    },
    
    renderPartners: function(){
        var placeholderText = "Enter Partners Here...";
        var delimiter = ";";
        var tagLimit = 1000;
        var availableTags = allPartners;
        var html = HTML.TagIt(this, 'partners', {
            values: this.model.get('partners'),
            strictValues: false, 
            options: {
                placeholderText: placeholderText,
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                tagLimit: tagLimit,
                availableTags: availableTags
            }
        });
        this.$("#partners").html(html);
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderKeywords();
        this.renderCoapplicants();
        this.renderPartners();
        this.$('select[name=pi_id]').chosen({allow_single_deselect: true}).change(this.changePI.bind(this));
        _.defer(function(){
            this.$("#start_date").change();
            this.$("#end_date").change();
        }.bind(this));
        return this.$el;
    }

});

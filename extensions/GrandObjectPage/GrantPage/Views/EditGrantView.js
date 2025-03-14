EditGrantView = Backbone.View.extend({

    person: null,
    timeout: null,

    initialize: function(){
        if(!this.model.isNew()){
            this.model.fetch({
                error: function(e){
                    this.$el.html("This Grant does not exist");
                }.bind(this)
            });
        }
        this.listenTo(this.model, "change:scientific_title", function(){
            if(this.model.get('scientific_title') != ''){
                main.set('title', "<a href='#'>Grants</a> > " + this.model.get('scientific_title'));
            }
            else if(this.model.isNew()){
                main.set('title', "<a href='#'>Grants</a> > " + 'New Grant');
            }
            else if(!this.model.isNew()){
                main.set('title', "<a href='#'>Grants</a> > " + 'Editing Grant');
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
        this.listenTo(this.model, 'change:copi', this.renderPortions.bind(this));
        
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
                    addError("There was a problem saving the Grant", true);
                }
            }.bind(this)
        });
    },
    
    previewContribution: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        this.timeout = setTimeout(function(){
            var id = $(e.currentTarget).attr('data-id');
            $.get(wgServer + wgScriptPath + "/index.php/Contribution:" + id, function(response){
                var widthBefore = $(document).width();
                var heightBefore = $(document).height();

                var view = $("#bodyContent", response);
                $("#footer", view).remove();

                $("#preview").html(view.html());

                $("input[name=edit]").hide();

                $("#preview").fadeIn(100);
                $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4);
                $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2);
                
                var widthAfter = $(document).width();
                var heightAfter = $(document).height();
                
                if(widthAfter != widthBefore){
                    $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4 - (widthAfter - widthBefore + 5));
                }
                if(heightAfter != heightBefore){
                    $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2 - (heightAfter - heightBefore + 5));
                }
            }.bind(this));
        }.bind(this), 50);
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
        "click #save": "save",
        "mouseover #contributions .sortable-list li": "previewContribution",
        "mouseout #contributions .sortable-list li": "hidePreview",
        "change #start_date": "changeStart",
        "change #end_date": "changeEnd"
    },
    
    changeStart: function(){
        var start_date = this.$("#start_date").val();
        var end_date = this.$("#end_date").val();
        if(start_date != "" && start_date != ZOT){
            if(end_date != "" && end_date != ZOT){
                this.$("#end_date").datepicker("option", "minDate", start_date);
            }
            else{
                this.$("#end_date").datepicker("option", "minDate", "");
                this.$("#end_date").val(end_date).change();
            }
        }
    },
    
    changeEnd: function(){
        var start_date = this.$("#start_date").val();
        var end_date = this.$("#end_date").val();
        if(end_date != "" && end_date != ZOT){
            this.$("#start_date").datepicker("option", "maxDate", end_date);
        }
    },
    
    renderCoapplicantsWidget: function(){
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
                availableTags: availableTags,
                afterTagAdded: function(event, ui){
                    // UofA Author
                    if(objs[ui.tagLabel] != undefined){
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
                        if(ui.tag.children("span").length > 1){
                            ui.tag.children("span")[1].style.setProperty("color", "white", 'important');
                        }
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
    
    renderPortions: function(){
        this.$('#portions').empty();
        _.each(this.model.get('copi'), function(copi){
            if(copi.id != undefined){
                var textbox = HTML.TextBox(this, 'portions.fullname', {});
                this.$('#portions').append("<b>" + copi.fullname + ": </b>" + textbox + "<br />");
            }
        }.bind(this));
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderCoapplicants();
        //this.renderPortions();
        this.$('input[name=total]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 2});
        this.$('input[name=funds_before]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 2});
        this.$('input[name=funds_after]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 2});
        this.$('select[name=pi_id]').chosen({allow_single_deselect: true}).change(this.changePI.bind(this));
        this.$('input[name=adjusted_amount]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 2});
        
        _.defer(function(){
            this.$("#start_date").change();
            this.$("#end_date").change();
        }.bind(this));
        return this.$el;
    }

});

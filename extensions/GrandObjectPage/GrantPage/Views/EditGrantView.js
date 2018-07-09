EditGrantView = Backbone.View.extend({

    person: null,
    timeout: null,
    allContributions: null,

    initialize: function(){
        if(!this.model.isNew()){
            this.model.fetch({
                error: $.proxy(function(e){
                    this.$el.html("This Revenue Account does not exist");
                }, this)
            });
        }
        this.listenTo(this.model, "change:title", function(){
            if(this.model.get('title') != ''){
                main.set('title', this.model.get('title'));
            }
            else if(this.model.isNew()){
                main.set('title', 'New Revenue Account');
            }
            else if(!this.model.isNew()){
                main.set('title', 'Editing Revenue Account');
            }
        });
        this.allPeople = new People();
        this.allPeople.simple = true;
        this.allPeople.roles = [NI];
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
        
        $.get(wgServer + wgScriptPath + "/index.php?action=contributionSearch&phrase=&category=all", $.proxy(function(response){
            this.allContributions = response;
        }, this));
        
        this.template = _.template($('#edit_grant_template').html());
        if(this.model.isNew()){
            this.model.trigger("sync");
        }
    },
    
    save: function(){
        this.$(".throbber").show();
        this.$("#save").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Revenue Account", true);
                }
            }, this)
        });
    },
    
    previewContribution: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        this.timeout = setTimeout($.proxy(function(){
            var id = $(e.currentTarget).attr('data-id');
            $.get(wgServer + wgScriptPath + "/index.php/Contribution:" + id, $.proxy(function(response){
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
            }, this));
        }, this), 50);
    },
    
    hidePreview: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        $("#preview").hide();
    },
    
    changePI: function(){
        _.defer($.proxy(function(){
            this.model.set('user_id', this.model.get('pi').id);
        }, this));
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
        if(start_date != "" && start_date != "0000-00-00"){
            if(end_date != "" && end_date != "0000-00-00"){
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
        if(end_date != "" && end_date != "0000-00-00"){
            this.$("#start_date").datepicker("option", "maxDate", end_date);
        }
    },
    
    renderContributionsWidget: function(){
        var model = this.model;
        if(headerColor != "#333333"){
            // Headers were changed, use this color
            this.$("#contributions .sortable-header").css("background", headerColor);
        }
        else{
            // Otherwise use the highlight color
            this.$("#contributions .sortable-header").css("background", highlightColor);
        }

        if(this.allContributions.length == 0){
            return;
        }

        var contributions = this.model.get('contributions');
        this.$("#contributions .sortable-widget").show();
        
        // Left Side (Current)
        _.each(contributions, $.proxy(function(cId){
            var contribution = _.findWhere(this.allContributions, {id: cId.toString()});
            if(contribution != null){
                this.$("#contributions #sortable1").append("<li data-id='" + contribution.id + "'>" + contribution.name + "</li>");
            }
        }, this));
        
        // Right Side (Available)
        _.each(this.allContributions, $.proxy(function(contribution){
            if(!_.contains(contributions, contribution.id)){
                this.$("#contributions #sortable2").append("<li data-id='" + contribution.id + "'>" + contribution.name + "</li>");
            }
        }, this));
    
        // Advanced groups
	    [{
		    name: 'contributions',
		    pull: true,
		    put: true
	    },
	    {
		    name: 'contributions',
		    pull: true,
		    put: true
	    }].forEach(function (groupOpts, i) {
	        if($("#contributions #sortable" + (i + 1)).length > 0){
		        Sortable.create($("#contributions #sortable" + (i + 1))[0], {
			        sort: (i != 1),
			        group: groupOpts,
			        animation: 150,
			        onSort: function (e) {
                        if($(e.target).attr('id') == 'sortable1'){
                            var ids = new Array();
                            $("li:visible", $(e.target)).each(function(i, el){
                                ids.push(parseInt($(el).attr('data-id')));
                            });
                            model.set('contributions', ids);
                        }
                    }
		        });
		    }
	    });
	    
	    var changeFn = function(){
	        var value = this.$("#contributions .sortable-search input").val().trim();
	        var lower = value.toLowerCase();
	        var showElements = new Array();
	        var hideElements = new Array();
	        $("#contributions #sortable2 li").each(function(i, el){
	            if($(el).text().toLowerCase().indexOf(lower) !== -1 || value == ""){
	                showElements.push(el);
	            }
	            else{
	                hideElements.push(el);
	            }
	        });
	        $(showElements).show();
	        $(hideElements).hide();
	    };
	    
	    this.$("#contributions .sortable-search input").change($.proxy(changeFn, this));
	    this.$("#contributions .sortable-search input").keyup($.proxy(changeFn, this));
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
                beforeTagAdded: $.proxy(function(event, ui) {
                    if(this.allPeople.pluck('fullName').indexOf(ui.tagLabel) == -1){
                        return false;
                    }
                    if(ui.tagLabel == "not found"){
                        return false;
                    }
                    if(this.allPeople.pluck('fullName').indexOf(ui.tagLabel) >= 0){
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
                    }
                }, this),
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
    
    /*renderCoapplicantsWidget: function(){
        var left = _.pluck(this.model.get('coapplicants'), 'fullname');
        var right = _.difference(this.allPeople.pluck('fullName'), left);
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });
        var html = HTML.Switcheroo(this, 'coapplicants.fullname', {name: 'Co-Applicant',
                                                                   left: left,
                                                                   right: right,
                                                                   objs: objs
                                                                  });
        this.$("#coapplicants").html(html);
        createSwitcheroos();
    },*/
    
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

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributionsWidget();
        this.renderCoapplicants();
        this.$('input[name=total]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('input[name=funds_before]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('input[name=funds_after]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('select[name=pi_id]').chosen({allow_single_deselect: true}).change($.proxy(this.changePI, this));
        
        _.defer($.proxy(function(){
            this.$("#start_date").change();
            this.$("#end_date").change();
        }, this));
        return this.$el;
    }

});

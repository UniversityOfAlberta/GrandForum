EditGrantView = Backbone.View.extend({

    person: null,
    timeout: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Grant does not exist");
            }, this)
        });
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.listenTo(this.model, 'sync', function(){
            this.person = new Person({id: this.model.get('user_id')});
            var xhr = this.person.fetch();
            $.when(xhr).then(this.render);
        });
        
        $.get(wgServer + wgScriptPath + "/index.php?action=contributionSearch&phrase=&category=all", $.proxy(function(response){
            this.allContributions = response;
        }, this));
        
        this.template = _.template($('#edit_grant_template').html());
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
                    addError("There was a problem saving the Grant", true);
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

                $("#preview").html($("#bodyContent", response).html());

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
    
    events: {
        "click #save": "save",
        "mouseover #contributions .sortable-list li": "previewContribution",
        "mouseout #contributions .sortable-list li": "hidePreview"
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

    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributionsWidget();
        this.$('input[name=total]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('input[name=funds_before]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('input[name=funds_after]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        return this.$el;
    }

});

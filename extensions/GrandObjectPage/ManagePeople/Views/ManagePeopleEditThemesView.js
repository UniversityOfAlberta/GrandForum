ManagePeopleEditThemeLeadersView = Backbone.View.extend({

    themes: null,
    person: null,
    themeViews: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.themeViews = new Array();
        this.template = _.template($('#edit_themes_template').html());
        this.person.getRoles();
        this.model.ready().then(function(){
            this.themes = this.model;
            this.listenTo(this.themes, "add", this.addRows);
            this.themes.each(function(p){
                p.startTracking();
            });
            this.render();
        }.bind(this));
        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval(function(){
            if(this.$el.width() != dim.w1 ||
               this.$el.height() != dim.h1 ||
               $(window).width() != dim.w2 ||
               $(window).height() != dim.h2){
                if(this.$el.height() >= $(window).height() - 100){
                    this.$el.height($(window).height() - 100);
                }
                else{
                    this.$el.height('auto');
                }
                this.$el.dialog("option","position", {
                    my: "center center",
                    at: "center center"
                });
            }
            dim.w1 = this.$el.width();
            dim.h1 = this.$el.height();
            dim.w2 = $(window).width();
            dim.h2 = $(window).height();
	    }.bind(this), 100);
    },
    
    saveAll: function(){
        var copy = this.themes.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, function(theme){
            if(_.contains(allowedThemes, theme.get('name')) && theme.unsavedAttributes() != false){
                if(theme.get('deleted') != "true"){
                    requests.push(theme.save(null));
                }
                else {
                    requests.push(theme.destroy(null));
                }
            }
        }.bind(this));
        $.when.apply($, requests).done(function(){
            addSuccess("Themes saved");
        }).fail(function(){
            addError("Themes could not be saved");
        });
    },
    
    addTheme: function(){
        var theme = _.first(allowedThemes);
        var personTheme = new PersonTheme();
        personTheme.startTracking();
        personTheme.set("name", theme);
        personTheme.set("personId", this.person.get('id'));
        this.themes.add(personTheme);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.themes.each(function(theme, i){
            if(this.themeViews[i] == null){
                var view = new ManagePeopleEditThemesRowView({model: theme});
                this.$("#theme_rows").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.themeViews[i] = view;
            }
        }.bind(this));
    },
       
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditThemesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.model.set('deleted', false);
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_themes_row_template').html());
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('0000-00-00').trigger("change");
        this.model.set('endDate', '0000-00-00');
    },
    
    events: {
        "click #infinity": "setInfinite"
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.update();
        return this.$el;
    }, 
    
});

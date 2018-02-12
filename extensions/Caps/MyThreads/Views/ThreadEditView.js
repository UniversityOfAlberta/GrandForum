ThreadEditView = Backbone.View.extend({

    isDialog: false,
    parent: null,
    allPeople: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', striphtml(this.model.get('title')));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#thread_edit_template').html());
        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
    },
    
    events: {
        "click #saveThread": "saveThread",
        "click #cancel": "cancel",
        "change [name='visibility']": "checkVisibility",
        "change #prov": "checkProvince",
        "change [name='public/private']" : "publicOrprivate",
    },

    checkVisibility: function(){
        if($("[name='visibility']").val() == "Chosen Experts"){
            this.renderAuthors();
            $("#threadPeople").show();
            $(".provinceSearch").show();
           	$(".chzn-select").chosen();

        }
        else if($("[name='visibility']").val() == "question is only visible to experts"){
            $("#threadPeople").hide();
            $("#provinceSearch").hide();
        }
        else if($("[name='visibility']").val() == "All Experts" || $("[name='visibility']").val() == "question is visible to CAPS health care professionals" ){
            $("#threadPeople").hide();
            $("#provinceSearch").hide();
        }
    },

    publicOrprivate: function(){
        if($("[name='public']").val() == "public"){
            console.log("it is public");
        }
        else{
            console.log("it is private");
        }

    },

    checkProvince: function(){
        this.renderAuthors();
    },
    
    validate: function(){
        if(this.model.get('title').trim() == ""){
            return "The Thread must have a title";
        }
        return "";
    },
    
    saveThread: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessage
            s();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveThread").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveThread").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(m, e){
                this.$(".throbber").hide();
                this.$("#saveThread").prop('disabled', false);
                clearAllMessages();
                addError(e.responseText, true);
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        var checkProvPeople = this.allPeople.models.slice();
        var province = $("#prov").val();
        var left = _.pluck(this.model.get('authors'), 'name');
        for (i = 0; i < checkProvPeople.length; i++) {
            if(province == "All"){
                break;
            }
            if(checkProvPeople[i].get('province') != province && checkProvPeople[i].get('province') != ""){
                checkProvPeople.splice(i,1);
                i--;
            }
        }
        checkProvPeople = _.pluck(_.pluck(checkProvPeople, 'attributes'),'name');
        var right = _.difference(this.allPeople.pluck('name'), left);
        right = _.intersection(right,checkProvPeople);
        var html = HTML.Switcheroo(this, 'authors.name', {name: 'author',
                                                          'left': left,
                                                          'right': right
                                                          });
        this.$("#threadPeople").html(html);
        createSwitcheroos();
    },
    
    renderAuthors: function(){
            this.allPeople = new People();
            this.allPeople.roles = ["Expert"];
            this.allPeople.fetch();
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                }
            }, this);
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});

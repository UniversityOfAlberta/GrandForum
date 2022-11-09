SopsEditView = Backbone.View.extend({

    sops: null,
    gsmsdata: null,
    initialize: function(){
        this.template = _.template($('#sops_edit_template').html());
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.gsmsdata = new GsmsData({user_id: this.model.get('user_id')});
            var xhr = this.gsmsdata.fetch();
            $.when(xhr).then(this.render);
            //this.render();
        }, this);
    },
    
    events: {
        "click #check_readability" : "check_readability",
        "click #check_coleman" : "check_coleman",
        "click #check_dalechall" : "check_dalechall",
        "click #check_flesch" : "check_flesch",
        "click #check_smog" : "check_smog",
        "click #check_dalechall_reading" : "check_dalechall_reading",
        "click #check_sentiment" : "check_sentiment",
        "click #check_sentiment_score" : "check_sentiment_score",
        "click #check_emotion" : "check_emotion",
        "click #check_anger" : "check_anger",
        "click #check_disgust" : "check_disgust",
        "click #check_fear" : "check_fear",
        "click #check_joy" : "check_joy",
        "click #check_readingease" : "check_readingease",
        "click #check_personality" : "check_personality",
        "click #hide_stats": "hide_stats",
        "click #sop_statistics2": "show_stats",


    },
    check_joy: function(){                $('#joy_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_fear: function(){                $('#fear_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_disgust: function(){                $('#disgust_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_anger: function(){                $('#anger_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_emotion: function(){
        $('#emotion_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_personality: function(){
        $('#personality_stats').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },

    check_sentiment_score: function(){        $('#sentiment_score_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_sentiment: function(){
        $('#sentiment_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_dalechall_reading: function(){
        $('#dalechall_reading_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_readability: function(){
  $('#readabilty_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },

    check_coleman: function(){
        $('#coleman_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_dalechall: function(){
        $('#dalechall_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_flesch: function(){
        $('#flesch_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_smog: function(){
        $('#smog_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },
    check_readingease: function(){
        $('#readingease_index').dialog({width:'500px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },


    spellcheck: function(){
       AtD.check('sopItem', {
          success : function(errorCount){
          },
          error : function(reason){
          }
       });
    },

    openDialog: function(div){
  $('#'+div).dialog({width:'200px',position:{my: 'center', at:'center', of: window},modal:true,resizable:false,     buttons: {
                            'OK': function () {
                                $(this).dialog('close')
                            }
                        }});

    },

    hide_stats: function(){
        $('#sop_statistics').animate({width:'0%'});
  $("#sop_statistics").hide();
  $("#sop_statistics2").show();
  $('#sop_div').animate({width:'95%'});
    },

    show_stats: function(){
        $("#sop_statistics2").hide();
        $('#sop_div').animate({width:'80%'});
  $('#sop_statistics').animate({width:'15%'});
  $('#sop_statistics').show();
    },

    set_link_to_table: function() {
        var suffix = "#";
        switch(this.gsmsdata.get('folder')) {
          case "Review Complete":
          case "Review in Progress":
            suffix = "#/reviewInProgress";
            break;
          case "In Progress":
            suffix = "#/inProgress";
            break;
          case "":
          case "New Applications":
            suffix = "#/newApplications";
            break;
          case "Rejected Apps":
            suffix = "#/rejected";
            break;
        }
        switch(this.gsmsdata.get('admit')){
          case "Admit":
            suffix = "#/admitted";
            break;
          case "Reject":
            suffix = "#/rejected";
            break;
        }
        var reviewers = this.gsmsdata.attributes.reviewers;
        //console.log(reviewers);
        for (var i = 0; i < reviewers.length; i++) {
            if ((reviewers[i].id == me.id) && (reviewers[i].rank == "-1")) {
                suffix = "#/hidden";
            }
        }
        var other_reviewers = this.gsmsdata.attributes.other_reviewers;
        //console.log(other_reviewers);
        for (var i = 0; i < other_reviewers.length; i++) {
            if ((other_reviewers[i].id == me.id) && (other_reviewers[i].rank == "-1")) {
                suffix = "#/hidden";
            }
        }
        suffix += "/"+this.gsmsdata.attributes.student_data.email;
        $('#link_to_table').attr('href', wgServer+wgScriptPath+'/index.php/Special:Sops'+suffix);
    },

    render: function(){
      $("#pageTitle").hide();
      main.set('title', this.model.get('author').name + " (" + this.gsmsdata.get('folder') + ")");
      var self = this;
      var moveAnnotatorFilter = setInterval(function(){
          if(($('.annotator-filter').length)>0){
              var annotator_filter = $('.annotator-filter').detach();
              $('#sop_acc').prepend(annotator_filter);
              clearInterval(moveAnnotatorFilter);
          }
      }, 500);
      setTimeout(function (){
        self.spellcheck();
        $('iframe').css('border','0 !important');
      }, 1000);
      setTimeout(function(){
              $("#sopItem").annotator()
                  .annotator('addPlugin', 'Filter', {
                    filters: [
                      {
                        label: 'MyTags',
                        property: 'tags',
                        isFiltered: function (input, tags){
                          if(input && tags && tags.length){
                            var keywords = input.split(/\s+/g);
                            for(var i = 0; i < keywords.length; i += 1){
                              for(var j = 0; j < tags.length; j += 1){
                                if(tags[j].indexOf(keywords[i]) !== -1){
                                  return true;
                                }
                              }
                            }
                          }
                          return false;
                        }
                      }
                    ]
                  })
                  .annotator('addPlugin', 'MyTags', {
                    availableTags: [
                      "other",
                      "academic experience", 
                      "professional experience", 
                      "personal qualities",
                    ], // use tags
                  })
                  .annotator( 'addPlugin', 'Store', {
                      prefix: wgServer+wgScriptPath+"/index.php?action=api.sop/" + self.model.id,
                      urls: {
                          create: '/annotations',
                          update: '/annotations/:id',
                          read: '/annotations/:id',
                          destroy: '/annotations/:id',
                          search: '/annotations'
                      },
                  }).annotator('addPlugin', 'Permissions', {
                    userAuthorize: function(action, annotation, user) {
                      var token, tokens, _i, _len;
                      if (annotation.permissions) {
                        tokens = annotation.permissions[action] || [];
                        if (tokens.length === 0) {
                          return true;
                        }
                        for (_i = 0, _len = tokens.length; _i < _len; _i++) {
                          token = tokens[_i];
                          if (this.userId(user) === token) {
                            return true;
                          }
                        }
                        return true;
                      } else if (annotation.user) {
                        if (user) {
                          return this.userId(user) === this.userId(annotation.user);
                        } else {
                          return true;
                        }
                      }
                      return true;
                    },
                    showEditPermissionsCheckbox: false,
                    showViewPermissionsCheckbox: false,
                    user: me.get('name'), // 'me.id' -> logged in user id
                    userId: function (user) {
                      if (user && user.id) {
                        return user.id;
                      }
                      return user;
                    },
                    userString: function (user) {
                      if (user && user.name) {
                        return user;
                      }
                      return user;
                    }
                  });

      }, 6000);
      /** This part constantly updates the errors column. It needs to check for the ajax response from AtD **/
      $( document ).ajaxComplete(function() {
          if($("#grammar_errors").text() == "0" || $("#grammar_errors").text() == ""){
                    $("#grammar_errors").html($(".hiddenGrammarError").size());
          }
                if($("#spelling_errors").text() == "0" || $("#spelling_errors").text() == ""){
        $("#spelling_errors").html($(".hiddenSpellError").size());
          }
                if($("#style_errors").text() == "0" || $("#style_errors").text() == ""){
        $("#style_errors").html($(".hiddenSuggestion").size());
          }
      });
      /** ----------------------------------------------------------------------------------------------  **/
      /**TEST**/
      var w = 80,h = 80;
      //Data
      var d = [
            [
              {axis:"Conscientiousness",value:self.model.get('conscientiousness')},
              {axis:"Openness",value:self.model.get('openness')},
              {axis:"Agreeableness",value:self.model.get('agreeableness')},
              {axis:"Neuroticism",value:self.model.get('neurotism')},
              {axis:"Extraversion",value:self.model.get('extraversion')},
            ]
      ];

      //Options for the Radar chart, other than default
      var mycfg = {
        w: w,
        h: h,
        maxValue: 1,
        levels: 4,
        ExtraWidthX: 400
      }

      //Call function to draw the Radar chart
      //Will expect that data is in %'s

      var intervalId = setInterval(function(){
        if($('#chart').is(':visible')){
          RadarChart.draw("#chart", d, mycfg);
          clearInterval(intervalId);
          intervalId = null;
        }     
      }, 100)

      var mod = _.extend(this.model.toJSON(), this.gsmsdata.toJSON());
      mod.sop_url = this.model.get("sop_url");
      this.el.innerHTML = this.template(mod);
      $("#accordion > div").accordion({
          autoHeight: false,
          collapsible: true,
          active:false
      });
      var reviewerInterval = setInterval(function(){
        if($("#review_iframe").is(":visible")){
            $("#review_iframe").attr('src', $("#review_iframe").attr("data-src"));
            clearInterval(reviewerInterval);
        }
      }, 33);
      var gradChairInterval = setInterval(function(){
        if($("#gradchair_iframe").is(":visible")){
            $("#gradchair_iframe").attr('src', $("#gradchair_iframe").attr("data-src"));
            clearInterval(gradChairInterval);
        }
      }, 33);
      spinner("review_spinner", 40, 75, 12, 10, '#888');
      spinner("gradchair_spinner", 40, 75, 12, 10, '#888');
      this.set_link_to_table();
      return this.$el;
    }
});

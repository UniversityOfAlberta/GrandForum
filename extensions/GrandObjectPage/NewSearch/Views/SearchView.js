SearchView = Backbone.View.extend({

    productTag: null,

    initialize: function(){
        //this.model.fetch();
        //this.model.bind('reset', this.render, this);
        this.template = _.template($('#search_template').html());
        this.render();
    },
    
    events: {
        "click #search_btn": "doSearch",
        "click #cancel": "cancel"
    },

    doSearch: function(){
        this.do_solr_query();
    },

    do_modifiers: function(facets, type_arr, tag_id, solr_field){
        var selected = $('#' + tag_id).val();
        if (selected != 'any'){
            var modifier = $('#' + tag_id + '_modify').val(); 
            if (modifier == 'is') 
              facets.push(solr_field + ':"' + selected + '"');
            else { // GT or LT
              var target = type_arr[selected];
              var temp = new Array();
              for (var key in type_arr){
                if ((modifier == 'gt_eq' && type_arr[key] >= target)
                 || (modifier == 'lt_eq' && type_arr[key] <= target))
                  temp.push(solr_field + ':"' + key + '"');
              }
              facets.push("(" + temp.join(" OR ") +")");
            }
        }
    },

    compile_facets: function(){
        var facets = new Array();
        var roles = {"BOD":90,"Manager":80,"Champion":70,"RMC":70,"PNI":60,"CNI":50,"Associated Researcher":40,"HQP":40,"Staff":30};
        var ranks = {"VP Research":90,"Associate Dean of Research":85,"Associate Dean of Student Affairs":85,"Director":80,"Canada Research Chair":80,"Professor":70,"Associate Professor":60,"Assistant Professor":50,"PostDoc":40,"PhD Student":30,"Industry Associate":25,"Masters Student":20,"Technician":15,"Undergraduate":10,"Other":0,"Unknown":0,"":0};


        // ROLE
        this.do_modifiers(facets, roles, 'sel_role', 'user_role');

        // RANKS
        this.do_modifiers(facets, ranks, 'sel_rank', 'user_rank');

        // PROJECTS
        if ($('#sel_proj').val() != 'any') 
            facets.push('proj_abbr:' + $('#sel_proj').val());

        //alert("facets: " + facets.length + "   " + facets.join(" AND "));

        if (facets.length > 0)
            return '&fq=(' + facets.join(" AND ") + ')';
        
        return '';
    },

    limit_product_count: function(){
        var product_count = '';
        var modifier = $('#sel_prod').val(); 
        if (modifier != 'any'){
            var target = $('#text_prod').val();
            if (target != '')
              if (modifier == 'gt_eq')
                product_count = '&fq=pub_count:[' + target + ' TO *]';
              else if (modifier == 'lt_eq')
                product_count = '&fq=pub_count:[* TO ' + target + ']';
        }
        return product_count; 
    },

    do_solr_query: function(){
        query = '"' + encodeURIComponent($("#query").val() + '"');
        start = 0;
        rows = 10;
        url_solr = "http://grand.cs.ualberta.ca:8981/solr/select?" 
          + "&wt=json"
          + "&json.wrf=?"
          + "&debug=results"
          + "&fl=score,*"
          + '&defType=dismax&bf=user_exp^20.0'
          + "&q=" + query
          + "&start=" + start
          + "&rows=" + rows
          + this.compile_facets() + this.limit_product_count();

        $.getJSON(url_solr, function(data){
            $("#results").text("");
            $("#cards").text("");
            $.each(data, function(key, val){
              //this.load_user_cards(key, val);
             });
        });
    },

    /*processData: function(){
        // This method is purposely not using Backbone views for performance reasons
        var data = Array();
        _.each(this.model.toJSON(), function(model, index){
            var authors = Array();
            var projects = Array();
            _.each(model.authors, function(author, aId){
                if(author.url != ''){
                    authors.push("<a href='" + author.url + "' target='_blank'>" + author.name + "</a>");
                }
                else{
                    authors.push(author.name);
                }
            });
            _.each(model.projects, function(project, aId){
                if(project.url != ''){
                    projects.push("<a href='" + project.url + "' target='_blank'>" + project.name + "</a>");
                }
                else{
                    projects.push(project.name);
                }
            });
            data.push(new Array("<span style='white-space: nowrap;'>" + model.date + "</span>", 
                                "<span style='white-space: nowrap;'>" + model.type + "</span>",
                                "<a href='" + model.url + "'>" + model.title + "</a>", authors.join(', '), projects.join(', ')));
        }, this);
        return data;
    },*/
    
    render: function(){
        // this.$el.empty();
        // this.$el.css('display', 'none');
        // var templateData = {'url' : '', 'title' : ''};
        // if(Backbone.history.fragment.indexOf('nonGrand') == -1){
        //     templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment + '/nonGrand';
        //     templateData.name = 'Non-' + main.get('title');
        // }
        // else{
        //     templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment.replace('/nonGrand', '');
        //     templateData.name = main.get('title').replace('Non-', '');
        // }
        //this.$el.html(this.template(templateData));
     //    var showButton = this.$("#showButton").detach();
     //    var data = this.processData();
     //    this.$el.find('#listTable').dataTable({'iDisplayLength': 100,
	    //                                        'aaSorting': [ [0,'desc'], [1,'asc'], [4, 'asc'] ],
	    //                                        'aaData' : data,
	    //                                        'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	    // this.$("#listTable_length").append(showButton);
     //    this.$el.css('display', 'block');
        this.$el.html(this.template);
       // return this.$el;
    }

});
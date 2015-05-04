SearchView = Backbone.View.extend({

    template: null,
    
    roles: {"BOD":90,"Manager":80,"Champion":70,"RMC":70,"NI":60,"HQP":40,"Staff":30},
    
    ranks: {"VP Research":90,"Associate Dean of Research":85,"Associate Dean of Student Affairs":85,"Director":80,"Canada Research Chair":80,"Professor":70,"Associate Professor":60,"Assistant Professor":50,"PostDoc":40,"PhD Student":30,"Industry Associate":25,"Masters Student":20,"Technician":15,"Undergraduate":10,"Other":0,"Unknown":0},
    
    max_products: 0,

    options: {
        page_num: 1
    },

    initialize: function(){
        this.template = _.template($('#search_template').html());
        this.render();
        this.init_projects_dd();
        this.init_roles_dd();
        this.init_ranks_dd();
        this.get_max_products();
        this.options.page_num = 0;


        //console.log("page_num="+this.options.page_num);
    },
    
    events: {
        "click #search_btn": "doSearch"
    },

    setup_pagination: function(items, current_page){
        $("#pagination-container").pagination({
            items: items,
            itemsOnPage: 10,
            currentPage: current_page,
            cssStyle: 'light-theme',
            hrefTextPrefix: "#page/"
        });
    },

    init_projects_dd: function(){
        var projects = new Projects();
        projects.fetch({
            success: function (projects) { 
                _.each(projects.models, function (project) {
                    //console.log(project.get("name"));
                    var proj_name = project.get("name");
                    $("#sel_proj").append( $('<option></option>').val(proj_name).html(proj_name) );
                });
            } 
        });
    },

    init_roles_dd: function(){
        _.each(this.roles, function (value, key) {
            //console.log(key);
            $("#sel_role").append( $('<option></option>').val(key).html(key) );
        }); 
    },

    init_ranks_dd: function(){
        _.each(this.ranks, function (value, key) {
            //console.log(key);
            $("#sel_rank").append( $('<option></option>').val(key).html(key) );
        });
    },

    get_max_products: function(){
        var virtu = new Virtu();
        var that = this;
        virtu.fetch().done(function(){
            that.max_products = virtu.get("max_products");
            resetSlider(that.max_products);
        });

    },

    doSearch: function(){
        //console.log("doSearch");
        var searchResults = new SearchResults();
        searchResults.options.page_num = this.options.page_num;
        
        //$("#search_results").html("<div id='currentViewSpinner'></div>");
        //spin = spinner("currentViewSpinner", 40, 75, 12, 10, '#888');

        that = this;
        searchResults.on(
            'reset',
            function(){
                numFound = searchResults.numFound;
                current_page = Number(that.options.page_num);
                console.log("NUM_FOUND="+numFound+"; CURRENT_PAGE="+current_page);
                that.setup_pagination(numFound, current_page);
            }
        );
        
        var searchResultsView = new SearchResultsView({collection:searchResults});
        searchResults.fetch(); //.done(function(){
        //$('#search_results').html(new SearchResultsView({collection:searchResults}));
        //});
        $('#search_results').html(searchResultsView.el);
    },
    
    render: function(){
        this.$el.html(this.template);       
       // return this.$el;
    }

});

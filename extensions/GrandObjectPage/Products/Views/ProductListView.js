ProductListView = Backbone.View.extend({

    productTag: null,
    table: null,

    initialize: function(){
        this.model.fetch();
        this.model.bind('partialSync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', function(start){ this.renderPartial(start); }, this);
        this.model.bind('sync', this.removeThrobber, this);
        this.template = _.template($('#product_list_template').html());
    },
    
    processData: function(start){
        // This method is purposely not using Backbone views for performance reasons
        var data = Array();
        var i = -1;
        _.each(this.model.toJSON(), function(model, index){
            i++;
            if(i < start){
                return;
            }
            var authors = Array();
            var projects = Array();
            var topProjects = Array();
            _.each(model.authors, function(author, aId){
                if(author.url != ''){
                    authors.push("<a href='" + author.url + "'>" + author.name + "</a>");
                }
                else{
                    authors.push(author.name);
                }
            });
            _.each(model.projects, function(project, aId){
                if(project.url != ''){
                    projects.push("<a href='" + project.url + "'>" + project.name + "</a>");
                }
                else{
                    projects.push(project.name);
                }
            });
            _.each(model.topProjects, function(project, aId){
                if(project.url != ''){
                    topProjects.push("<a href='" + project.url + "'>" + project.name + "</a>");
                }
                else{
                    topProjects.push(project.name);
                }
            });
            
            var ifranking = [];
            var impactFactor = (model.data["impact_factor_override"] != undefined && model.data["impact_factor_override"] != "") ? model.data["impact_factor_override"] : model.data["impact_factor"];
            var ranking = (model.data["category_ranking_override"] != undefined && model.data["category_ranking_override"] != "") ? model.data["category_ranking_override"] : model.data["category_ranking"];
            if(impactFactor != undefined && impactFactor != ""){
                ifranking.push("IF:" + impactFactor);
            }
            if(ranking != undefined && ranking != ""){
                ifranking.push("Ranking: " + ranking);
            }

            var row = new Array("<span style='white-space: nowrap;'>" + model.date + "</span>", 
                                "<span style='white-space: nowrap;'>" + model.type + "</span>",
                                "<span class='productTitle' data-id='" + model.id + "' data-href='" + model.url + "'>" + model.title + "</span><br />" + "<span style='float:right;'>" + ifranking.join('; ') + "</span>", "<div style='display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;overflow: hidden;'>" + authors.join(', ') + "</div>",
                                model.status);
            row.push(model.citation);
            if(networkName == "FES"){
                if(typeof model.data.collaboration != 'undefined'){
                    row.push(model.data.collaboration);
                }
                else{
                    row.push("");
                }
                if(typeof model.data.ucalgary != 'undefined'){
                    row.push(model.data.ucalgary);
                }
                else{
                    row.push("");
                }   
                if(typeof model.data.partner != 'undefined'){
                    row.push(model.data.partner);
                }
                else{
                    row.push("");
                }   
                if(typeof model.data.hqp != 'undefined'){
                    row.push(model.data.hqp);
                }
                else{
                    row.push("");
                }
                if(typeof model.data.published_in != 'undefined'){
                    row.push(model.data.published_in);
                }
                else{
                    row.push("");
                }
                if(typeof model.data.impact_factor != 'undefined'){
                    row.push(model.data.impact_factor);
                }
                else{
                    row.push("");
                }
            }
            row.push(_.values(_.mapObject(model.data, function(val, key){ return "<b>" + key + ":</b> " + val; })).join("\r"));
            if(projectsEnabled){
                row.push(projects.join(', '));
                if(_.contains(allowedRoles, STAFF)){
                    // Show top Projects if they are at least STAFF
                    row.push(topProjects.join(', '));
                }
            }
            data.push(row);
        }, this);
        return data;
    },
    
    removeThrobber: function(){
        this.$(".throbber").hide();
    },
    
    renderPartial: function(start){
        if(start == undefined){
            start = 0;
        }
        if(this.table != undefined){
            _.defer(function(){
                var data = this.processData(start);
                this.table.rows.add(data);
                this.table.draw();
            }.bind(this));
            return this.$el;
        }
        return this.render();
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        var templateData = {'url' : '', 'title' : ''};
        if(Backbone.history.fragment.indexOf('nonGrand') == -1){
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment + '/nonGrand';
            templateData.name = 'Non ' + main.get('title');
        }
        else{
            templateData.url = '../index.php/Special:Products#/' + Backbone.history.fragment.replace('/nonGrand', '');
            templateData.name = main.get('title').replace('Non ', '');
        }
        this.$el.html(this.template(templateData));
        var showButton = this.$("#showButton").detach();
        var throbber = this.$(".throbber").detach();
        var data = this.processData(0);
        var targets = [ 4, 5, 6 ];
        if(networkName == "FES"){
            targets = [4, 5, 6, 7, 8, 9, 10, 11, 12 ];
        }
        this.table = this.$('#listTable').DataTable({'iDisplayLength': 100,
	                                    'aaSorting': [[0,'desc'], [1,'asc']],
	                                    'autoWidth': false,
	                                    'aaData' : data,
	                                    'deferRender': true,
	                                    'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
	                                    'dom': 'Blfrtip',
	                                    'drawCallback': renderProductLinks,
	                                    "columnDefs": [
                                            {
                                                "targets": targets,
                                                "visible": false,
                                                "searchable": true
                                            }
                                        ],
                                        'buttons': [
                                            'excel', 'pdf'
                                        ]});
	    this.$("#listTable_length").append(showButton);
	    this.$("#listTable_length").append(throbber);
        this.$el.css('display', 'block');
        return this.$el;
    }

});

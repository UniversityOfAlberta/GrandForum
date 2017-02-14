SopsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    template: _.template($('#sops_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
       "click #update_button" : "updateSop", 
    },

    updateSop: function(){
	$.ajax({url: wgServer+wgScriptPath+"/index.php/index.php?action=api.updateSop&id="+this.model.id, 
	type:'GET',
	success: function(data){
		        location.reload();
	},
	error: function(){
                        location.reload();
	},
	});
    },

    render: function(){
	var i = this.model.toJSON();
        var mod = _.extend(this.model.toJSON());
        this.el.innerHTML = this.template(mod);
        for(m=0;m<i.annotations.length;m++){
            if(i.annotations[m].tags != null){
            	for(n=0;n<i.annotations[m].tags.length;n++){
                    var comment_column = "#span"+i.id;
		    if(m == i.annotations.length-1){
			$(comment_column).append(i.annotations[m].tags[n]);
			break;
		    }
                    $(comment_column).append(i.annotations[m].tags[n]+", ");
                }
            }
        }
        return this.$el;
    }
});

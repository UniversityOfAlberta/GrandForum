Story = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.story',

    defaults: {
        id: null,
        rev_id: "",
	user: "",
        title: "",
        story: "",
        date_submitted: "0000-00-00 00:00:00",
        approved: 0
    }
});

Stories = Backbone.Collection.extend({
    
   model: Story,

   url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.stories';
        }
        return 'index.php?action=api.stories/';
    }
 
});

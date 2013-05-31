SearchResultsView = Backbone.View.extend({
	tagName:'ul',
 	attributes: {
 		id: "people_list"
 	},

    people: [],

    initialize:function () {
        //this.collection.bind("request", this.init_spinner, this);
        this.collection.bind("reset", this.fetch_people, this);
        // that = this;
        // this.collection.fetch().done(function(){
        //     console.log("Collection fetched:");
        //     console.log(that.collection);
        //     that.people = that.fetch_people();
        //     //that.render();
        // });

    },

    fetch_people: function(){
        that = this;
        deferreds = [];
        this.people = [];
        _.each(this.collection.models, function (search_result) {
            person_id = search_result.get("user_id");
            person = new Person({id: person_id});

            fetch_req = person.fetch();
            deferreds.push(fetch_req);

            //fetch_req.done(function(){
                that.people.push(person);
            //});
        });    
        
        
        $.when(deferreds).done(function(){
        //     console.log("People fetched:");
        //     console.log(people);
        //     return people;
            that.render();
        });
        
    },

    render:function (eventName) {
    	var row_count = 0;
    	//this.fetch_people();
        _.each(this.people, function (person) {    	
			row_count++;
            var odd = "odd";
            if(row_count % 2 == 0){
                odd = "";
            }
            psw = new PersonCardView({model:person});
            psw.odd = odd;
			$(this.el).append(psw.render());        
        }, this);
        
        return this;
    }
});
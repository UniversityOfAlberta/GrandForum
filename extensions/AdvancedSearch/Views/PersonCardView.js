PersonCardView = Backbone.View.extend({
	//el: "#people_list",
    tagName: "li",
    className: "pscard",

    odd: "odd",
    
    initialize:function () {
        this.model.bind("change", this.render, this);
        this.model.getRoles().bind("sync", this.renderRoles, this);
        this.template = _.template($('#person_card_mid_template').html());
        //this.model.fetch();
        this.$el.css('display', 'none');
    },

    renderRoles: function(){
        //console.log("renderRoles");
        var current = this.model.roles.getCurrent();
        var that = this;
        this.model.roles.ready().then(function(){
            var roles = Array();
            if(current.models.length > 0){
                _.each(current.models, function(role, index){
                    roles.push(role.get('name'));
                }, that);
                $(that.el).find("#proles").html("(" + roles.join(', ') + ")");
            }
            //console.log(roles);
            that.$el.css('display', 'block');
        });
    },

    render: function (eventName) {
        
        pj = this.model.toJSON();
        if(pj.photo == ""){
            profile_photo = "/Photos/Empty.jpg";
        }else{
            profile_photo = pj.photo;
        }

        template_vars = {
            name: pj.reversedName,
            email: pj.email,
            profile_photo: profile_photo,
            profile_url: pj.url,
            university: pj.university,
            department: pj.department,
            position: pj.position,
            public_profile: pj.publicProfile.substring(0, 250),
            //odd: this.attributes.odd,
            //roles: roleNames
        };
        //console.log("render");
        $(this.el).addClass(this.odd);
        $(this.el).html(this.template(template_vars));
        
        return this.el;
    }
});
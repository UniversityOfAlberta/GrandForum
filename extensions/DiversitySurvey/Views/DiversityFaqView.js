DiversityFaqView = Backbone.View.extend({

    initialize: function(){
        this.model.once('sync', this.render, this);
        this.model.bind('change:language', this.render, this);
        this.template_en = _.template($('#faq_en_template').html());
        this.template_fr = _.template($('#faq_fr_template').html());
        this.model.fetch();
    },
    
    events: {
        
    },

    render: function(){
        if(this.model.get('language') == 'en' || this.model.get('language') == ''){
            main.set('title', networkName + " EDI Survey FAQs");
            this.$el.html(this.template_en(this.model.toJSON()));
        }
        else if (this.model.get('language') == 'fr'){
            main.set('title', "FAQ pour l'enquête sur l’EDI de " + networkName);
            this.$el.html(this.template_fr(this.model.toJSON()));
        }
        return this.$el;
    }

});

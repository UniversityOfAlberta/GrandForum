Journal = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.journal',
    
    defaults: function(){ return {
            id: null,
            year: '',
            short_title: '',
            iso_abbrev: '',
            title: '',
            issn: '',
            eissn: '',
            description: '',
            ranking_numerator: '',
            ranking_denominator: '',
            ratio: '',
            impact_factor: '',
            cited_half_life: '',
            eigenfactor: ''
        };
    }
    
});

Journals = Backbone.Collection.extend({
    model: Journal,
    
    search: null,

    //url: 'index.php?action=api.journal'

    url: function(){
        if (this.search != null){
            return 'index.php?action=api.journal/search/' + this.search;
        }
        return 'index.php?action=api.journal';
    }

});

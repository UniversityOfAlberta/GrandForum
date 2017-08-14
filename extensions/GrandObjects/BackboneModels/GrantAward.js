GrantAward = Backbone.Model.extend({

    initialize: function(){
        this.grant = new Grant();
        
        this.bind("change:grant_id", function(){
            this.grant.set('id', this.get('grant_id'));
        });
        
        this.bind("sync", function(){
            var partners = new Array();
            _.each(this.get('partners'), function(partner){
                var p = new GrantPartner(partner);
                partners.push(p);
            });
            this.set('partners', partners);
        });
    },

    urlRoot: 'index.php?action=api.grantaward',
    
    getGrant: function(){
        this.grant.fetch();
        return this.grant;
    },
    
    defaults: function(){ 
        return {
            id: null,
            user_id: '',
            grant_id: 0,
            cle: '',
            department: '',
            institution: '',
            province: '',
            country: '',
            fiscal_year: '',
            competition_year: '',
            amount: '',
            program_id: '',
            program_name: '',
            group: '',
            committee_name: '',
            area_of_application_group: '',
            area_of_application: '',
            research_subject_group: '',
            installment: '',
            partie: '',
            nb_partie: '',
            application_title: '',
            keyword: '',
            application_summary: '',
            coapplicants: '',
            partners: new Array()
        };
    }
    
});

GrantAwards = Backbone.Collection.extend({
    model: GrantAward,
    
    fetch: function(options) {
        if(_.isFunction(this.url)){
            this.temp = [];
            this.fetchChunk(0, 1000); // Fetch 1000 at a time
        }
        else if(_.isArray(this.url)){
            this.temp = [];
            this.fetchMultiple(0, 100); // Fetch 100 at a time
        }
        else{
            return Backbone.Collection.prototype.fetch.call(this, options);
        }
    },
    
    fetchMultiple: function(start, count){
        var rest = _.first(_.rest(this.url, start+1), count);
        var url = this.url[0] + '-1,' + rest.join(',');
        var self = this;
        $.get(url, function(data) {
            self.temp = self.temp.concat(data);
            if(_.size(data) == count){
                // There's probably more, so keep calling
                self.fetchMultiple(start + count, count);
            }
            else{
                // Done fetching
                self.reset(self.temp);
                self.trigger('sync');
            }
        });
    },
    
    fetchChunk: function(start, count){
        var url = this.url() + '/' + start + '/' + count;
        var self = this;
        $.get(url, function(data) {
            if(_.size(data) == count){
                // There's probably more, so keep calling
                self.add(data, {silent: true});
                self.trigger('partialSync', start, count);
                self.fetchChunk(start + count, count);
            }
            else{
                // Done fetching
                self.add(data, {silent: true});
                self.trigger('sync', start, count);
            }
        });
    },
    
    url: function(){
        return 'index.php?action=api.grantaward';
    }
});

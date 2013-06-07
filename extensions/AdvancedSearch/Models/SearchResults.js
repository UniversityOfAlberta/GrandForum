SearchResult = Backbone.Model.extend({
	defaults: {
		'entity': "",
		'p_count': 0, 
		'score': 0,
		'u_conn': 0,
		'u_exp': 0,
		'u_prod': 0,
		'u_stat': 0,
		'user_id': ""
		}
});

SearchResults = Backbone.Collection.extend({

	model: SearchResult,
	
	urlRoot: 'https://forum.grand-nce.ca:8990/solr/select',
	
    numFound: 0,
	
    options: {
	  page_num: 1
	},

	sync: function(){
		var rows = 10;
		var page = this.options.page_num-1;
        //console.log("SYNC page="+this.options.page_num);
		var start = page * rows;
		var params = {
				'wt': 'json',
				'json.wrf': 'parseSolrResponse',
				'fl': 'score,*',
				'defType': 'edismax',
				'bf': 'u_exp^20.0',
				'start': start,
				'rows': rows
		};
		var user_query = encodeURIComponent($("#t_query").val());
		if (user_query == ''){
			user_query = "*:*";
		}

		var urlRoot = this.urlRoot;
		var fq = ['entity:users'];
		var facets = this.compile_facets();
		var prod_lim = product_limit;
		if(facets){
			fq.push(facets);
		}

		if(prod_lim){
			fq.push(prod_lim);
		}
		
		$.extend(params, {'q': user_query});
		$.extend(params, {'fq': fq});

		sr = this;
		
		return $.ajax({
			url: urlRoot, 
			data: params,
			type: "GET",
			dataType: 'jsonp',
			jsonp: false, 
			jsonpCallback: "parseSolrResponse",
			traditional: true,
			success: function(data){
                sr.numFound = data.response.numFound;
				sr.reset(data.response.docs);
			}
		});
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

		var roles={"BOD":90,"Manager":80,"Champion":70,"RMC":70,"PNI":60,"CNI":50,"Associated Researcher":40,"HQP":40,"Staff":30};
		var ranks={"VP Research":90,"Associate Dean of Research":85,"Associate Dean of Student Affairs":85,"Director":80,"Canada Research Chair":80,"Professor":70,"Associate Professor":60,"Assistant Professor":50,"PostDoc":40,"PhD Student":30,"Industry Associate":25,"Masters Student":20,"Technician":15,"Undergraduate":10,"Other":0,"Unknown":0,"":0};

		// ROLE
		this.do_modifiers(facets, roles, 'sel_role', 'user_role');

		// RANKS
		this.do_modifiers(facets, ranks, 'sel_rank', 'user_rank');

		// PROJECTS
		if ($('#sel_proj').val() != 'any') 
			facets.push('proj_abbr:' + $('#sel_proj').val());

		//alert("facets: " + facets.length + "   " + facets.join(" AND "));

		if (facets.length > 0)
			return '(' + facets.join(" AND ") + ')';
		
		return '';
	},

});
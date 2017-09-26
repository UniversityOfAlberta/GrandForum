SopsView = Backbone.View.extend({

    table: null,
    sops: null,
    editDialog: null,
    lastTimeout: null,

    initialize: function(){
        this.template = _.template($('#sops_template').html());
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
    },
    
    addRows: function(){
        if(this.table != undefined){
            this.table.destroy();
        }
        this.sops.each($.proxy(function(p, i){
            var row = new SopsRowView({model: p, parent: this});
            this.$("#sopRows").append(row.$el);
            row.render();
        }, this));
        this.createDataTable();
    },
    
    createDataTable: function(){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'bFilter': true,
                                                     'autoWidth': false,
                                                     'aLengthMenu': [[-1], ['All']]});
        this.table.draw();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
    },

    events: {
	"keyup .filter_option": "filterAll",
        "change .filter_option" : "filterAll",
	"click input[type=checkbox]": "filterAll",
	"click #filterMeOnly": "filterMineOnly",
	"click #clearFiltersButton" : "clearFilters",

    },

    filterAll: function(){
	console.log('filtered');
	this.showAllRows();
	this.filterDegreeName();
	this.filterInstitutionName();
	this.filterGPA();
	this.filterAnatomyType();
	this.filterStatsType();
	this.filterAdmitType();
	this.filterFinalAdmitType();
	this.filterByTags();
	this.filterByNationality();
    },

    checkFilters: function(){
	var found = false;
	for (var i = 0; i < $('.filter_option').not(".check_tags").size(); i++){
	    if($('.filter_option').not(".check_tags")[i].value != ""){
		found = true;
	    }
	}
        for (var i = 0; i < $('.check_tags').size(); i++){
            if($(".check_tags")[i].checked != false){
                found = true;
            }
        }
	return found;
    },

    showAllRows: function(){
	$("#listTable > tbody > tr").show();
    },

    clearFilters: function(){
	$('.filter_option').val("");
	$('.filter_option').prop('checked', false);
        this.showAllRows();
    },

    filterByRow: function(row,input){
	if(input){
            $('#listTable > tbody > tr').each(function(){
                if(!($(this).find('td').eq(row).text().toUpperCase().indexOf(input) > -1)){
                        $(this).hide();
                }
            });
        }
    },

    filterByRowExact: function(row,input){
        if(input){
            $('#listTable > tbody > tr').each(function(){
                if(!($(this).find('td').eq(row).text().toUpperCase() == (input))){
                        $(this).hide();
                }
            });
        }
    },

    filterByRange: function(index, min, max){
        if(min){
            $('#listTable > tbody > tr').each(function(){
		console.log(parseFloat($(this).find('td').eq(index).text())>= min);
                if(!(parseFloat($(this).find('td').eq(index).text()) >= min && parseFloat($(this).find('td').eq(index).text()) <= max)){
                        $(this).hide();
                }
            });
        }
    },

    filterStudentName: function(){
	input = $('#nameInput').val().toUpperCase();
	this.filterByRow(0,input);
    },

    filterDegreeName: function(){
        input = $('#degreeInput').val().toUpperCase();
        this.filterByRow(8,input);
    },

    filterInstitutionName: function(){
        input = $('#InstitutionNameInput').val().toUpperCase();
        this.filterByRow(8,input);
    },

    filterReviewerName: function(){
        input = $('#reviewerNameInput').val().toUpperCase();
        this.filterByRow(11,input);
    },

    filterGPA: function(){
        var minInput = $('#referenceNameInputMin').val();
        var maxInput = $('#referenceNameInputMax').val();
        if (!minInput){
            minInput = 0;
        }
        if (!maxInput){
            maxInput = 4;
        }
	this.filterByRange(2,minInput,maxInput);
    },

    filterAnatomyType: function(){
        input = $('#anatomyType').val().toUpperCase();
        this.filterByRow(6,input);
    },

    filterStatsType: function(){
        input = $('#statsType').val().toUpperCase();
        this.filterByRow(7,input);
    },

    filterNationalType: function(){
        input = $('#nationalType').val().toUpperCase();
        this.filterByRow(9,input);
    },

    filterAdmitType: function(){
        input = $('#admitType').val().toUpperCase();
        this.filterByRow(12,input);
    },

    filterFinalAdmitType: function(){
        input = $('#finalAdmitType').val().toUpperCase();
        this.filterByRow(14,input);
    },

    filterByTags: function(){
            $('#listTable > tbody > tr').each(function(){
		var show = false;
                var tags = $(this).find('td').eq(13).text().replace(/<\/?[^>]+(>|$)/g, "").split(",");
		for(j = 0; j < tags.length; j++){
		    var tag = tags[j].replace(/\s/g, '').replace('//','').toLowerCase();
		    if($('#'+tag).is(':checked')){
			show = true;
			break;	
		    }
		}
		if($('#filterByTags').is(':checked')){
		    if(!show){
		    	$(this).hide();
		    }
		}
            });
   },

    filterMineOnly: function(){
	    var input = me.get('fullName').toUpperCase();
		console.log("hello");
                if($('#filterMeOnly').is(':checked')){
	            this.filterByRow(11,input);
                }
   },

    filterByNationality: function(){
            $('#listTable > tbody > tr').each(function(){
                var show = false;
                var tags = $(this).find('td').eq(9).text().split(",");
		console.log(tags);
                for(j = 0; j < tags.length; j++){
                    var tag = tags[j].replace(/\s/g, '').replace('//','').toLowerCase();
                    if($('#'+tag).is(':checked')){
                        show = true;
                        break;
                    }
                }
                if($('#indigenous').is(':checked') || $('#canadian').is(':checked') || $('#saskatchewan').is(':checked') || $('#international').is(':checked')){
                    if(!show){
                        $(this).hide();
                    }
                }
            });
   },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
	console.log(this.model);
        this.addRows();
                $(document).ready(function () {
                    $("#showfilter").click(function () {
                        if ($(this).data('name') == 'show') {
                            $("#filters").animate({
                            }).hide();
                            $(this).data('name', 'hide');
                            $(this).val('Show Filter Options');
                        } else {
                            $("#filters").animate({
                            }).show();
                            $(this).data('name', 'show')
                            $(this).val('Hide Filter Options');
                        }
                    });
                });
        return this.$el;
    }
});

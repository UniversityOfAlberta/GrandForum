SopsView = Backbone.View.extend({

    table: null,
    sops: null,
    editDialog: null,
    lastTimeout: null,
    expanded: false,
    expanded2: false,
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
                                                     'dom': 'Bfrtip',
                                                     'buttons': [
                                                        {
                                                            extend: 'colvis',
                                                            className: 'btn btn-primary',
                                                            text: 'Column Visibility'
                                                        }
                                                     ],
                                                     'aLengthMenu': [[-1], ['All']]});
        this.table.draw();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
    },

    events: {
        "keyup .filter_option": "reloadTable",
        "change .filter_option" : "reloadTable",
        "click input[type=checkbox]": "reloadTable",
        "click #clearFiltersButton" : "clearFilters",
        "click #filterMeOnly": "reloadTable",
        "click #selectTagBox" : "showCheckboxes",
        "click #showfilter" : "showFilter",
    },

    reloadTable: function(){
    this.table.draw();
    },

    showFilter: function(){
        if ($(this).data('name') == 'show') {
            $("#filters").animate().hide();
            $(this).data('name', 'hide');
            $(this).val('Show Filter Options');
        } else {
            $("#filters").animate().show();
            $(this).data('name', 'show')
            $(this).val('Hide Filter Options');
        }
    },

    showCheckboxes: function(){
        var checkboxes = document.getElementById("checkboxes");
        if (!this.expanded) {
            checkboxes.style.display = "block";
            checkboxes.style.position = "absolute";
            checkboxes.style.background="white";
            this.expanded = true;
        } else {
            checkboxes.style.display = "none";
            this.expanded = false;
        }
    },

    clearFilters: function(){
    $('.filter_option').val("");
    $('.filter_option').prop('checked', false);
    this.reloadTable();
    },

    filterCitizenship: function(settings,data,dataIndex){
        var input = $('#countryOfCitizenshipInput').val().toUpperCase();
        var name = data[4];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterDepartmentName: function(settings,data,dataIndex){
        var input = $('#DepartmentInput').val().toUpperCase();
        var name = data[7];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterGPA: function(settings,data,dataIndex){
        var min = parseFloat($('#referenceNameInputMin').val(),0);
        var max = parseFloat($('#referenceNameInputMax').val(),0);
        var gpa = parseFloat( data[12] ) || 0; // use column 2
    //check if gpa inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && gpa <= max ) ||
             ( min <= gpa   && isNaN( max ) ) ||
             ( min <= gpa   && gpa <= max ) )
        {
            return true;
        }
        return false;
    },

    filterReviewType: function(settings,data,dataIndex){
        var input = $('#statusType').val().toUpperCase();
        var name = data[2];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },


    filterAdmitType: function(settings,data,dataIndex){
        var input = $('#admitType').val().toUpperCase();
        var name = data[18];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterFinalAdmitType: function(settings,data,dataIndex){
        var input = $('#finalAdmitType').val().toUpperCase();
        var name = data[21];
        if(name != undefined){
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        }
        else{
	    return true;
        }
        return false;
    },

    filterByTags: function(settings,data,dataIndex){
        var tags = data[20].replace(/<\/?[^>]+(>|$)/g, "").split(",");
        if($('#filterByTags').is(':checked')){
            for(j = 0; j < tags.length; j++){
                var tag = tags[j].replace(/\s/g, '').replace('//','').toLowerCase();
                if($('#'+tag).is(':checked')){
                    return true;
                }
            }
            return false;
        }
        return true;
   },

    filterMineOnly: function(settings,data,dataIndex){
        var input = me.get('fullName').toUpperCase();
        if($('#filterMeOnly').is(':checked')){
            var name = data[17];
            if(name.toUpperCase().indexOf(input) > -1){
                return true;
            }
        return false;
        }
    return true;
   },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        $.fn.dataTable.ext.search.push(
            this.filterGPA,
            this.filterReviewType,
            this.filterAdmitType,
            this.filterFinalAdmitType,
            this.filterMineOnly,
            this.filterByTags,
            this.filterCitizenship,
            this.filterDepartmentName,
        );
        return this.$el;
    }
});

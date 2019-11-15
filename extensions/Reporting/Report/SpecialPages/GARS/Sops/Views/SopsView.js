SopsView = Backbone.View.extend({

    table: null,
    sops: null,
    editDialog: null,
    lastTimeout: null,
    expanded: false,
    expanded2: false,

    initialize: function(){
        this.template = _.template($('#sops_template').html());
        $(this).data('name', 'show');
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
        setInterval(function () {
            var pad = $('#bodyContent').css('padding-left');
            $('#filter-pane').css('margin-left', parseInt(pad)-16);
        }, 16);
    },
    
    renderRoles: function(){
        if(me.roleString.get('roleString').indexOf('Manager') !== -1 || me.roleString.get('roleString').indexOf('Admin') !== -1 || me.roleString.get('roleString').indexOf('Chair') !== -1){
            $('.assign_button').css('visibility','visible');
        }
        else{
            $('#filterMeOnly').prop("checked", true);
            this.reloadTable();
            $('#mineOnly').css('visibility', 'hidden');
            

        }
    },


    addRows: function(){
        if(this.table != undefined){
            this.table.destroy();
        }
        this.sops.each(function(p, i){
            var row = new SopsRowView({model: p, parent: this});
            this.$("#sopRows").append(row.$el);
            row.render();
        }.bind(this));
        this.createDataTable();
    },
    
    createDataTable: function(){
        var buttonDownload = {
            exportOptions: {
                format: {
                    body: function ( data, row, column, node ) {
                        var ret;
                        data = $('<div>' + data + '</div>').text();
                        // add a newline after each education entry
                        if ((column === 6)) {
                            ret = data.replace(/(\))([a-zA-Z])/g, '$1\n$2');
                        // Clean up Reviewers/Faculty columns for downloaded excel/csv
                        } else if ((column === 17) || (column === 19)) {
                            ret = data.replace(/\s\s+/g, '\n');
                            ret = ret.replace(/\+/g, "");
                        // Clean up Notes column for downloaded excel/csv
                        } else if (column === 21) {
                            ret = data.replace(/\s\s+/g, '\n');
                            ret = ret.replace(/\+/g, "");
                            ret = ret.replace(/[\n\r].*Additional Notesclose\s*([^\n\r]*)/, "");
                        } else {
                            ret = data;
                        }
                        return ret;
                    }
                }
            }
        };
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'bFilter': true,
                                                     'dom': 'Bfrtip',
                                                     'autoWidth': true,
                                                     'scrollX': true,
                                                     'fixedColumns':   
                                                        {
                                                            leftColumns: 2
                                                        },
                                                     'columns': [
                                                        { 'width': '95px' }, // lastname
                                                        { 'width': '95px' }, // firstname
                                                        { 'width': '200px' }, // email
                                                        { 'width': '95px' },  // GSMS ID
                                                        { 'width': '95px' },  // SID
                                                        { 'width': '5px' },  // UserID
                                                        { 'width': '100px' },  // Country
                                                        { 'width': '200px' },  // Degree
                                                        { 'width': '75px' },  // Nationality Notes
                                                        { 'width': '30px' },  // GPA 60
                                                        { 'width': '40px' },  // GPA / credits
                                                        { 'width': '40px' },  // GPA / credits
                                                        { 'width': '70px' },  // Anatomy
                                                        { 'width': '70px' },  // Stats
                                                        { 'width': '70px' },  // CASPER
                                                        { 'width': '200px' },  // Reviewers
                                                        { 'width': '200px' },  // Reviewer Decision
                                                        { 'width': '200px' },  // Notes
                                                        //{ 'width': '120px' },  // Comments
                                                        { 'width': '120px' },  // Decision
                                                      ],
                                                     'buttons': [
                                                        {
                                                            extend: 'colvis',
                                                            className: 'btn btn-primary',
                                                            text: 'Column Visibility'
                                                        },
                                                        $.extend( true, {}, buttonDownload, {
                                                            extend: 'csv',
                                                            text: 'CSV',
                                                            title: 'CSGARS_Overview_Table'
                                                        } ),
                                                        $.extend( true, {}, buttonDownload, {
                                                            extend: 'excel',
                                                            text: 'Excel',
                                                            title: 'CSGARS_Overview_Table'
                                                        } )
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
        "click #nationalityBox" : "showNationalityBoxes",
        "click #selectTagBox" : "showCheckboxes",
        "click #showfilter" : "showFilter",
        "click #hidefilter" : "showFilter",
        "change #year": "changeYear"
    },

    reloadTable: function(){
    this.table.draw();
    },

    showFilter: function(){
        if ($(this).data('name') == 'show') {
            $('#filter-pane').stop().animate({left: -5 }, 300, 'swing');
            $('#bodyContent').stop().animate({left: 330 }, 300, 'swing');
            //$("#filters").animate().hide();
            $(this).data('name', 'hide');
            $('#showfilter').attr('value', 'Hide Filter Options');
        } else {
            $('#filter-pane').stop().animate({left: -365 }, 300, 'swing');
            $('#bodyContent').stop().animate({left: 0 }, 300, 'swing');
            //$("#filters").animate().show();
            $(this).data('name', 'show')
            $('#showfilter').attr('value', 'Show Filter Options');
        }
        
    },

    showNationalityBoxes: function(){
        var checkboxes = document.getElementById("nationboxes");
        if (!this.expanded2) {
            checkboxes.style.display = "block";
            checkboxes.style.position = "absolute";
            checkboxes.style.background="white";
            this.expanded2 = true;
        } else { 
            checkboxes.style.display = "none";
            this.expanded2 = false;
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
        var name = data[6];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterDegreeName: function(settings,data,dataIndex){
        var input = $('#degreeInput').val().toUpperCase();
        var name = data[7];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterInstitutionName: function(settings,data,dataIndex){
        var input = $('#InstitutionNameInput').val().toUpperCase();
        var name = data[7];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterGPA: function(settings,data,dataIndex){
        var min = parseFloat($('#referenceNameInputMin').val(),0);
        var max = parseFloat($('#referenceNameInputMax').val(),0);
        var gpa = parseFloat( data[9] ) || 0; // use column 11
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

    filterAnatomyType: function(settings,data,dataIndex){
        var input = $('#anatomyType').val().toUpperCase();
        var name = data[12];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterStatsType: function(settings,data,dataIndex){
        var input = $('#statsType').val().toUpperCase();
        var name = data[13];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterAdmitType: function(settings,data,dataIndex){
        var input = $('#admitType').val().toUpperCase();
        var name = data[16];
                if(name.toUpperCase().indexOf(input) > -1){
                        return true;
                }
        return false;
    },

    filterReviewerStatus: function(settings,data,dataIndex){
	var input = $('#reviewerStatus').val().toUpperCase();
	var reviewer = data[15];
                if(input == "REVIEWED"){
		    if(reviewer.toUpperCase().indexOf("DONE") > -1) {
			return true;
		    }
                }
                else if(input == "NOTREVIEWED"){
                    if(reviewer.toUpperCase().indexOf("DONE") <= -1) {   
                        return true;
                    }
                }
                else if(input == "--"){
                   return true;

                }
         
	return false;
    },

    filterFinalAdmitType: function(settings,data,dataIndex){
        var input = $('#finalAdmitType').val().toUpperCase();
        var name = data[19];
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
        var tags = data[13].replace(/<\/?[^>]+(>|$)/g, "").split(",");
        if($('#filterByTags').is(':checked')){
            for(j = 0; j < tags.length; j++){
                var tag = tags[j].replace(/\s/g, '').replace('//','').toLowerCase();
        if($('#'+tag).is(':checked')){
                    return true;
        }
                return false;
            }
        }
        return true;
   },

    filterMineOnly: function(settings,data,dataIndex){
        var input = me.get('fullName').toUpperCase();
        if($('#filterMeOnly').is(':checked')){
            var name = data[15];
            if(name.toUpperCase().indexOf(input) > -1){
                return true;
            }
        return false;
        }
    return true;
   },

    filterByNationality: function(settings,data,dataIndex){
        var tags = data[9].split(",");
        if($('#indigenous').is(':checked') || $('#canadian').is(':checked') || $('#saskatchewan').is(':checked') || $('#international').is(':checked')){
            for(j = 0; j < tags.length; j++){
                var tag = tags[j].replace(/\s/g, '').replace('//','').toLowerCase();
                if($('#'+tag).is(':checked')){
                    return true;
                }
                return false;
            }
        }
        return true;
   },

   filterBirthday: function(settings,data,dataIndex){
        var birthday = new Date(data[3]);
        var operator = $('#filterDoBSpan').find(":selected").text();
        var filterdate = $('#filterDoB').datepicker('getDate');

        var operation = {
            '--':     function(a, b) { return true; },
            'before': function(a, b) { if(filterdate){return a < b;} else {return true;} },
            'after':  function(a, b) { if(filterdate){return a > b;} else {return true;} }
        };

        return operation[operator](birthday, filterdate);
    },
    
    changeYear: function(){
        var year = this.$("#year").val();
        var frag = Backbone.history.fragment.split("/")[0];
        document.location = wgServer + wgScriptPath + '/index.php/Special:Sops#/' + year;
    },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        me.getRoleString().bind('sync', this.renderRoles, this);
        $.fn.dataTable.ext.search.push(
            this.filterGPA,
            this.filterCitizenship,
            this.filterDegreeName,
            this.filterInstitutionName,
            this.filterAnatomyType,
            this.filterStatsType,
            this.filterAdmitType,
            this.filterFinalAdmitType,
            this.filterMineOnly,
            this.filterByTags,
            this.filterByNationality,
            this.filterReviewerStatus,
        );
        this.$("#filterDoB").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:-18",
            defaultDate: "-18y"
        });
        return this.$el;
    }
});

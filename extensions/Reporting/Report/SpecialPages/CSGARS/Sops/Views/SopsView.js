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
        this.listenToOnce(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
        setInterval(function () {
            var pad = $('#bodyContent').css('padding-left');
            $('#filter-pane').css('margin-left', parseInt(pad)-16);
        }, 16);
    },

    renderRoles: function(){
        var rolestring = me.roleString.get('roleString');
        //console.log(rolestring);
        if(rolestring.indexOf('Manager') !== -1 || rolestring.indexOf('Admin') !== -1){
            //console.log("HI");
            $('.assign_button').css('display','inline');
            this.table.draw();
        }
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
                                                            leftColumns: 1
                                                        },
                                                     'columns': [
                                                        { 'width': '225px' }, // User email gender
                                                        { 'width': '95px' },  // GSMS ID
                                                        { 'width': '55px' },  // Folder
                                                        { 'width': '70px' },  // DoB
                                                        { 'width': '70px' },  // Country
                                                        { 'width': '70px' },  // Applicant Type
                                                        { 'width': '140px' }, // Education history
                                                        { 'width': '70px' },  // Program Name
                                                        { 'width': '70px' },  // EPL
                                                        { 'width': '110px' },  // Areas
                                                        { 'width': '75px' },  // Supervisors
                                                        { 'width': '80px' },  // Scholarships Held/Applied
                                                        { 'width': '75px' },  // GPA Normalized
                                                        { 'width': '70px' },  // GRE
                                                        { 'width': '70px' },  // Number of Publications
                                                        { 'width': '70px' },  // Awards
                                                        { 'width': '110px' },  // Courses
                                                        { 'width': '120px' },  // Reviewers
                                                        { 'width': '70px' },  // Avg Rev Rank
                                                        { 'width': '120px' },  // Faculty
                                                        { 'width': '70px' },  // Avg Faculty Rank
                                                        { 'width': '120px' },  // Notes
                                                        { 'width': '70px' },  // Comments
                                                        { 'width': '70px' }   // Decision
                                                      ],
                                                     'buttons': [
                                                        {
                                                            extend: 'colvis',
                                                            className: 'btn btn-primary',
                                                            text: 'Column Visibility',
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
                                                        } )/*,
                                                        {
                                                            extend: 'pdf',
                                                            className: 'btn btn-primary',
                                                            text: 'PDF'
                                                        }*/
                                                     ], 
                                                     'aLengthMenu': [[-1], ['All']]});
        this.table.draw();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        //this.$('#listTable_length').prepend("<div id='download_btns' style='margin-left:112px; margin-top: 6px; vertical-align:baseline;'>Download: <a class='buttons-csv buttons-html5'>CSV</a>, <a class='buttons-excel buttons-html5'>Excel</a></div>")
    },

    events: {
        "keyup .filter_option": "reloadTable",
        "change .filter_option" : "reloadTable",
        "click input[type=checkbox]": "reloadTable",
        "click #clearFiltersButton" : "clearFilters",
        "click #filterMeOnly": "reloadTable",
        "click #selectTagBox" : "showCheckboxes",
        "click #showfilter" : "showFilter",
        "click #hidefilter" : "showFilter",
        "click #selectAreasBox" : "showAreasCheckboxes",
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

    showAreasCheckboxes: function(){
        var checkboxes = document.getElementById("areasCheckboxes");
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
        $('.filter_option').trigger("chosen:updated");
        this.reloadTable();
    },

    filterCitizenship: function(settings,data,dataIndex){
        var filtercountry = $("#filterSelectCountry").chosen().val();
        var studentcountry = data[4];
        if (filtercountry != null) {
            if ($.inArray(studentcountry, filtercountry) == -1) {
                return false;
            }
        }
        return true;
    },

    filterProgramName: function(settings,data,dataIndex){
        var filterprograms = $("#filterSelectProgramName").chosen().val();
        var studentprogram = data[7];
        if (filterprograms != null) {
            for (var i = 0; i < filterprograms.length; ++i) {
                if (studentprogram.indexOf(filterprograms[i]) > -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
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

    filterFolder: function(settings,data,dataIndex){
        var input = $('#filterSelectFolder').chosen().val();
        var folder = data[2];
        if (input != null) {
            for (var i = 0; i < input.length; ++i) {
                if (folder.indexOf(input[i]) > -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
    },

    filterDecision: function(settings,data,dataIndex){
        var input = $('#filterSelectDecision').chosen().val();
        var decision = data[23];
        if (input != null) {
            for (var i = 0; i < input.length; ++i) {
                if (input[i] == decision) {
                    return true;
                }
            }
            return false;
        }
        return true;
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

   filterByAreasOfInterest: function(settings,data,dataIndex){
        var filterSelected= $("#filterSelectAoI").chosen().val();
        var aois = data[9].split(", ");
        if (filterSelected != null) {
            for (var i = 0; i < filterSelected.length; ++i) {
                if ($.inArray(filterSelected[i], aois) != -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
   },

   filterSupervisors: function(settings,data,dataIndex){
        var filtersupervisors = $("#filterSelectSupervisors").chosen().val();
        var studentsupervisors = data[10].split(", ");
        if (filtersupervisors != null) {
            for (var i = 0; i < filtersupervisors.length; ++i) {
                if ($.inArray(filtersupervisors[i], studentsupervisors) != -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
   },

   filterReviewers: function(settings,data,dataIndex){
        var filterreviewers = $("#filterSelectReviewers").chosen().val();
        var reviewers = data[17];
        if (filterreviewers != null) {
            for (var i = 0; i < filterreviewers.length; ++i) {
                if (reviewers.indexOf(filterreviewers[i]) != -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
   },

   filterNumPubs: function(settings,data,dataIndex){
        var min = parseFloat($('#numPubsInputMin').val(),0);
        var max = parseFloat($('#numPubsInputMax').val(),0);
        var pubs = parseFloat( data[14] ) || 0; // use column 14
        //check if num pubs inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && pubs <= max ) ||
             ( min <= pubs   && isNaN( max ) ) ||
             ( min <= pubs   && pubs <= max ) )
        {
            return true;
        }
        return false;
    },

    filterNumAwards: function(settings,data,dataIndex){
        var min = parseFloat($('#numAwardsInputMin').val(),0);
        var max = parseFloat($('#numAwardsInputMax').val(),0);
        var awards = parseFloat( data[15] ) || 0; // use column 15
        //check if num awards inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && awards <= max ) ||
             ( min <= awards   && isNaN( max ) ) ||
             ( min <= awards   && awards <= max ) )
        {
            return true;
        }
        return false;
    },

    filterScholHeld: function(settings,data,dataIndex){
        var values = data[11].split('/')[0].split(", ");

        var options = {};
        options["NSERC"] = $('#heldNSERC')[0].checked;
        options["AITF"] = $('#heldAITF')[0].checked;
        options["Vanier"] = $('#heldVanier')[0].checked;

        if (options["NSERC"] && ($.inArray("NSERC", values) != -1)) {
            return true;
        }
        if (options["AITF"] && ($.inArray("AITF", values) != -1)) {
            return true;
        }
        if (options["Vanier"] && ($.inArray("Vanier", values) != -1)) {
            return true;
        }
        if (options["NSERC"] || options["AITF"] || options["Vanier"]) {
            return false;
        }
        return true;
    }, 

    filterScholApplied: function(settings,data,dataIndex){
        var values = data[11].split('/')[1].split(", ");

        var options = {};
        options["NSERC"] = $('#appliedNSERC')[0].checked;
        options["AITF"] = $('#appliedAITF')[0].checked;
        options["Vanier"] = $('#appliedVanier')[0].checked;

        if (options["NSERC"] && ($.inArray("NSERC", values) != -1)) {
            return true;
        }
        if (options["AITF"] && ($.inArray("AITF", values) != -1)) {
            return true;
        }
        if (options["Vanier"] && ($.inArray("Vanier", values) != -1)) {
            return true;
        }
        if (options["NSERC"] || options["AITF"] || options["Vanier"]) {
            return false;
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

    filterGREVerbal: function(settings,data,dataIndex){
        var min = parseFloat($('#filterValGreVerbalMin').val(),0);
        var max = parseFloat($('#filterValGreVerbalMax').val(),0);
        var gre = parseFloat( data[13].split(", ")[0] ) || 0;
        //check if gre inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && gre <= max ) ||
             ( min <= gre   && isNaN( max ) ) ||
             ( min <= gre   && gre <= max ) )
        {
            return true;
        }
        return false;
    },

    filterGREQuantitative: function(settings,data,dataIndex){
        var min = parseFloat($('#filterValGreQuantMin').val(),0);
        var max = parseFloat($('#filterValGreQuantMax').val(),0);
        var gre = parseFloat( data[13].split(", ")[1] ) || 0;
        //check if gre inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && gre <= max ) ||
             ( min <= gre   && isNaN( max ) ) ||
             ( min <= gre   && gre <= max ) )
        {
            return true;
        }
        return false;
    },

    filterGREAnalytical: function(settings,data,dataIndex){
        var min = parseFloat($('#filterValGreAnalyticalMin').val(),0);
        var max = parseFloat($('#filterValGreAnalyticalMax').val(),0);
        var gre = parseFloat( data[13].split(", ")[2] ) || 0;
        //check if gre inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && gre <= max ) ||
             ( min <= gre   && isNaN( max ) ) ||
             ( min <= gre   && gre <= max ) )
        {
            return true;
        }
        return false;
    },

    filterGRECS: function(settings,data,dataIndex){
        var min = parseFloat($('#filterValGreCSMin').val(),0);
        var max = parseFloat($('#filterValGreCSMax').val(),0);
        var gre = parseFloat( data[13].split(", ")[3] ) || 0;
        //check if gre inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && gre <= max ) ||
             ( min <= gre   && isNaN( max ) ) ||
             ( min <= gre   && gre <= max ) )
        {
            return true;
        }
        return false;
    },

    filterCourses: function(settings,data,dataIndex){
        var input = $('#filterCourses').val().toUpperCase();
        var courses = data[16];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
    },

    filterNotes: function(settings,data,dataIndex){
        var input = $('#filterNotes').val().toUpperCase();
        var courses = data[19];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
    },

    filterComments: function(settings,data,dataIndex){
        var input = $('#filterComments').val().toUpperCase();
        var courses = data[20];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
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
        me.getRoleString().bind('sync', this.renderRoles, this);
        $.fn.dataTable.ext.search.push(
            this.filterGPA,
            this.filterFolder,
            this.filterDecision,
            this.filterMineOnly,
            this.filterByTags,
            this.filterCitizenship,
            this.filterProgramName,
            this.filterNumPubs,
            this.filterNumAwards,
            this.filterScholHeld,
            this.filterScholApplied,
            this.filterBirthday,
            this.filterGREVerbal,
            this.filterGREQuantitative,
            this.filterGREAnalytical,
            this.filterGRECS,
            this.filterCourses,
            this.filterNotes,
            this.filterComments,
            this.filterByAreasOfInterest,
            this.filterSupervisors,
            this.filterReviewers,
        );
        this.$("#filterDoB").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:-18",
            defaultDate: "-18y"
        });
        this.$('#filterSelectCountry').chosen({ placeholder_text_multiple: 'Select Country' });
        this.$('#filterSelectFolder').chosen({ placeholder_text_multiple: 'Select Folder' });
        this.$('#filterSelectDecision').chosen({ placeholder_text_multiple: 'Select Decision' });
        this.$('#filterSelectProgramName').chosen({ placeholder_text_multiple: 'Select Program Name' });
        this.$('#filterSelectSupervisors').chosen({ placeholder_text_multiple: 'Select Supervisor(s)' });
        this.$('#filterSelectAoI').chosen({ placeholder_text_multiple: 'Select Area(s) of Interest' });
        this.$('#filterSelectReviewers').chosen({ placeholder_text_multiple: 'Select Reviewers' });
            
        return this.$el;
    }
});

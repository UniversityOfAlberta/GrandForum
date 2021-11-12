SopsView = Backbone.View.extend({

    table: null,
    sops: null,
    editDialog: null,
    lastTimeout: null,
    expanded: false,
    expanded2: false,
    filtersSelected: null,
    hidden: false,
    favorites: false,
    defaultSearch: "",
    scrollValue: 0,
    
    initialize: function() {
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

        var storedPrefs = JSON.parse(localStorage.getItem("USERPREFS"));
        var globalPrefs = SopsView.filtersSelected;
        if (storedPrefs == null) {
            localStorage.setItem("USERPREFS", JSON.stringify(globalPrefs));
        } else if (Object.keys(storedPrefs).length < Object.keys(globalPrefs).length) {
            var newPrefs = _.defaults(storedPrefs, globalPrefs);
            localStorage.setItem("USERPREFS", JSON.stringify(newPrefs));
        }
        this.getUserPrefs();
        
        $(window).resize(function(){
            this.$('div.dataTables_scrollBody').css('max-height', $(window).height() - 
                                                       this.$('#tableContainer').offset().top - 
                                                       this.$('#listTable_length').outerHeight() - 
                                                       this.$('.dataTables_scrollHead').outerHeight() - 
                                                       this.$('#listTable_info').outerHeight() -
                                                       $('#footer').outerHeight());
            this.table.draw();
        }.bind(this));
    },

    updateUserPrefs: function() {
        localStorage.setItem("USERPREFS", JSON.stringify(SopsView.filtersSelected));
    },

    getUserPrefs: function() {
        SopsView.filtersSelected = JSON.parse(localStorage.getItem("USERPREFS"));
        return SopsView.filtersSelected;
    },

    renderRoles: function(){
        var rolestring = me.roleString.get('roleString');
        if(rolestring.indexOf('Manager') !== -1 || rolestring.indexOf('Admin') !== -1){
            this.$('.assign_button').css('display','inline');
        }
    },
    
    addRows: function(){
        this.$('#listTable').hide();
        if(this.table != undefined){
            this.table.destroy();
        }
        
        // Filter the Sops
        var sops = new Sops(this.sops.filter(function(sop) {
            return (sop.get('hidden') == this.hidden);
        }.bind(this)));
        if(this.favorites){
            sops = new Sops(sops.filter(function(sop) {
                return (sop.get('favorited') == this.favorites);
            }.bind(this)));
        }
        
        // Render the SopsRows
        var fragment = document.createDocumentFragment();
        sops.each(function(p, i){
            var row = new SopsRowView({model: p, parent: this});
            row.render();
            fragment.appendChild(row.el);
        }.bind(this));
        this.$("#sopRows").html(fragment);
        
        // Create the DataTable
        this.createDataTable();
        
        // Show the DataTable
        this.$('#listTable').show();
        this.$('.dataTables_scrollHead table').show();
        this.$('.DTFC_LeftHeadWrapper table').show();
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
        var invisibleColumns = [];
        var colvis = SopsView.filtersSelected['colvis']
        for (i = 0; i < colvis.length; i++) {
            if (colvis[i] == false) {
                invisibleColumns.push(i);
            }
        }
        // Create the DataTable
        this.table = this.$('#listTable').DataTable({'oSearch': {'sSearch': this.defaultSearch},
                                                     'bFilter': true,
                                                     'dom': 'Bfrtip',
                                                     'autoWidth': true,
                                                     'deferRender': true,
                                                     'scrollX': true,
                                                     'scrollY': screen.height, // Essentially the max height of the table
                                                     'scrollCollapse': true,
                                                     'scroller': {
                                                        loadingIndicator: true,
                                                        rowHeight: 159,
                                                        displayBuffer: 2
                                                     },
                                                     'fixedColumns':   
                                                     {
                                                        leftColumns: 1
                                                     },
                                                     'columnDefs': [
                                                        { 'visible': false, 'targets': invisibleColumns }
                                                      ],
                                                     'columns': [
                                                        { 'width': '250px' }, // User email gender
                                                        { 'width': '115px' }, // GSMS ID
                                                        { 'width': '30px' },  // GSMS PDF
                                                        { 'width': '55px' },  // Folder
                                                        { 'width': '70px' },  // DoB
                                                        { 'width': '140px' }, // Country
                                                        { 'width': '70px' },  // Applicant Type
                                                        { 'width': '140px' }, // Education history
                                                        { 'width': '140px' }, // Country of Degrees
                                                        { 'width': '70px' },  // Program Name
                                                        { 'width': '70px' },  // EPL
                                                        { 'width': '100px' }, // UofA References
                                                        { 'width': '110px' }, // Areas
                                                        { 'width': '85px' },  // Supervisors
                                                        { 'width': '80px' },  // Scholarships Held/Applied
                                                        { 'width': '75px' },  // GPA Normalized
                                                        { 'width': '70px' },  // GRE
                                                        { 'width': '70px' },  // Number of Publications
                                                        { 'width': '70px' },  // Awards
                                                        { 'width': '110px' }, // Courses
                                                        { 'width': '70px' }, // Submitted
                                                        { 'width': '150px' }, // Reviewers
                                                        { 'width': '70px' },  // Avg Rev Rank
                                                        { 'width': '150px' }, // Faculty
                                                        { 'width': '70px' },  // Avg Faculty Rank
                                                        { 'width': '120px' }, // Notes
                                                        { 'width': '85px' },  // Willing to Supervise
                                                        { 'width': '85px' },  // Supervisor
                                                        { 'width': '70px' },  // Comments
                                                        { 'width': '70px' },  // Decision
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
                                                        } )
                                                     ], 
                                                     'drawCallback': function() {
                                                        this.renderRoles();
                                                        if (SopsView.filtersSelected.filterMenuOpen == true) {
                                                            // Move the filter menu back out
                                                            $('#bodyContent').css('left', 330);
                                                            $('#filter-pane').css('left', -5);
                                                            $('#showfilter').attr('value', 'Hide Filter Options');
                                                        }
                                                        // This is for keeping filter options across overview table types
                                                        SopsView.filtersSelected.filterSelectProgramName = this.filterSelectProgramName.chosen({ placeholder_text_multiple: 'Select Program Name' }).val();
                                                        SopsView.filtersSelected.filterSelectCountry = this.filterSelectCountry.chosen({ placeholder_text_multiple: 'Select Country'}).val();
                                                        SopsView.filtersSelected.filterSelectDecision = this.filterSelectDecision.chosen({ placeholder_text_multiple: 'Select Decision' }).val();
                                                        SopsView.filtersSelected.filterSelectAoI = this.filterSelectAoI.chosen({ placeholder_text_multiple: 'Select Area(s) of Interest' }).val();
                                                        SopsView.filtersSelected.filterSelectSupervisors = this.filterSelectSupervisors.chosen({ placeholder_text_multiple: 'Select Supervisor(s)' }).val();
                                                        SopsView.filtersSelected.filterSelectReviewers = this.filterSelectReviewers.chosen({ placeholder_text_multiple: 'Select Reviewers' }).val();
                                                        SopsView.filtersSelected.referenceGPAInputMin = this.referenceGPAInputMin.val();
                                                        SopsView.filtersSelected.referenceGPAInputMax = this.referenceGPAInputMax.val();
                                                        SopsView.filtersSelected.filterDoB = this.filterDoB.val();
                                                        SopsView.filtersSelected.filterGend = this.filterGend.val();
                                                        SopsView.filtersSelected.filterSubmitted = this.filterSubmitted.val();
                                                        SopsView.filtersSelected.filterValEPLScoreMin = this.filterValEPLScoreMin.val();
                                                        SopsView.filtersSelected.filterValEPLScoreMax = this.filterValEPLScoreMax.val();
                                                        SopsView.filtersSelected.filterValGreVerbalMin = this.filterValGreVerbalMin.val();
                                                        SopsView.filtersSelected.filterValGreVerbalMax = this.filterValGreVerbalMax.val();
                                                        SopsView.filtersSelected.filterValGreQuantMin = this.filterValGreQuantMin.val();
                                                        SopsView.filtersSelected.filterValGreQuantMax = this.filterValGreQuantMax.val();
                                                        SopsView.filtersSelected.filterValGreAnalyticalMin = this.filterValGreAnalyticalMin.val();
                                                        SopsView.filtersSelected.filterValGreAnalyticalMax = this.filterValGreAnalyticalMax.val();
                                                        SopsView.filtersSelected.filterValGreCSMax = this.filterValGreCSMax.val();
                                                        SopsView.filtersSelected.filterValGreCSMin = this.filterValGreCSMin.val();
                                                        SopsView.filtersSelected.filterCoursesEl = this.filterCoursesEl.val();
                                                        SopsView.filtersSelected.filterNotesEl = this.filterNotesEl.val();
                                                        SopsView.filtersSelected.filterCommentsEl = this.filterCommentsEl.val();
                                                        SopsView.filtersSelected.filterMeOnly = this.filterMeOnly.prop("checked");
                                                        SopsView.filtersSelected.numPubsInputMin = this.numPubsInputMin.val();
                                                        SopsView.filtersSelected.numPubsInputMax = this.numPubsInputMax.val();
                                                        SopsView.filtersSelected.numAwardsInputMin = this.numAwardsInputMin.val();
                                                        SopsView.filtersSelected.numAwardsInputMax = this.numAwardsInputMax.val();
                                                        SopsView.filtersSelected.heldNSERC = this.heldNSERC.prop("checked");
                                                        SopsView.filtersSelected.heldVanier = this.heldVanier.prop("checked");
                                                        SopsView.filtersSelected.heldAITF = this.heldAITF.prop("checked");
                                                        SopsView.filtersSelected.appliedVanier = this.appliedVanier.prop("checked");
                                                        SopsView.filtersSelected.appliedAITF = this.appliedAITF.prop("checked");
                                                        SopsView.filtersSelected.appliedNSERC = this.appliedNSERC.prop("checked");
                                                        SopsView.filtersSelected.filterDoBSpan = this.filterDoBSpan.val();
                                                        SopsView.filtersSelected.filterSubmittedSpan = this.filterSubmittedSpan.val();
                                                        SopsView.filtersSelected.filterSelectEPLTest = this.filterSelectEPLTest.val();
                                                     }.bind(this)
                                                 });
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        table = this.table;
    },

    events: {
        "keyup .filter_option": "reloadTable",
        "change .filter_option" : "reloadTable",
        "click input[type=checkbox]:not([name=hidden]):not([name=favorited])": "reloadTable",
        "click #clearFiltersButton" : "clearFilters",
        "click #filterMeOnly": "reloadTable",
        "click #selectTagBox" : "showCheckboxes",
        "click #showfilter" : "showFilter",
        "click #hidefilter" : "showFilter",
        "click #selectAreasBox" : "showAreasCheckboxes",
        "click .buttons-colvis" : "recordColVis",
        "change #year": "changeYear"
    },

    reloadTable: function(){
        $(window).trigger('resize'); // Ends up calling table.draw()
        this.updateUserPrefs();
    },

    showFilter: function(){
        if (!SopsView.filtersSelected.filterMenuOpen) {
            SopsView.filtersSelected.filterMenuOpen = true;
            $('#filter-pane').stop().animate({left: -5 }, 300, 'swing');
            $('#bodyContent').stop().animate({left: 330 }, 300, 'swing');
            $('#showfilter').attr('value', 'Hide Filter Options');
            // The filter menu is showing now.
        } else {
            SopsView.filtersSelected.filterMenuOpen = false;
            $('#filter-pane').stop().animate({left: -365 }, 300, 'swing');
            $('#bodyContent').stop().animate({left: 0 }, 300, 'swing');
            $('#showfilter').attr('value', 'Show Filter Options');
            // The filter menu is hidden now
        }
        this.updateUserPrefs();
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
        var filtercountry = this.filterSelectCountry.chosen();
        var value = filtercountry.val();
        var studentcountry = data[5];
        if (!_.isEmpty(value)) {
            return _.reduce(value, function(memo, val){ return (memo || (studentcountry.indexOf(val) !== -1)); }, false);
        }
        return true;
    },

    filterProgramName: function(settings,data,dataIndex){
        var filterprograms = this.filterSelectProgramName.chosen().val();
        var studentprogram = data[9];
        if (!_.isEmpty(filterprograms)) {
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
        var min = parseFloat(this.referenceGPAInputMin.val(),0);
        var max = parseFloat(this.referenceGPAInputMax.val(),0);
        var gpa = parseFloat( data[15] ) || 0; // use column 2
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

    /*filterFolder: function(settings,data,dataIndex){
        var input = $('#filterSelectFolder').chosen().val();
        var folder = data[3];
        if (!_.isEmpty(input)) {
            for (var i = 0; i < input.length; ++i) {
                if (folder.indexOf(input[i]) > -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
    },*/

    filterDecision: function(settings,data,dataIndex){
        var input = this.filterSelectDecision.chosen().val();
        var decision = data[27];
        if (!_.isEmpty(input)) {
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
        var tags = data[24].replace(/<\/?[^>]+(>|$)/g, "").split(",");
        if(this.filterByTagsEl.is(':checked')){
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
        var filterSelected= this.filterSelectAoI.chosen().val();
        var aois = data[12].split(", ");
        if (!_.isEmpty(filterSelected)) {
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
        var filtersupervisors = this.filterSelectSupervisors.chosen().val();
        var studentsupervisors = unaccentChars(data[13]).trim();
        if (!_.isEmpty(filtersupervisors)) {
            for (var i = 0; i < filtersupervisors.length; ++i) {
                var sup = unaccentChars(filtersupervisors[i]);
                if(studentsupervisors.trim().indexOf(sup) != -1){
                    return true;
                }
            }
            return false;
        }
        return true;
   },

   filterReviewers: function(settings,data,dataIndex){
        var filterreviewers = this.filterSelectReviewers.chosen().val();
        var reviewers = unaccentChars(data[21]);
        if (!_.isEmpty(filterreviewers)) {
            for (var i = 0; i < filterreviewers.length; ++i) {
                if (reviewers.indexOf(unaccentChars(filterreviewers[i])) != -1) {
                    return true;
                }
            }
            return false;
        }
        return true;
   },

   filterNumPubs: function(settings,data,dataIndex){
        var min = parseFloat(this.numPubsInputMin.val(),0);
        var max = parseFloat(this.numPubsInputMax.val(),0);
        var pubs = parseFloat( data[17] ) || 0; // use column 14
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
        var min = parseFloat(this.numAwardsInputMin.val(),0);
        var max = parseFloat(this.numAwardsInputMax.val(),0);
        var awards = parseFloat( data[18] ) || 0; // use column 15
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
        var values = data[14].split('/')[0].split(", ");

        var options = {};
        options["NSERC"] = this.heldNSERC[0].checked;
        options["AITF"] = this.heldAITF[0].checked;
        options["Vanier"] = this.heldVanier[0].checked;

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
        var values = data[14].split('/')[1].split(", ");

        var options = {};
        options["NSERC"] = this.appliedNSERC[0].checked;
        options["AITF"] = this.appliedAITF[0].checked;
        options["Vanier"] = this.appliedVanier[0].checked;

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
        var birthday = new Date(data[4]);
        var operator = this.filterDoBSpan.find(":selected").text();
        var filterdate = this.filterDoB.datepicker('getDate');

        var operation = {
            '--':     function(a, b) { return true; },
            'before': function(a, b) { if(filterdate){return a < b;} else {return true;} },
            'after':  function(a, b) { if(filterdate){return a > b;} else {return true;} }
        };

        return operation[operator](birthday, filterdate);
    },
    
    filterGender: function(settings,data,dataIndex){
        var cell = data[0];
        return (cell.indexOf(")" + this.filterGend.val()) != -1);
    },
    
    filterSub: function(settings,data,dataIndex){
        var birthday = new Date(data[20]);
        var operator = this.filterSubmittedSpan.find(":selected").text();
        var filterdate = this.filterSubmitted.datepicker('getDate');

        var operation = {
            '--':     function(a, b) { return true; },
            'before': function(a, b) { if(filterdate){return a < b;} else {return true;} },
            'after':  function(a, b) { if(filterdate){return a > b;} else {return true;} }
        };

        return operation[operator](birthday, filterdate);
    },

    filterEPLTest: function(settings,data,dataIndex){
        var selectedTest = this.filterSelectEPLTest.val();
        if (selectedTest == '') {
            return true;
        }
        var userEPL = data[10].split(" ");
        if (userEPL[0] == selectedTest) {
            return true;
        } else {
            return false;
        }
    },

    filterEPLScore: function(settings,data,dataIndex){
        var min = parseFloat(this.filterValEPLScoreMin.val(), 0);
        var max = parseFloat(this.filterValEPLScoreMax.val(), 0);
        var score = parseFloat(data[10].split(" ")[1]) || 0;
        //check if score inbetween min-max
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && score <= max ) ||
             ( min <= score && isNaN( max ) ) ||
             ( min <= score && score <= max ))
        {
            return true;
        }
        return false;
    },

    filterGREVerbal: function(settings,data,dataIndex){
        var min = parseFloat(this.filterValGreVerbalMin.val(),0);
        var max = parseFloat(this.filterValGreVerbalMax.val(),0);
        var gre = parseFloat( data[16].split(", ")[0] ) || 0;
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
        var min = parseFloat(this.filterValGreQuantMin.val(),0);
        var max = parseFloat(this.filterValGreQuantMax.val(),0);
        var gre = parseFloat( data[16].split(", ")[1] ) || 0;
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
        var min = parseFloat(this.filterValGreAnalyticalMin.val(),0);
        var max = parseFloat(this.filterValGreAnalyticalMax.val(),0);
        var gre = parseFloat( data[16].split(", ")[2] ) || 0;
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
        var min = parseFloat(this.filterValGreCSMin.val(),0);
        var max = parseFloat(this.filterValGreCSMax.val(),0);
        var gre = parseFloat( data[16].split(", ")[3] ) || 0;
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
        var input = this.filterCoursesEl.val().toUpperCase();
        var courses = data[19];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
    },

    filterNotes: function(settings,data,dataIndex){
        var input = this.filterNotesEl.val().toUpperCase();
        var courses = data[23];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
    },

    filterComments: function(settings,data,dataIndex){
        var input = this.filterCommentsEl.val().toUpperCase();
        var courses = data[24];
        if(courses.toUpperCase().indexOf(input) > -1){
                return true;
        }
        return false;
    },

    filterMineOnly: function(settings,data,dataIndex){
        var input = me.get('fullName').toUpperCase();
        if(this.filterMeOnly.is(':checked')){
            var name = data[21];
            if(name.toUpperCase().indexOf(input) > -1){
                return true;
            }
            return false;
        }
        return true;
    },

    recordColVis: function() {
        var that = this;
        $('.buttons-columnVisibility').click(function () {
            // Get the index of the column button just clicked
            var i = 0;
            var elem = this;
            while (elem.previousSibling != null) {
                elem = elem.previousSibling;
                ++i;
            }
            SopsView.filtersSelected['colvis'][i] = this.classList.contains("active");
            that.updateUserPrefs();
        });
    },
    
    changeYear: function(){
        var year = this.$("#year").val();
        var frag = Backbone.history.fragment.split("/")[0];
        if(frag == ""){
            frag = "reviewInProgress";
        }
        document.location = wgServer + wgScriptPath + '/index.php/Special:Sops#/' + frag + "//" + year;
    },

    render: function(){
        this.$el.html(this.template());
        
        this.filterSelectCountry = this.$('#filterSelectCountry');
        this.filterSelectProgramName = this.$('#filterSelectProgramName');
        this.referenceGPAInputMin = this.$('#referenceGPAInputMin');
        this.referenceGPAInputMax = this.$('#referenceGPAInputMax');
        this.filterSelectDecision = this.$('#filterSelectDecision');
        this.filterByTagsEl = this.$('#filterByTags');
        this.filterSelectAoI = this.$('#filterSelectAoI');
        this.filterSelectSupervisors = this.$('#filterSelectSupervisors');
        this.filterSelectReviewers = this.$('#filterSelectReviewers');
        this.numPubsInputMin = this.$('#numPubsInputMin');
        this.numPubsInputMax = this.$('#numPubsInputMax');
        this.numAwardsInputMin = this.$('#numAwardsInputMin');
        this.numAwardsInputMax = this.$('#numAwardsInputMax');
        this.heldVanier = this.$('#heldVanier');
        this.heldAITF = this.$('#heldAITF');
        this.heldNSERC = this.$('#heldNSERC');
        this.appliedVanier = this.$('#appliedVanier');
        this.appliedAITF = this.$('#appliedAITF');
        this.appliedNSERC = this.$('#appliedNSERC');
        this.filterDoB = this.$('#filterDoB');
        this.filterDoBSpan = this.$('#filterDoBSpan');
        this.filterGend = this.$('#filterGend');
        this.filterSubmitted = this.$('#filterSubmitted');
        this.filterSubmittedSpan = this.$('#filterSubmittedSpan');
        this.filterSelectEPLTest = this.$('#filterSelectEPLTest');
        this.filterValEPLScoreMin = this.$('#filterValEPLScoreMin');
        this.filterValEPLScoreMax = this.$('#filterValEPLScoreMax');
        this.filterValGreVerbalMin = this.$('#filterValGreVerbalMin');
        this.filterValGreVerbalMax = this.$('#filterValGreVerbalMax');
        this.filterValGreQuantMin = this.$('#filterValGreQuantMin');
        this.filterValGreQuantMax = this.$('#filterValGreQuantMax');
        this.filterValGreAnalyticalMin = this.$('#filterValGreAnalyticalMin');
        this.filterValGreAnalyticalMax = this.$('#filterValGreAnalyticalMax');
        this.filterValGreCSMax = this.$('#filterValGreCSMax');
        this.filterValGreCSMin = this.$('#filterValGreCSMin');
        this.filterCoursesEl = this.$('#filterCourses');
        this.filterNotesEl = this.$('#filterNotes');
        this.filterCommentsEl = this.$('#filterComments');
        this.filterMeOnly = this.$('#filterMeOnly');
        
        var fnChosen = function (variable) {
            if (SopsView.filtersSelected[variable] != null) {
                var field = this[variable].chosen();
                field.val(SopsView.filtersSelected[variable]);
                field.trigger("chosen:updated");
            }
        }.bind(this);
        var fnField = function (variable) {
            if (SopsView.filtersSelected[variable] != null) {
                var field = this[variable].val(SopsView.filtersSelected[variable]);
            }
        }.bind(this);
        var fnCheckbox = function (variable) {
            if (SopsView.filtersSelected[variable]) {
                this[variable].prop("checked", true);
            }
        }.bind(this);

        // This is for keeping filter options across overview table types
        fnChosen('filterSelectCountry');
        fnChosen('filterSelectProgramName');
        fnChosen('filterSelectDecision');
        fnChosen('filterSelectAoI');
        fnChosen('filterSelectSupervisors');
        fnChosen('filterSelectReviewers');

        fnField('referenceGPAInputMin');
        fnField('referenceGPAInputMax');
        fnField('filterDoB');
        fnField('filterGend');
        fnField('filterSubmitted');
        fnField('filterValEPLScoreMin');
        fnField('filterValEPLScoreMax');
        fnField('filterValGreVerbalMin');
        fnField('filterValGreVerbalMax');
        fnField('filterValGreQuantMin');
        fnField('filterValGreQuantMax');
        fnField('filterValGreAnalyticalMin');
        fnField('filterValGreAnalyticalMax');
        fnField('filterValGreCSMax');
        fnField('filterValGreCSMin');
        fnField('filterCoursesEl');
        fnField('filterNotesEl');
        fnField('filterCommentsEl');
        //fnField('filterMeOnly');
        fnField('numPubsInputMin');
        fnField('numPubsInputMax');
        fnField('numAwardsInputMin');
        fnField('numAwardsInputMax');

        fnCheckbox('heldNSERC');
        fnCheckbox('heldVanier');
        fnCheckbox('heldAITF');
        fnCheckbox('appliedVanier');
        fnCheckbox('appliedAITF');
        fnCheckbox('appliedNSERC');
        fnCheckbox('filterMeOnly');

        fnField('filterDoBSpan');
        fnField('filterSubmittedSpan');
        fnField('filterSelectEPLTest');

        this.addRows();
        var roleString = me.getRoleString();
        this.listenToOnce(roleString, 'sync', function(){
            $(window).trigger('resize'); // Ends up calling table.draw()
        });
        $.fn.dataTable.ext.search = new Array();
        $.fn.dataTable.ext.search.push(
            this.filterGPA.bind(this),
            //this.filterFolder.bind(this),
            this.filterDecision.bind(this),
            this.filterMineOnly.bind(this),
            this.filterByTags.bind(this),
            this.filterProgramName.bind(this),
            this.filterCitizenship.bind(this),
            this.filterNumPubs.bind(this),
            this.filterNumAwards.bind(this),
            this.filterScholHeld.bind(this),
            this.filterScholApplied.bind(this),
            this.filterBirthday.bind(this),
            this.filterGender.bind(this),
            this.filterSub.bind(this),
            this.filterGREVerbal.bind(this),
            this.filterGREQuantitative.bind(this),
            this.filterGREAnalytical.bind(this),
            this.filterGRECS.bind(this),
            this.filterCourses.bind(this),
            this.filterNotes.bind(this),
            this.filterComments.bind(this),
            this.filterByAreasOfInterest.bind(this),
            this.filterSupervisors.bind(this),
            this.filterReviewers.bind(this),
            this.filterEPLTest.bind(this),
            this.filterEPLScore.bind(this)
        );
        this.$("#filterDoB").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:-18",
            defaultDate: "-18y"
        });
        this.$("#filterSubmitted").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
        return this.$el;
    }
});

SopsView.filtersSelected = {
    filterMenuOpen: false, //
    filterSelectCountry: null, //
    filterSelectProgramName: null, //
    referenceGPAInputMin: null,
    referenceGPAInputMax: null,
    filterSelectDecision: null, //
    filterByTagsEl: null,
    filterSelectAoI: null, //
    filterSelectSupervisors: null, //
    filterSelectReviewers: null, //
    numPubsInputMin: null,
    numPubsInputMax: null,
    numAwardsInputMin: null,
    numAwardsInputMax: null,
    heldVanier: null,
    heldAITF: null,
    heldNSERC: false,
    appliedVanier: null,
    appliedAITF: null,
    appliedNSERC: null,
    filterDoB: null,
    filterDoBSpan: null,
    filterGend: null,
    filterSubmitted: null,
    filterSubmittedSpan: null,
    filterSelectEPLTest: null,
    filterValEPLScoreMin: null,
    filterValEPLScoreMax: null,
    filterValGreVerbalMin: null,
    filterValGreVerbalMax: null,
    filterValGreQuantMin: null,
    filterValGreQuantMax: null,
    filterValGreAnalyticalMin: null,
    filterValGreAnalyticalMax: null,
    filterValGreCSMax: null,
    filterValGreCSMin: null,
    filterCoursesEl: null,
    filterNotesEl: null,
    filterCommentsEl: null,
    filterMeOnly: null,
    colvis: [
        true,true,true,true,true,
        true,true,true,true,true,
        true,true,true,true,true,
        true,true,true,true,true,
        true,true,true,true,true,
    ]
};

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
        var rolestring = me.roleString.get('roleString');
        console.log(rolestring);
        if(rolestring.indexOf('Manager') !== -1 || rolestring.indexOf('Admin') !== -1){
           console.log("HI");
            $('.assign_button').css('visibility','visible');
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
        "click #hidefilter" : "showFilter",
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

        if (options["NSERC"] && (jQuery.inArray("NSERC", values) == -1)) {
            return false;
        }
        if (options["AITF"] && (jQuery.inArray("AITF", values) == -1)) {
            return false;
        }
        if (options["Vanier"] && (jQuery.inArray("Vanier", values) == -1)) {
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

        if (options["NSERC"] && (jQuery.inArray("NSERC", values) == -1)) {
            return false;
        }
        if (options["AITF"] && (jQuery.inArray("AITF", values) == -1)) {
            return false;
        }
        if (options["Vanier"] && (jQuery.inArray("Vanier", values) == -1)) {
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
            'before': function(a, b) { return a < b; },
            'after':  function(a, b) { return a > b; }
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
            this.filterReviewType,
            this.filterAdmitType,
            this.filterFinalAdmitType,
            this.filterMineOnly,
            this.filterByTags,
            this.filterCitizenship,
            this.filterDepartmentName,
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

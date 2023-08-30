CRMProjectContactsTableView = CRMContactsTableView.extend({

    projectId: -1,

    initialize: function(options){
        this.projectId = "" + options.projectId + "";
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_project_contacts_table_template').html());
    },
    
    initTable: function(){
        // Initialize order/filter
        var searchStr = "";
        var order = [1, "asc"];
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
        }
        var rowsGroup = [1,0,2,3,4];
        if(this.groupBy != null){
            rowsGroup = [this.groupBy].concat(rowsGroup);
        }
        this.$("table#contacts thead tr")
            .clone(true)
            .addClass('filters')
            .appendTo('table#contacts thead');
        this.table = this.$("table#contacts").DataTable({
            "autoWidth": true,
            'bPaginate': false,
            'iDisplayLength': -1,
            'order': [[ 1, "asc" ]],
            'aLengthMenu': [[-1], ['All']],
            'orderCellsTop': true,
            'rowsGroup': rowsGroup,
            'dom': 'Blfrtip',
            'buttons': [
                {
                    extend: 'excel',
                    text: 'Excel',
                    exportOptions: {
                        format: {
                            body: function (html, row, col, node) {
                                var html = $("<div>" + html + "</div>");
                                $("span", html).remove();
                                $("br", html).remove();
                                return $(html).text().trim().replaceAll("\n", "");
                            }
                        }
                    }
                }
            ],
            initComplete: function () {
                var api = this.api();
                // For each column
                api
                    .columns()
                    .eq(0)
                    .each(function (colIdx) {
                        // Set the header cell to contain the input element
                        var cell = $('.filters th').eq(
                            $(api.column(colIdx).header()).index()
                        );
                        var title = $(cell).text();
                        $(cell).html('<input type="text" placeholder="' + title + '" style="width:100%;" />');
     
                        // On every keypress in this input
                        $(
                            'input',
                            $('.filters th').eq($(api.column(colIdx).header()).index())
                        )
                            .off('keyup change')
                            .on('change', function (e) {
                                // Get the search value
                                $(this).attr('title', $(this).val());
     
                                cursorPosition = this.selectionStart;
                                // Search the column for that value
                                api
                                    .column(colIdx)
                                    .search(this.value, false, false)
                                    .draw();
                            })
                            .on('keyup', function (e) {
                                e.stopPropagation();
     
                                $(this).trigger('change');
                                $(this)
                                    .focus()[0]
                                    .setSelectionRange(cursorPosition, cursorPosition);
                            });
                    });
            }
        });
        table = this.table;
        this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.table.on('order.dt', function(e, el, ord){
            if(ord.length == 1){
                this.groupBy = ord[0].src;
                this.render();
            }
            else{
                this.groupBy = null;
            }
        }.bind(this));
        this.$('#contacts_wrapper').prepend("<div id='contacts_length' class='dataTables_length'></div>");
	    this.$("#contacts_length").empty();
	    this.$("#contacts_length").append(this.$("#addContact").detach());
	    this.$("#contacts_length").append(this.$(".dt-buttons button").detach());
	    this.$(".dt-buttons").remove();
	    
	    this.$("#addContact").css("margin-right", "5px");
    },

});

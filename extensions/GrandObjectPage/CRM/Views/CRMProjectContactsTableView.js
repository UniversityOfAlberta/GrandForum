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
        this.table = this.$("table#contacts").DataTable({
            "autoWidth": true,
            'bPaginate': false,
            'iDisplayLength': -1,
            'order': [[ 1, "asc" ]],
            'aLengthMenu': [[-1], ['All']],
            'rowsGroup': rowsGroup
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
    },

});

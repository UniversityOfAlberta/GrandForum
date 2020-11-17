/* Table */
if($("#hqpTable").length > 0){
    $('#hqpTable').DataTable({
        aLengthMenu: [
            [25, 50, 100, 200, -1],
            [25, 50, 100, 200, 'All']
        ],
        iDisplayLength: -1
    });
    $('select[name=term]').change(function(){
        document.location = wgServer + wgScriptPath + '/index.php/Special:GradDB?term=' + $('select[name=term]').val();
    });
}

/* Form */
if($("#hqpTable").length == 0){
    var template = $('#supervisors tbody tr').first().detach();

    $("#bodyContent > h1").html("<a href='" + wgServer + wgScriptPath + "/index.php/Special:GradDB'>Grad DB</a> &gt; Make a Contract");

    var allPeople = new People();
    allPeople.roles = ['HQP'];
    allPeople.simple = true;
    allPeople.fetch();

    $("#hqp").append("<option></option>");
    $("#hqp").chosen({width: "400px"});
    $("#hqp").trigger("chosen:activate");
    xhr = null;
    var changeFn = function(e){
        _.defer(function(){
            if(e.keyCode == 37 ||
               e.keyCode == 38 ||
               e.keyCode == 39 ||
               e.keyCode == 40 ||
               e.keyCode == 13){
                // Arrows/Enter key
                return;  
            }
            if(xhr != null){
                xhr.abort();
            }
            var searchStr = $("#hqp_chosen .chosen-search input").val();
            if(searchStr == ""){
                // Don't search if empty string
                return;
            }
            var url = wgServer + wgScriptPath + "/index.php?action=api.globalSearch/people/" + escape(searchStr).replace(/\//g, ' ');
            xhr = $.get(url, function(data){
                if($("#hqp_chosen .chosen-search input").val() != searchStr){
                    // The value in the search box has changed, retry the ajax request
                    $("#hqp_chosen .chosen-search input").trigger("change");
                    return;
                }
                var results = data.results;
                var people = new Array("<option></option>");
                _.each(this.allPeople.sortBy('reversedName'), function(p){
                    if(_.contains(results, parseInt(p.get('id')))){
                        var fullname = p.get('reversedName');
                        if(p.get('email') != ""){
                            fullname += " (" + p.get('email').split('@')[0] + ")";
                        }
                        people.push("<option value='" + p.get('id') + "'>" + fullname + "</option>");
                    }
                }.bind(this));
                $("#hqp").html(people.join());
                $("#hqp").trigger("chosen:updated");
                $("#hqp_chosen .chosen-search input").val(searchStr);
            }.bind(this));
        }.bind(this));
    }.bind(this);
    $("#hqp_chosen .chosen-search input").keyup(changeFn)
                                                          .change(changeFn)
                                                          .on("paste", changeFn);

    function initSupervisors(){
        var scale = {award: 0, salary: 0};
        var parent = $('#supervisors tbody tr').last();
        
        $('select#hqp').change(function(){
            $.get('index.php?action=api.graddbfinancial/' + $('select#hqp').val() + '/2020', function(response){
                scale = response;
                $('select[name=\"hours[]\"]', parent).change();
                $('select[name=\"type[]\"]', parent).change();
            });
        }).change();
        
        $('select[name=\"account[]\"]', parent).chosen();
        $('input[name=\"award[]\"]', parent).forceNumeric({min: 0, max: 9999999999999999});
        
        $('select[name=\"hours[]\"]', parent).change(function(){
            var percent = parseInt($(this).val())/12;
            $('span.award', parent).text('$' + Math.round(scale.award*percent));
            $('span.salary', parent).text('$' + Math.round(scale.salary*percent));
            $('span.total', parent).text('$' + Math.round(parseInt(scale.award*percent) + (scale.salary*percent)));
        }).change();
        
        $('.removeSupervisor', parent).click(function(){
            $(this).closest('tr').remove();
        });
        
        $('input[name=\"award[]\"]', parent).change(function(){
            var val = $(this).val();
            $('span.total', parent).text('$' + val);
        });
        
        $('select[name=\"type[]\"]', parent).change(function(){
            var val = $(this).val();
            if(val == "Fee Differential"){
                $('span.award', parent).show();
                $('input[name=\"award[]\"]', parent).hide();
                $('select[name=\"hours[]\"]', parent).hide();
                $('span.award', parent).text('$1000');
                $('span.salary', parent).text('$0');
                $('span.total', parent).text('$1000');
            }
            else if(val == "Top Up"){
                $('span.award', parent).hide();
                $('input[name=\"award[]\"]', parent).show();
                $('select[name=\"hours[]\"]', parent).hide();
                $('span.award', parent).text('$1000');
                $('span.salary', parent).text('$0');
                $('span.total', parent).text('$1000');
                $('input[name=\"award[]\"]', parent).change();
            }
            else{
                $('span.award', parent).show();
                $('input[name=\"award[]\"]', parent).hide();
                $('select[name=\"hours[]\"]', parent).show().change();
            }
        }).change();
    }

    $('.addSupervisor').click(function(){
        $('#supervisors tbody').append(template[0].outerHTML);
        initSupervisors();
    });

    initSupervisors();
}

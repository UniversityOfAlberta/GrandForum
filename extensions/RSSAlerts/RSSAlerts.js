var allPeople = new People();
allPeople.roles = ['all'];
allPeople.simple = true;
allPeople.comparator = 'fullName';
allPeople.fetch();

var allProjects = new Projects();
allProjects.fetch();

$('#new_feed').click(function(){
    $('#new_feed').parent().hide();
    $('#new_feed_div').show();
});

// Tables
$("#feeds").DataTable({
    'aLengthMenu': [[-1], ['All']],
    'dom': 'Blfrtip',
    'buttons': [
        'excel', 'pdf'
    ],
    "preDrawCallback": function() {
        $("#feeds input, #feeds select").each(function(i, el){
            $("#hiddenForm [name='" + $(el).attr('name') + "']").remove();
        });
        $("#feeds input, #feeds select").each(function(i, el){
            if($(el).attr("name") != "" && $(el).attr("name") != undefined){
                var clone = $(el).clone();
                $("option", el).each(function(j, op){
                    $(clone).find("option[value=" + $(op).val() + "]").attr("selected", $(op).prop('selected'));
                });
                $("#hiddenForm").append(clone);
            }
        });
    }
});
$("#feeds").show();

$("#articles").DataTable({
    'aLengthMenu': [[50, 100, -1], [50, 100, 'All']],
    'autoWidth': false,
    'aaSorting': [[2,'desc']],
    'dom': 'Blfrtip',
    'buttons': [
        'excel', 
        {
            extend: 'pdfHtml5',
            orientation: 'landscape'
        }
    ],
    "preDrawCallback": function() {
        $("#articles input, #articles select").each(function(i, el){
            $("#hiddenForm [name='" + $(el).attr('name') + "']").remove();
        });
        $("#articles input, #articles select").each(function(i, el){
            if($(el).attr("name") != "" && $(el).attr("name") != undefined){
                var clone = $(el).clone();
                $("option", el).each(function(j, op){
                    $(clone).find("option[value=" + $(op).val() + "]").attr("selected", $(op).prop('selected'));
                });
                $("#hiddenForm").append(clone);
            }
        });
    },
    "drawCallback": function(){
        $("#articles input, #articles select").each(function(i, el){
            $("#hiddenForm [name='" + $(el).attr('name') + "']").remove();
        });
    },
});
$("#articles").show();


// Editing
$("td.filter").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<input type='text' name='filter[" + id + "]' style='width:100%;box-sizing:border-box;margin:0;' />");
    $("input", $(this)).val(text);
    $(this).off("dblclick");
});

$("td.people").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<input type='hidden' name='people[" + id + "][]' />" + 
                 "<select style='min-width:120px;' data-placeholder='People...' name='people[" + id + "][]' multiple ></select>");
    _.each(allPeople.toJSON(), function(person){
        if(person.fullName == ""){ return; }
        var selected = (text.indexOf(person.fullName) != -1) ? "selected" : "";
        $("select", $(this)).append("<option value='" + person.id + "' " + selected + " >" + person.fullName + "</option>");
    }.bind(this));
    $("select", $(this)).chosen({width: "100%"});
    $("div.chosen-container", this).css("margin", 0);
    $(this).off("dblclick");
    $(this).css('white-space', 'nowrap');
});

$("td.projects").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<input type='hidden' name='projects[" + id + "][]' />" + 
                 "<select style='min-width:120px;' data-placeholder='Projects...' name='projects[" + id + "][]' multiple ></select>");
    _.each(allProjects.toJSON(), function(project){
        if(project.name == ""){ return; }
        var selected = (text.indexOf(project.name) != -1) ? "selected" : "";
        $("select", $(this)).append("<option value='" + project.id + "' " + selected + " >" + project.name + "</option>");
    }.bind(this));
    $("select", $(this)).chosen({width: "100%"});
    $("div.chosen-container", this).css("margin", 0);
    $(this).off("dblclick");
    $(this).css('white-space', 'nowrap');
});

$("td.keywords").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<input type='text' name='keywords[" + id + "]' />");
    $("input", $(this)).val(text).tagit();
    $(this).off("dblclick");
});

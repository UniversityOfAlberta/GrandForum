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
    'aLengthMenu': [[-1], ['All']]
});

$("#articles").DataTable({
    'aLengthMenu': [[-1], ['All']],
    'aaSorting': [[2,'desc']],
});

// Editing
$("td.people").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<select style='min-width:120px;' data-placeholder='People...' name='people[" + id + "][]' multiple ></select>");
    _.each(allPeople.toJSON(), function(person){
        if(person.fullName == ""){ return; }
        var selected = (text.indexOf(person.fullName) != -1) ? "selected" : "";
        $("select", $(this)).append("<option value='" + person.id + "' " + selected + " >" + person.fullName + "</option>");
    }.bind(this));
    $("select", $(this)).chosen();
    $(this).off("dblclick");
    $(this).css('white-space', 'nowrap');
});

$("td.projects").dblclick(function(){
    var id = $(this).closest("tr").attr("data-id");
    var text = $(this).text();
    $(this).html("<select style='min-width:120px;' data-placeholder='Projects...' name='projects[" + id + "][]' multiple ></select>");
    _.each(allProjects.toJSON(), function(project){
        if(project.name == ""){ return; }
        var selected = (text.indexOf(project.name) != -1) ? "selected" : "";
        $("select", $(this)).append("<option value='" + project.id + "' " + selected + " >" + project.name + "</option>");
    }.bind(this));
    $("select", $(this)).chosen();
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

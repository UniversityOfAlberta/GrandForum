var $ = jQuery;

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var project = getParameterByName("project");
if(project != ""){
    // Show individual project
    $("#project-page").show();
    $("#project-list").hide();
    $.get('https://forum.glyconet.ca/index.php?action=api.project/' + project, function(response){
        
        $("#project-title").html(response.fullname);
        var leaders = new Array();
        var leaderNames = new Array();
        $.each(response.leaders, function(i, leader){
            leaders.push("<a href='http://canadianglycomics.ca/people/?tab=network-investigators&person=" + leader.id + "'>" + leader.name + "</a>");
            leaderNames.push(leader.name);
        });
        $("#project-leaders").append(leaders.join('; '));
        $("#project-description").html(response.description);
        $.get('https://forum.glyconet.ca/index.php?action=api.project/' + project + '/members/NI', function(people){
            var length = 0;
            var nis = new Array();
            $.each(people, function(i, person){
                if($.inArray(person.fullName, leaderNames) == -1){
                    length++;
                    nis.push("<a href='http://canadianglycomics.ca/people/?tab=network-investigators&person=" + person.id + "'>" + person.fullName + "</a>");
                }
            });
            if(length > 0){
                $("#project-nis").append(nis.join(", "));
                $("#project-nis").show();
            }
        });
    });
}
else{
    // Show list of Projects
    $.get('https://forum.glyconet.ca/index.php?action=api.project', function(response){
        var themes = {}
        $.each(response, function(i, project){
            if(themes[project.theme] == undefined){
                themes[project.theme] = new Array();
            }
            themes[project.theme].push(project);
        });
        $.each(themes, function(theme, projects){
            $("#project-list").append("<h4>" + theme + "</h4>");
            $.each(projects, function(i, project){
                var leaders = new Array();
                $.each(project.leaders, function(i, leader){
                    leaders.push(leader.name);
                });
                $("#project-list").append("<div style='margin-bottom:10px;'><a href='?project=" + project.id + "'>" + project.fullname + "</a><br /><span style='font-weight:bold;'>Project Leader:</span>&nbsp;" + leaders.join(', ') + "</div>");
            });
            $("#project-list").append("<hr />");
        });
        $("#project-list hr").last().remove();
    });
}

$(document).ready(function(){
    if(project != ""){
        $("#project-page").show();
        $("#project-list").hide();
    }
    else{
        $("#project-page").hide();
        $("#project-list").show();
    }
});

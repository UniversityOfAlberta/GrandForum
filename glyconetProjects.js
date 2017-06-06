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
        $("#project-image").html("<img style='width:100%;border-radius:10px;' src='http://canadianglycomics.ca/wp-content/uploads/" + response.theme.replace(/ /g, '-') + ".jpg' />");
        $("#project-title").html(response.fullname);
        $(".gdl-page-title").html("<a style='color: #0e4c61;' href='http://canadianglycomics.ca/projects/'>Projects</a> » " + response.name);
        var leaders = new Array();
        var leaderNames = new Array();
        $.each(response.leaders, function(i, leader){
            leaders.push("<a href='http://canadianglycomics.ca/people/?tab=network-investigators&person=" + leader.id + "'>" + leader.name + "</a>");
            leaderNames.push(leader.name);
        });
        $("#project-leaders").append(leaders.join('; '));
        $("#project-description").html(response.description);
        $.get('https://forum.glyconet.ca/index.php?action=api.project/' + project + '/members/NI,NFI', function(people){
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
        $.get('https://forum.glyconet.ca/index.php?action=api.project/' + project + '/members/Collaborator', function(people){
            var length = 0;
            var collabs = new Array();
            $.each(people, function(i, person){
                length++;
                collabs.push("<a href='http://canadianglycomics.ca/people/?tab=collaborators&person=" + person.id + "'>" + person.fullName + "</a>");
            });
            if(length > 0){
                $("#project-collaborators").append(collabs.join(", "));
                $("#project-collaborators").show();
            }
        });
    });
}
else{
    // Show list of Projects
    $.get('https://forum.glyconet.ca/index.php?action=api.project', function(response){
        var themes = {}
        $.each(response, function(i, project){
            if(project.status == "Proposed"){
                return;
            }
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

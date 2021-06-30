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
        //$("#project-image").html("<img style='width:100%;border-radius:10px;' src='http://canadianglycomics.ca/wp-content/uploads/" + response.themeName.replace(/ /g, '-') + ".jpg' />");
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
            if(project.status != "Active" || project.name == "SD-1" || 
                                             project.name == "CD-78" || 
                                             project.name == "CD-44"){
                return;
            }
            switch(project.name){
                case "CD-2":
                case "CD-29":
                case "CD-30":
                case "CD-46":
                case "CD-47":
                case "CD-50":
                case "CD-57":
                case "CD-60":
                case "CD-63":
                case "CD-65":
                case "CD-66":
                case "CD-72":
                case "DO-2":
                case "DO-16":
                case "DO-18":
                case "DO-19":
                case "RG-14":
                case "TP-1":
                case "TP-22":
                case "TP-24":
                case "TP-27":
                case "TP-38":
                    project.themeName = "Chronic Diseases";
                    project.theme = "CD";
                    break;
                case "CD-44":
                case "CD-68":
                case "CD-31":
                case "CD-36":
                case "CD-40":
                case "CD-58":
                case "CR-01":
                case "CR-03":
                case "TP-35":
                case "TP-36":
                    project.themeName = "Cancer";
                    project.theme = "CR";
                    break;
                case "AM-2":
                case "AM-5":
                case "AM-21":
                case "AM-22":
                case "AM-25":
                case "AM-32":
                case "ID-01":
                case "ID-02":
                case "ID-03":
                case "ID-04":
                case "TP-25":
                    project.themeName = "Infectious Diseases";
                    project.theme = "ID";
                    break;
                case "CD-33":
                case "CD-35":
                case "CD-41":
                case "CD-61":
                case "CD-62":
                case "CD-67":
                case "CD-70":
                case "CD-71":
                case "ND-01":
                case "ND-02":
                case "ND-03":
                case "ND-05":
                case "RG-1":
                    project.themeName = "Neurodegenerative Diseases";
                    project.theme = "ND";
                    break;
            }
            if(themes[project.themeName] == undefined){
                themes[project.themeName] = new Array();
            }
            themes[project.themeName].push(project);
        });
        $.each(themes, function(theme, projects){
            if(theme == "Not Specified"){ return; }
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

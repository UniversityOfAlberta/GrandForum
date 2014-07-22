// Helper functions for template views should go here

function projectChecked(projects, projectId){
    if(_.where(projects, {id: projectId}).length > 0){
        return 'checked="checked"';
    }
    return "";
}

function otherChecked(view){
    var ret = '';
    var projects = view.projects.models;
    var allProjects = new Array();
    _.each(projects, function(proj){
        allProjects.push(proj);
        _.each(proj.get('subprojects'), function(sub){
            allProjects.push(sub);
        });
    });
    _.each(view.model.get('projects'), function(proj){
        if(_.where(allProjects, {id: proj.id}).length == 0){
            ret = 'checked="checked"';
        }
    });
    return ret;
}

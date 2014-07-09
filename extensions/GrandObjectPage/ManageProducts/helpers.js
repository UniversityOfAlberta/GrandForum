// Helper functions for template views should go here

function projectChecked(projects, projectId){
    if(_.where(projects, {id: projectId}).length > 0){
        return 'checked="checked"';
    }
    return "";
}

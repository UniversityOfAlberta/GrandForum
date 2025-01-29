<?php
$wgHooks['ToolboxLinks'][] = 'ManagePeople::createToolboxLinks';
BackbonePage::register('ManagePeople', 'ManagePeople', 'network-tools', dirname(__FILE__));

class ManagePeople extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(NI);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'manage_people',
                     'manage_people_row',
                     'edit_roles',
                     'edit_roles_row',
                     'edit_universities',
                     'edit_universities_row',
                     'edit_relations',
                     'edit_relations_row');
    }
    
    function getViews(){
        global $wgOut;
        $universities = new Collection(University::getAllUniversities());
        $uniNames = json_encode($universities->pluck('name'));
        $positions = json_encode(array_values(Person::getAllPositions()));

        $departments = json_encode(array_values(Person::getAllDepartments()));
        
        $wgOut->addScript("<script type='text/javascript'>
            var allUniversities = $uniNames;
            var allPositions = $positions;
            var allDepartments = $departments;
        </script>");
        
        return array('Backbone/*',
                     'ManagePeopleView',
                     'ManagePeopleRowView',
                     'ManagePeopleEditRolesView',
                     'ManagePeopleEditUniversitiesView',
                     'ManagePeopleEditRelationsView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $me = Person::newFromWgUser();
            $title = "Manage HQP";
            if($me->isRoleAtLeast(STAFF)){
                $title = "Manage People";
            }
            $toolbox['Tools']['links'][] = TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:ManagePeople");
        }
        return true;
    }

}

?>

<?php

$wgHooks['ToolboxLinks'][] = 'ManagePeople::createToolboxLinks';
BackbonePage::register('ManagePeople', 'ManagePeople', 'network-tools', dirname(__FILE__));

class ManagePeople extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        global $config;
        $me = Person::newFromWgUser();
        if($config->getValue('networkName') == "AVOID"){
            return ($me->isRoleAtLeast(STAFF));
        }
        return $me->isRoleAtLeast(NI);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'manage_people',
                     'manage_people_row',
                     'edit_roles',
                     'edit_roles_row',
                     'edit_role_projects',
                     'edit_themes',
                     'edit_themes_row',
                     'edit_universities',
                     'edit_universities_row',
                     'edit_relations',
                     'edit_relations_row',
                     'edit_subroles',
                     'edit_alumni');
    }
    
    function getViews(){
        global $wgOut;
        $me = Person::newFromWgUser();
        $universities = new Collection(University::getAllUniversities());
        $uniNames = $universities->pluck('name');
        $positions = json_encode(array_values(Person::getAllPositions()));

        $departments = json_encode(array_values(Person::getAllDepartments()));
        $faculties = json_encode(array_values(Person::getAllFaculties()));
        $organizations = array_unique($uniNames);
        sort($organizations);
        
        $organizations = json_encode($organizations);
        $emptyProject = new Project(array());
        $frozen = json_encode((!$me->isRoleAtLeast(STAFF) && $emptyProject->isFeatureFrozen("Manage People")));
        
        $wgOut->addScript("<script type='text/javascript'>
            var allUniversities = $organizations;
            var allPositions = $positions;
            var allDepartments = $departments;
            var allFaculties = $faculties;
            
            var frozen = $frozen;
        </script>");
        
        return array('Backbone/*',
                     'ManagePeopleView',
                     'ManagePeopleRowView',
                     'ManagePeopleEditRolesView',
                     'ManagePeopleEditUniversitiesView',
                     'ManagePeopleEditRelationsView',
                     'ManagePeopleEditSubRolesView',
                     'ManagePeopleEditThemesView',
                     'ManagePeopleEditAlumniView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $toolbox['People']['links'][] = TabUtils::createToolboxLink("Manage People", "$wgServer$wgScriptPath/index.php/Special:ManagePeople");
        }
        return true;
    }

}

?>

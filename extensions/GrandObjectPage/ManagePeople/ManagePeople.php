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
        if($config->getValue('networkName') == "AVOID" || $config->getValue('networkName') == "Voyant"){
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
                     'edit_level_of_study',
                     'edit_relations',
                     'edit_relations_row',
                     'edit_subroles',
                     'edit_alumni');
    }
    
    function getViews(){
        global $wgOut, $config;
        $me = Person::newFromWgUser();
        $universities = new Collection(University::getAllUniversities());
        $uniNames = $universities->pluck('name');
        $positionsCombo = json_encode((count($config->getValue('positionList')) == 0));
        $positionList = json_encode($config->getValue('positionList'));
        $positions = (count($config->getValue('positionList')) > 0) ? array_keys($config->getValue('positionList')) : 
                                                                      Person::getAllPositions();
        $positions = large_json_encode($positions);
        $hqpPositions = large_json_encode($config->getValue('hqpPositionList'));

        $departments = large_json_encode(array_values(Person::getAllDepartments()));
        $faculties = large_json_encode(array_values(Person::getAllFaculties()));
        $organizations = array_unique($uniNames);
        sort($organizations);
        
        $organizations = json_encode($organizations);
        $emptyProject = new Project(array());
        $frozen = json_encode((!$me->isRoleAtLeast(STAFF) && $emptyProject->isFeatureFrozen("Manage People")));
        
        $wgOut->addScript("<script type='text/javascript'>
            var allUniversities = $organizations;
            var allPositions = $positions;
            var positionList = $positionList;
            var positionsCombo = $positionsCombo;
            var hqpPositions = $hqpPositions;
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

<?php
$wgHooks['ToolboxLinks'][] = 'Courses::createToolboxLinks';
$wgHooks['SubLevelTabs'][] = 'Courses::createSubTabs';
BackbonePage::register('Courses', 'Courses', 'network-tools', dirname(__FILE__));

/**
* Class Sops generates the Sop pages that we view!
*/
class Courses extends BackbonePage {

  /**
   * isListed checks whether sop is lister
   * @return bool
   */
    function isListed(){
        return false;
    }

    /**
    * userCanExecute returns boolean of whether user can execute or not.
    * @param string &user the user 
    * @return boolean
    */
    function userCanExecute($user){
        global $config;
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(HQP);
    }


    /**
    * getTemplates returns an array of the Sop templates
    * @return array
    */
    function getTemplates(){
        return array('Backbone/*',
                     'courses',
                     'courses_row',
                     'courses_edit',
		     'course',
        );
    }

    /**
    * getView returns an array of the Sop views
    * @return array
    */
    function getViews(){
        return array('Backbone/*',
          'CoursesView',
          'CoursesRowView',
          'CoursesEditView',
	  'CourseView',
        );
    }

    /**
    * getModels returns an array models
    * @return array
    */
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Courses";

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "Courses") ? "selected" : false;
            $tabs["Courses"]['subtabs'][] = TabUtils::createSubTab("Courses", "{$url}", $selected);
        }
        
        return true;
    }

    /**
    * createToolboxLinks inserts new links to toolbox array
    * @param array $toolbox array to be modified
    * @return boolean
    */
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
       	//if(self::userCanExecute($wgUser)){
	$person = Person::newFromWgUser();
	if($person->isRole(HQP)){
            $toolbox['People']['links'][] = TabUtils::createToolboxLink("Manage Courses", "$wgServer$wgScriptPath/index.php/Special:Courses");
        }
        return true;
    }
}

?>

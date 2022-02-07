<?php
$wgHooks['ToolboxLinks'][] = 'Sops::createToolboxLinks';
$wgHooks['SubLevelTabs'][] = 'Sops::createSubTabs';
BackbonePage::register('SoPs', 'SoPs', 'network-tools', dirname(__FILE__));

/**
* Class Sops generates the Sop pages that we view!
*/
class Sops extends BackbonePage {

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
        return $me->isRoleAtLeast(EVALUATOR);
    }


    /**
    * getTemplates returns an array of the Sop templates
    * @return array
    */
    function getTemplates(){
        return array('Backbone/*',
                     'sops',
                     'sops_row',
                     'sops_edit',
                     'notes'
        );
    }

    /**
    * getView returns an array of the Sop views
    * @return array
    */
    function getViews(){
        return array('Backbone/*',
          'SopsView',
          'SopsRowView',
          'SopsEditView',
          'NotesView'
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
        $url = "$wgServer$wgScriptPath/index.php/Special:Sops";

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "Sops") ? "selected" : false;
            $tabs["Review"]['subtabs'][] = TabUtils::createSubTab("Applicant Review", "{$url}", $selected);
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
        $me = Person::newFromWgUser();
        if(self::userCanExecute($wgUser)){
            $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Students Overview", "$wgServer$wgScriptPath/index.php/Special:Sops");
        }
        return true;
    }
}

?>

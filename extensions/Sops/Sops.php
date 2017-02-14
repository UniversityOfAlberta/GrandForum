<?php
$wgHooks['ToolboxLinks'][] = 'Sops::createToolboxLinks';
BackbonePage::register('Sops', 'Sops', 'network-tools', dirname(__FILE__));

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
        return $me->isRoleAtLeast(HQP);
    }


    /**
    * getTemplates returns an array of the Sop templates
    * @return array
    */
    function getTemplates(){
        return array('Backbone/*',
                     'sops',
                     'sops_row',
                     'sops_edit'
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
        );
    }

    /**
    * getModels returns an array models
    * @return array
    */
    function getModels(){
        return array('Backbone/*');
    }

    /**
    * createToolboxLinks inserts new links to toolbox array
    * @param array $toolbox array to be modified
    * @return boolean
    */
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if(self::userCanExecute($wgUser)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("SoPs", "$wgServer$wgScriptPath/index.php/Special:Sops");
        }
        return true;
    }
}

?>

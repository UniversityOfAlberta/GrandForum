<?php
$wgHooks['ToolboxLinks'][] = 'AdminTabs::createToolboxLinks';
$wgHooks['SubLevelTabs'][] = 'AdminTabs::createSubTabs';
BackbonePage::register('AdminTabs', 'Admin Tabs', 'network-tools', dirname(__FILE__));


class AdminTabs extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(ADMIN);
    }
    
    function getTemplates(){
        return array(
		     "Backbone/*",
		     "reviewer_import",
		     "student_import",
		     "edit_bio",
		     "tabs",
		     "gsms_outcome_import",
		     );
    }
    
    function getViews(){
        return array("
		     Backbone/*",
		     "StudentImportView",
		     "ReviewerImportView",
		     "EditBioView",
		     "TabsView",
		     "GsmsOutcomeImportView",
		     );
    }
    
    function getModels(){
        return array("AdminTabsModel");
    }

    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:AdminTabs";

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "AdminTabs") ? "selected" : false;
            $tabs["AdminTabs"]['subtabs'][] = TabUtils::createSubTab("AdminTabs", "{$url}", $selected);
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
        if(self::userCanExecute($wgUser)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Import Student PDF", "$wgServer$wgScriptPath/index.php/Special:AdminTabs");
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Import Reviewers", "$wgServer$wgScriptPath/index.php/Special:AdminTabs#tabs-2");
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Import Student GSMS Bio", "$wgServer$wgScriptPath/index.php/Special:AdminTabs#tabs-3");
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Import Student GSMS Outcomes", "$wgServer$wgScriptPath/index.php/Special:AdminTabs#tabs-4");
        }
        return true;
    }

}

?>

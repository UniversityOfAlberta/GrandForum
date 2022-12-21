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
        global $wgOut;
        $stats = self::evalStats();
        $wgOut->addHTML("<script type='text/javascript'>
            evalTotal = '{$stats['total']}';
            evalCompleted = '{$stats['completed']}';
        </script>");
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
    
    static function evalStats(){
        $me = Person::newFromWgUser();
        $evals = $me->getEvaluates('sop');
        $completed = 0;
        foreach($evals as $eval){
            $sop = SOP::newFromUserId($eval->getId(), YEAR);
            $ignore = self::getBlobValue(BLOB_ARRAY, YEAR, "RP_OTT", "OT_REVIEW", "CS_Review_Uninteresting", $me->getId(), $sop->id, 0);
            //if($ignore == null || @count($ignore["q0"]) == 0){
                $rank = self::getBlobValue(BLOB_TEXT, YEAR, "RP_OTT", "OT_REVIEW", "CS_Review_Rank", $me->getId(), $sop->id, 0);
                $confidence = self::getBlobValue(BLOB_TEXT, YEAR, "RP_OTT", "OT_REVIEW", "CS_Review_Rank_Confidence", $me->getId(), $sop->id, 0);
                $explain = self::getBlobValue(BLOB_TEXT, YEAR, "RP_OTT", "OT_REVIEW", "CS_Review_RankExplain", $me->getId(), $sop->id, 0);
                if($rank != "" && $confidence != "" && trim($explain) != ""){
                    $completed++;
                }
            //}
        }
        return array('total' => count($evals), 'completed' => $completed);
    }
    
    static function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, $subItem);
        $result = $blb->load($addr);
        $data = $blb->getData();
        return $data;
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

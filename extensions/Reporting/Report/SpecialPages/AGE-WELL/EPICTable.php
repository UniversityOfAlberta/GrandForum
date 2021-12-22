<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EPICTable'] = 'EPICTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EPICTable'] = $dir . 'EPICTable.i18n.php';
$wgSpecialPageGroups['EPICTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'EPICTable::createSubTabs';

function runEPICTable($par) {
    EPICTable::execute($par);
}

class EPICTable extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("EPICTable", null, false, 'runEPICTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(SD) || $person->getName() == "Euson.Yeung" || $person->getName() == "Susan.Jaglal");
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("EPIC Table");
        EPICTable::generateHTML($wgOut);
    }
    
    function generateCSV($epics){
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="EPIC Survey.csv"');
        echo "Name,Email,HQP Type,Institution,Department,Title,WPs,Gender,1a,1b,1c,1d,1e,1f,1g,2a,2a_ex,2b,2b_ex,2c,2c_ex,2d,2d_ex,2e,2e_ex,2f,2f_ex,2g,2g_ex,2h,2h_ex,2i,2j,2j_ex,2k,2l,2m,2n,2n_ex,2o,3\n";
        foreach($epics as $epic){
            $wps = array();
            foreach($epic->getProjects() as $project){
                foreach($project->getChallenges() as $challenge){
                    $wps[$challenge->getAcronym()] = $challenge->getAcronym();
                }
            }
            echo "\"{$epic->getName()}\",";
            echo "\"{$epic->getEmail()}\",";
            echo "\"".implode(", ", array_unique($epic->getSubRoles()))."\",";
            echo "\"{$epic->getUni()}\",";
            echo "\"{$epic->getDepartment()}\",";
            echo "\"{$epic->getPosition()}\",";
            echo "\"".implode(", ", $wps)."\",";
            echo "\"{$epic->getGender()}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1A')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1B')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1C')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1D')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1E')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1F')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'1G')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2A')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2A_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2B')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2B_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2C')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2C_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2D')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2D_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2E')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2E_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2F')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2F_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2G')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2G_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2H')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2H_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2I')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2J')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2J_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2K')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2L')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2M')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2N')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2N_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'2O_EX')}\",";
            echo "\"{$this->getBlobValue($epic->getId(),'3A_EX')}\",";
            echo "\n";
        }
        exit;
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgOut;
        
        $me = Person::newFromWgUser();

        $epics = array();
        $hqps = array_merge(Person::getAllPeopleDuring(HQP, "0000-00-00", "9999-12-31"), 
                            Person::getAllCandidatesDuring(HQP, "0000-00-00", "9999-12-31"));
        
        foreach($hqps as $hqp){
            if($hqp->isEpic()){
                $epics[] = $hqp;
            }
        }
        if(isset($_GET['downloadCSV'])){
            $this->generateCSV($epics);
        }
        $tabbedPage = new TabbedPage("person");
        $tab = new ApplicationTab('RP_EPIC_REPORT', $epics, 0, "EPIC Survey");
        $tab->html = "<a class='button' style='margin-bottom:10px;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EpicTable?downloadCSV'>Download as CSV</a>";
        $tabbedPage->addTab($tab);
        $tabbedPage->showPage();
    }
    
    function getBlobValue($hqpId, $item){
        $addr = ReportBlob::create_address('RP_EPIC_REPORT', 'SURVEY', $item, 0);
        $blob = new ReportBlob(BLOB_TEXT, 0, $hqpId, 0);
        $blob->load($addr);
        $value = $blob->getData();
        $value = str_replace('"', "'", $value);
        return $value;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EPICTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("EPIC Surveys", "$wgServer$wgScriptPath/index.php/Special:EPICTable", $selected);
        }
        return true;
    }

}

?>

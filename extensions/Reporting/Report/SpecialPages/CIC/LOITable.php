<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['LOITable'] = 'LOITable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['LOITable'] = $dir . 'LOITable.i18n.php';
$wgSpecialPageGroups['LOITable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'LOITable::createSubTabs';

function runLOITable($par) {
    LOITable::execute($par);
}

class LOITable extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("LOITable", null, false, 'runLOITable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $people = Person::getAllCandidates();
        $this->getOutput()->setPageTitle("LOI Table");
        $wgOut->addHTML("<table id='loi_table' class='wikitable'>
            <thead>
                <th style='width:20%;'>Applicant</th>
                <th style='width:60%;'>Title</th>
                <th style='width:1%;'>Application</th>
                <th style='width:20%;'>Actions</th>
            </thead>
            <tbody>");
        foreach($people as $person){
            $projectId = 0;
            while(true){
                $report = new DummyReport("LOI", $person, $projectId++, 0, true);
                if(!$report->isGenerated()){
                    break;
                }
                $pdf = $report->getPDF();
                $wgOut->addHTML("<tr>
                    <td>{$person->getNameForForms()}</td>
                    <td>{$this->getBlobValue('TITLE', $person, $projectId)}</td>
                    <td><a class='button' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=".urlencode($pdf[0]['token'])."'>Download</a></td>
                    <td></td>
                </tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#loi_table').DataTable({
                'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': -1, 
                'autoWidth': false
             });
        </script>");
    }
    
    function getBlobValue($blobItem, $person, $projectId=0, $blobType=BLOB_TEXT){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $person->getId();
        
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_LOI', 'INTENT', $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "LOITable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("LOI Table", "{$wgServer}{$wgScriptPath}/index.php/Special:LOITable", $selected);
        }
        
        return true;
    }

}

?>

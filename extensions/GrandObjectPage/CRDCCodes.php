<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['CRDCCodes'] = 'CRDCCodes'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CRDCCodes'] = $dir . 'CRDCCodes.i18n.php';
$wgSpecialPageGroups['CRDCCodes'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'CRDCCodes::createSubTabs';

function runCRDCCodes($par){
    CRDCCodes::execute($par);
}

class CRDCCodes extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("CRDCCodes", null, false, 'runCRDCCodes');
	}
	
	function userCanExecute($wgUser){
	    $person = Person::newFromUser($wgUser);
	    return $person->isRoleAtLeast(STAFF);
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
		$this->getOutput()->setPageTitle("CRDCCodes");
		
		$data = array();
		
		$people = Person::getAllPeople();
		foreach($people as $person){
	        foreach($person->getCRDC() as $crdc){
	            if(!isset($data[$crdc])){ 
	                // Init
	                $data[$crdc] = array('ecr' => 0, 'non' => 0);
	            }
	            if($person->getEarlyCareerResearcher()){
	                // ECR
	                $data[$crdc]['ecr']++;
	            }
	            else{
	                // Non-ECR
	                $data[$crdc]['non']++;
	            }
	        }
		}
		
	    $wgOut->addHTML("<table id='milestonesHistory' frame='box' rules='all'>
	                        <thead>
	                            <tr>
	                                <th>Code</th>
	                                <th>Description</th>
	                                <th>ECR</th>
	                                <th>non-ECR</th>
	                            </tr>
	                        </thead>
	                        <tbody>");
	    $codes = $config->getValue("crdcCodes");
	    foreach($data as $code => $row){
	        $wgOut->addHTML("<tr>
	                            <td>{$code}</td>
	                            <td>{$codes[$code]}</td>
	                            <td>{$row['ecr']}</td>
	                            <td>{$row['non']}</td>
	                         </tr>");
	    }
        $wgOut->addHTML("</tbody></table><script type='text/javascript'>
	        $('#milestonesHistory').dataTable({
	            'iDisplayLength': 100,
                'autoWidth': false,
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
	    </script>");
	}
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "CRDCCodes") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("CRDC Codes", "$wgServer$wgScriptPath/index.php/Special:CRDCCodes", $selected);
        }
        return true;
    }
}

?>

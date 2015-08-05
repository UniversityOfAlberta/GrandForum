<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HQPRegisterTable'] = 'HQPRegisterTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HQPRegisterTable'] = $dir . 'HQPRegisterTable.i18n.php';
$wgSpecialPageGroups['HQPRegisterTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'HQPRegisterTable::createSubTabs';

function runHQPRegisterTable($par) {
    HQPRegisterTable::execute($par);
}

class HQPRegisterTable extends SpecialPage{

    function HQPRegisterTable() {
        SpecialPage::__construct("HQPRegisterTable", null, false, 'runHQPRegisterTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        HQPRegisterTable::generateHTML($wgOut);
    }
    
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $candidates = Person::getAllCandidates(HQP);
        $wgOut->addHTML("<table id='hqpRegisterTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th>Registration Date</th>
                    <th>Level</th>
                    <th>Supervisor</th>
                    <th>Application PDF</th>
                </tr>
            </thead>
            <tbody>");
        foreach($candidates as $candidate){
            $report = new DummyReport("HQPApplication", $candidate);
            $check = $report->getLatestPDF();
            $button = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $button = "<a class='button' href='{$pdf->getUrl()}'>Download PDF</a>";
            }
            
            $supervisor = $this->getBlobValue(REPORTING_YEAR, $candidate->getId(), HQP_APPLICATION_SUP);
            $level = $this->getBlobValue(REPORTING_YEAR, $candidate->getId(), HQP_APPLICATION_LVL);
            
            if($level == 'Other:'){
                $level = $this->getBlobValue(REPORTING_YEAR, $candidate->getId(), HQP_APPLICATION_OTH);
            }
            
            $wgOut->addHTML("<tr>");
            $candidate->getName();
            $wgOut->addHTML("<td align='right'>{$candidate->getFirstName()}</td>");
            $wgOut->addHTML("<td>{$candidate->getLastName()}</td>");
            $wgOut->addHTML("<td><a href='mailto:{$candidate->getEmail()}'>{$candidate->getEmail()}</a></td>");
            $wgOut->addHTML("<td>".time2date($candidate->getRegistration(), 'Y-m-d')."</td>");
            $wgOut->addHTML("<td>{$level}</td>");
            $wgOut->addHTML("<td>{$supervisor}</td>");
            $wgOut->addHTML("<td align='center'>{$button}</td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('#hqpRegisterTable').dataTable({'iDisplayLength': 100});
        </script>");
    }
    
    static function getBlobValue($year, $candidateId, $item){
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, $item, 0);
        $blob = new ReportBlob(BLOB_TEXT, $year, $candidateId, 0);
        $blob->load($addr);
        return nl2br($blob->getData());
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "HQPRegisterTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("HQP Registration Table", "$wgServer$wgScriptPath/index.php/Special:HQPRegisterTable", $selected);
        }
        return true;
    }

}

?>

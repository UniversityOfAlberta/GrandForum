<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['RFPApplicationTable'] = 'RFPApplicationTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['RFPApplicationTable'] = $dir . 'RFPApplicationTable.i18n.php';
$wgSpecialPageGroups['RFPApplicationTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'RFPApplicationTable::createSubTabs';

function runRFPApplicationTable($par) {
    RFPApplicationTable::execute($par);
}

class RFPApplicationTable extends SpecialPage{

    function RFPApplicationTable() {
        SpecialPage::__construct("RFPApplicationTable", null, false, 'runRFPApplicationTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("RFP Application Table");
        RFPApplicationTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $wgOut->addHTML("<div id='tabs'>
                            <ul>
                                <li><a href='#catalyst'>Catalyst</a></li>
                                <li><a href='#translational'>Translational</a></li>
                            </ul>");
        $this->addTable(RP_CATALYST, 'catalyst');
        $this->addTable(RP_TRANS, 'translational');
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#tabs').tabs();
        </script>");
    }
    
    function addTable($rp=RP_CATALYST, $type='catalyst'){
        global $wgOut;
        $nis = array_merge(Person::getAllPeople(NI), Person::getAllCandidates(NI));
        $wgOut->addHTML("
            <div id='{$type}'>
            <table id='{$type}Table' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th>Project Title</th>
                    <th width='1%'>Generation&nbsp;Date (MST)</th>
                    <th width='1%'>PDF&nbsp;Download</th>
                </tr>
            </thead>
            <tbody>");
        foreach($nis as $ni){
            $report = new DummyReport($rp, $ni, null, REPORTING_YEAR, true);
            $check = $report->getLatestPDF();
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $generated = $check[0]['timestamp'];
                
                $addr = ReportBlob::create_address($rp, CAT_DESC, CAT_DESC_TITLE, 0);
                $blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $ni->getId(), 0);
                $blob->load($addr);
                $title = $blob->getData();
                
                $wgOut->addHTML("<tr>");
                $wgOut->addHTML("<td>{$ni->getFirstName()}</td><td>{$ni->getLastName()}</td><td><a href='mailto:{$ni->getEmail()}'>{$ni->getEmail()}</a></td>");
                $wgOut->addHTML("<td>{$title}</td>");
                $wgOut->addHTML("<td style='white-space:nowrap;'>".time2date($generated, 'F j, Y h:i:s')."</td>");
                $wgOut->addHTML("<td align='center'><a class='button' href='{$pdf->getUrl()}'>Download</a></td>");
                $wgOut->addHTML("</tr>");
            }
        }
        $wgOut->addHTML("</tbody>
        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#{$type}Table').dataTable();
        </script>
        </div>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "RFPApplicationTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("RFP Applications", "$wgServer$wgScriptPath/index.php/Special:RFPApplicationTable", $selected);
        }
        return true;
    }

}

?>

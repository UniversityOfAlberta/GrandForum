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
        
        $startYear = $config->getValue('projectPhaseDates');
        $startYear = substr($startYear[1], 0, 4);
        
        $wgOut->addHTML("<div id='tabs'>
                            <ul>");
        for($year=date('Y'); $year >= $startYear; $year--){
            $wgOut->addHTML("<li><a href='#tabs-$year'>$year</a></li>");
        }
        $wgOut->addHTML("</ul>");
        for($year=date('Y'); $year >= $startYear; $year--){
            $hqps = array_merge(Person::getAllPeopleDuring(HQP, $year.'-01-01 00:00:00', $year.'-12-31 23:59:59'), 
                                Person::getAllCandidatesDuring(HQP, $year.'-01-01 00:00:00', $year.'-12-31 23:59:59'));
            $wgOut->addHTML("<div id='tabs-$year'>
                <table id='hqpRegisterTable_{$year}' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th width='1%'>First&nbsp;Name</th>
                        <th width='1%'>Last&nbsp;Name</th>
                        <th width='1%'>Email</th>
                        <th>Registration Date</th>
                        <th>University</th>
                        <th>Level</th>
                        <th>Application</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>");
            foreach($hqps as $hqp){
                $tab = new HQPProfileTab($hqp, array('isMe' => true, 'isSupervisor' => true));
                
                $research = nl2br($tab->getBlobValue(HQP_APPLICATION_RESEARCH, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
                $train    = nl2br($tab->getBlobValue(HQP_APPLICATION_TRAIN, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
                $bio      = nl2br($tab->getBlobValue(HQP_APPLICATION_BIO, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
                $align    = nl2br($tab->getBlobValue(HQP_APPLICATION_ALIGN, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
                $boundary = nl2br($tab->getBlobValue(HQP_APPLICATION_BOUNDARY, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
                $cv       = $tab->getBlobValue(HQP_APPLICATION_CV, BLOB_RAW, HQP_APPLICATION_DOCS, true, $year);
                $application = "";
                $button = "";
                if($research != "" ||
                   $train != "" ||
                   $bio != "" ||
                   $align != "" ||
                   $boundary != "" ||
                   $cv != ""){
                    $report = new DummyReport(RP_HQP_APPLICATION, $hqp, null, $year);
                    $report->year = $year;
                    $check = $report->getLatestPDF();
                    if(isset($check[0])){
                        $pdf = PDF::newFromToken($check[0]['token']);
                        $button = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
                    }
                    $text = ($report->hasStarted()) ? "Award" : "Affiliate";
                    $star = ($tab->hasEdited()) ? "<b style='color:red;'>*</b>" : "";
                    $application .= "<button onClick='$(\"#app{$year}_{$hqp->getId()}\").dialog({width:800, maxHeight:600, height:600});'>{$text}</button>"; 
                    
                    $tab->generateBody($year);
                    $application .= "<div title='{$hqp->getNameForForms()}' id='app{$year}_{$hqp->getId()}' style='display:none;'><small><input type='text' size='1' style='position:relative;top:-20px;height:1px;float:right;' />";
                    $application .= $tab->html;
                    $application .= "</small></div>";
                }
                
                $wgOut->addHTML("<tr>");
                $hqp->getName();
                $wgOut->addHTML("<td align='right'>{$hqp->getFirstName()}</td>");
                $wgOut->addHTML("<td>{$hqp->getLastName()}</td>");
                $wgOut->addHTML("<td><a href='mailto:{$hqp->getEmail()}'>{$hqp->getEmail()}</a></td>");
                $wgOut->addHTML("<td>".time2date($hqp->getRegistration(), 'Y-m-d')."</td>");
                $wgOut->addHTML("<td>{$hqp->getUni()}</td>");
                $wgOut->addHTML("<td>{$hqp->getPosition()}</td>");
                $wgOut->addHTML("<td align='center'>{$application}</td>");
                $wgOut->addHTML("<td align='center'>{$button}</td>");
                $wgOut->addHTML("</tr>");
            }
            $wgOut->addHTML("</tbody></table>");
            
            $wgOut->addHTML("<script type='text/javascript'>
                $('#hqpRegisterTable_{$year}').dataTable({'iDisplayLength': 100});
            </script>
            </div>");
        }
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            $('#tabs').tabs();
        </script>");
    }
    
    static function getBlobValue($year, $hqpId, $item){
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, $item, 0);
        $blob = new ReportBlob(BLOB_TEXT, $year, $hqpId, 0);
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

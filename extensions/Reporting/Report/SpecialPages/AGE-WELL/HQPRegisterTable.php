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

    function __construct() {
        SpecialPage::__construct("HQPRegisterTable", null, false, 'runHQPRegisterTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("HQP Registration Table");
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
        
        $affilReport = new DummyReport("AffiliateApplication", Person::newFromWgUser(), null, 0, true);

        $affilReport->year = 0;
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
                        <th>Registration</th>
                        <th>University</th>
                        <th>Level</th>
                        <th>Affiliate</th>
                    </tr>
                </thead>
                <tbody>");
            foreach($hqps as $hqp){
                $application = "";
                $button1 = "";
                
                $affilReport->person = $hqp;
                
                if($affilReport->hasStarted()){
                    $check = $affilReport->getLatestPDF();
                    $button1 = "Started";
                    if(isset($check[0])){
                        $button1 = "<span style='display:none;'>Z</span><a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf={$check[0]['token']}&type=AffiliateApplication'>Download</a><br />{$check[0]['timestamp']}";
                    }
                }
                
                $wgOut->addHTML("<tr>");
                $wgOut->addHTML("<td align='right'>{$hqp->getFirstName()}</td>");
                $wgOut->addHTML("<td>{$hqp->getLastName()}</td>");
                $wgOut->addHTML("<td><a href='mailto:{$hqp->getEmail()}'>{$hqp->getEmail()}</a></td>");
                $wgOut->addHTML("<td align='center'>".time2date($hqp->getRegistration(), 'Y-m-d')."</td>");
                $wgOut->addHTML("<td>{$hqp->getUni()}</td>");
                $wgOut->addHTML("<td>{$hqp->getPosition()}</td>");
                $wgOut->addHTML("<td align='center'>{$button1}</td>");
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
    
    static function array_flatten(array $array)
    {
        $flat = array(); // initialize return array
        $stack = array_values($array); // initialize stack
        while($stack) // process stack until done
        {
            $value = array_shift($stack);
            if (is_array($value)) // a value to further process
            {
                $stack = array_merge(array_values($value), $stack);
            }
            else // a value to take
            {
               $flat[] = $value;
            }
        }
        return $flat;
    }
    
    static function getBlobValue($year, $hqpId, $item){
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, $item, 0);
        $blob = new ReportBlob(BLOB_TEXT, $year, $hqpId, 0);
        $blob->load($addr);
        $data = $blob->getData();
        if(is_array($data)){
            $data = self::array_flatten($data);
            $data = implode(", ", $data);
        }
        return nl2br($data);
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "HQPRegisterTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("HQP Registration Table", "$wgServer$wgScriptPath/index.php/Special:HQPRegisterTable", $selected);
        }
        return true;
    }

}

?>

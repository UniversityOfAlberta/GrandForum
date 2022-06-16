<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApplicationsTable'] = 'ApplicationsTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApplicationsTable'] = $dir . 'ApplicationsTable.i18n.php';
$wgSpecialPageGroups['ApplicationsTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ApplicationsTable::createSubTabs';

function runApplicationsTable($par) {
    ApplicationsTable::execute($par);
}

class ApplicationsTable extends SpecialPage{

    var $nis;
    var $fullHQPs;
    var $hqps;
    var $externals;
    var $wps;
    var $ccs;
    var $ihs;
    var $catalyst;
    var $platform;
    var $projects;

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function initArrays(){
        $this->projects = Project::getAllProjectsEver();
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $me = Person::newFromWgUser();
        
        $links = array();
        
        $wgOut->addHTML("<style type='text/css'>
            #bodyContent > h1:first-child {
                display: none;
            }
            
            #contentSub {
                display: none;
            }
        </style>");

        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=progress'>Progress</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=completion'>Completion</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=impact'>Impact</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=datatech'>DataTech</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=openround2'>OpenRound2</a>";
        $links[] = "<a href='$wgServer$wgScriptPath/index.php/Special:ApplicationsTable?program=opencall2022'>OpenCall2022</a>";

        
        $wgOut->addHTML("<h1>Report Tables:&nbsp;".implode("&nbsp;|&nbsp;", $links)."</h1><br />");
        if(!isset($_GET['program'])){
            return;
        }
        $program = $_GET['program'];
        
        $this->initArrays();
        
        if($program == "progress"){
            $this->generateProgress();
        }
        else if($program == "completion"){
            $this->generateCompletion();
        }
        else if($program == "impact"){
            $this->generateImpact();
        }
        else if($program == "datatech"){
            $this->generateDataTech();
        }
        else if($program == "openround2"){
            $this->generateOpenRound2();
        }
        else if($program == "opencall2022"){
            $this->generateOpenCall2022();
        }
        return;
    }
    
    function impactReportSummary($year){
        $activities = array("Grant proposal writing",
                            "Background research/literature review",
                            "Choosing research methods",
                            "Developing sampling procedures",
                            "Recruiting study participants",
                            "Engaging other research partners",
                            "Designing interview and/or survey questions",
                            "Collecting primary data",
                            "Analyzing collected data",
                            "Interpreting study findings",
                            "Writing reports and journal articles",
                            "Sharing findings at meetings, conferences and on social media",
                            "Other");

        $ceris = array();
        $summary_4_2 = array();
        $summary_4_4 = array();
        $summary_4_6 = array();
        $summary_4_7 = array();
        foreach($this->projects as $project){
            $ceri = self::getBlobValue("RP_IMPACT", "SECTION3", "CERI", 0, BLOB_ARRAY, $year, 0, $project->getId());
            if(is_array($ceri)){
                for($i=1;$i<=13;$i++){
                    for($j=1;$j<=4;$j++){
                        if(isset($ceri["ceri_{$i}_{$j}"]) && $ceri["ceri_{$i}_{$j}"] != ""){
                            $ceris["{$i}_{$j}"][] = $ceri["ceri_{$i}_{$j}"];
                        }
                    }
                }
            }
            
            // 4.2 Target
            $s_4_2 = self::getBlobValue("RP_IMPACT", "SECTION4", "TARGET", 0, BLOB_ARRAY, $year, 0, $project->getId());
            $s_4_2 = @$s_4_2['target'];
            if(is_array($s_4_2)){
                foreach($s_4_2 as $val){
                    $summary_4_2[$val][] = $project->getName();
                }
            }
            
            // 4.4 Venues
            $s_4_4 = self::getBlobValue("RP_IMPACT", "SECTION4", "VENUES", 0, BLOB_ARRAY, $year, 0, $project->getId());
            $s_4_4 = @$s_4_4['venues'];
            if(is_array($s_4_4)){
                foreach($s_4_4 as $val){
                    $summary_4_4[$val][] = $project->getName();
                }
            }
            
            // 4.6 Topics
            $s_4_6 = self::getBlobValue("RP_IMPACT", "SECTION4", "TOPICS", 0, BLOB_ARRAY, $year, 0, $project->getId());
            $s_4_6 = @$s_4_6['topics'];
            if(is_array($s_4_6)){
                foreach($s_4_6 as $val){
                    $summary_4_6[$val][] = $project->getName();
                }
            }
            
            // 4.7 Support
            $s_4_7 = self::getBlobValue("RP_IMPACT", "SECTION4", "SUPPORT", 0, BLOB_ARRAY, $year, 0, $project->getId());
            $s_4_7 = @$s_4_7['support'];
            if(is_array($s_4_7)){
                foreach($s_4_7 as $val){
                    $summary_4_7[$val][] = $project->getName();
                }
            }
        }
        $html = "<h3>CERI Averages</h3>
                 <table class='wikitable summary{$year}'>
                    <thead>
                    <tr>
                        <th>Project activity</th>
	                    <th>Service-delivery agencies</th>
	                    <th>Indigenous Partners</th>
	                    <th>PWLE</th>
	                    <th>Orders of Government</th>
	                    <th>Racialized Communities</th>
	                    <th>2SLGBTQIA+ community members</th>
                    </tr>
                    </thead>
                    <tbody>";
        for($i=1;$i<=13;$i++){
            $html .= "<tr><td><span style='display:none;'>{$i}.</span> {$activities[$i-1]}</td>";
            for($j=1;$j<=6;$j++){
                $avg = (isset($ceris["{$i}_{$j}"])) ? number_format(array_sum($ceris["{$i}_{$j}"])/count($ceris["{$i}_{$j}"]), 2) : "0.00";
                $html .= "<td>{$avg}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        // 4.2 Target
        $html .= "<h3>Section 4.2</h3>
                  <table class='wikitable summary{$year}'>
                    <thead>
                    <tr>
                        <th>Target Audience</th>
                        <th>N of projects</th>
                        <th>Projects</th>
                    </tr>
                    </thead>
                    <tbody>";
        foreach($summary_4_2 as $key => $vals){
            $html .= "<tr>";
            $html .= "<td>{$key}</td>";
            $html .= "<td>".count($vals)."</td>";
            $html .= "<td>".implode("; ", $vals)."</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        // 4.4 Venues
        $html .= "<h3>Section 4.4</h3>
                  <table class='wikitable summary{$year}'>
                    <thead>
                    <tr>
                        <th>KMb Item</th>
                        <th>N of projects</th>
                        <th>Projects</th>
                    </tr>
                    </thead>
                    <tbody>";
        foreach($summary_4_4 as $key => $vals){
            $html .= "<tr>";
            $html .= "<td>{$key}</td>";
            $html .= "<td>".count($vals)."</td>";
            $html .= "<td>".implode("; ", $vals)."</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        // 4.6 Topics
        $html .= "<h3>Section 4.6</h3>
                  <table class='wikitable summary{$year}'>
                    <thead>
                    <tr>
                        <th>KMb Outputs</th>
                        <th>N of projects</th>
                        <th>Projects</th>
                    </tr>
                    </thead>
                    <tbody>";
        foreach($summary_4_6 as $key => $vals){
            $html .= "<tr>";
            $html .= "<td>{$key}</td>";
            $html .= "<td>".count($vals)."</td>";
            $html .= "<td>".implode("; ", $vals)."</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        // 4.7 Support
        $html .= "<h3>Section 4.7</h3>
                  <table class='wikitable summary{$year}'>
                    <thead>
                    <tr>
                        <th>Method of delivery</th>
                        <th>N of projects</th>
                        <th>Projects</th>
                    </tr>
                    </thead>
                    <tbody>";
        foreach($summary_4_7 as $key => $vals){
            $html .= "<tr>";
            $html .= "<td>{$key}</td>";
            $html .= "<td>".count($vals)."</td>";
            $html .= "<td>".implode("; ", $vals)."</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        $html .= "<script type='text/javascript'>
            $('.summary{$year}').dataTable({
                autoWidth: false,
                aLengthMenu: [
                    [25, 50, 100, -1],
                    [25, 50, 100, 'All']
                ],
                'columnDefs': [
                    {'type': 'natural', 'targets': 0 }
                ],
                iDisplayLength: -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        
        return $html;
    }
    
    function generateProgress(){
        global $wgOut;
        
        $tabbedPage = new InnerTabbedPage("reports");
        for($year=date('Y'); $year >= 2021; $year--){
            $tab = new ApplicationTab(RP_PROGRESS, null, $year, "{$year}", array());                                           
            $tabbedPage->addTab($tab);
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateCompletion(){
        global $wgOut;
        
        $tabbedPage = new InnerTabbedPage("reports");
        for($year=date('Y'); $year >= 2021; $year--){
            $tab = new ApplicationTab('RP_COMPLETION', null, $year, "{$year}", array());                                           
            $tabbedPage->addTab($tab);
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateImpact(){
        global $wgOut;
        
        $ceris = array();
        $scores = array();
        
        for($i = 1; $i <= 6; $i++){
            $ceris[$i] = new AverageArrayReportItem();
            $ceris[$i]->setBlobType(BLOB_ARRAY);
            $ceris[$i]->setBlobItem('CERI');
            $ceris[$i]->setBlobSection("SECTION3");
            $ceris[$i]->setAttr("indices", "ceri_1_$i|ceri_2_$i|ceri_3_$i|ceri_4_$i|ceri_5_$i|ceri_6_$i|ceri_7_$i|ceri_8_$i|ceri_9_$i|ceri_10_$i|ceri_11_$i|ceri_12_$i|ceri_13_$i");
            
            $scores[$i] = new AverageArrayReportItem();
            $scores[$i]->setBlobType(BLOB_ARRAY);
            $scores[$i]->setBlobItem('CERI');
            $scores[$i]->setBlobSection("SECTION3");
            $scores[$i]->setAttr("indices", "ceri_1_$i|ceri_2_$i|ceri_3_$i|ceri_4_$i|ceri_5_$i|ceri_6_$i|ceri_7_$i|ceri_8_$i|ceri_9_$i|ceri_10_$i|ceri_11_$i|ceri_12_$i");
            $scores[$i]->setAttr("denominator", "3");
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        
        for($y = 2021; $y >= 2020; $y--){
            $tab = new ApplicationTab('RP_IMPACT', null, $y, "$y", array('Service Delivery Agencies' => $ceris[1],
                                                                         'Indigenous community-based agencies and/or governing bodies' => $ceris[2],
                                                                         'Persons with lived experiences of homelessness' => $ceris[3],
                                                                         'Orders of Government' => $ceris[4],
                                                                         'Racialized Communities' => $ceris[5],
                                                                         '2SLGBTQIA+ community members' => $ceris[6],
                                                                         'CERI 1 Score' => $scores[1],
                                                                         'CERI 2 Score' => $scores[2],
                                                                         'CERI 3 Score' => $scores[3],
                                                                         'CERI 4 Score' => $scores[4],
                                                                         'CERI 5 Score' => $scores[5],
                                                                         'CERI 6 Score' => $scores[6]));
                                                                                                 
            $tab->addExtra($this->impactReportSummary($y));
            $tabbedPage->addTab($tab);
        }
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateDataTech(){
        global $wgOut;

        $survey = array('title' => 'Title',
                        'stream' => 'Stream',
                        'applicant' => 'Applicant',
                        'position' => 'Position',
                        'dept' => 'Department',
                        'institution' => 'Institution',
                        'dept' => 'Department',
                        'tri_council' => "Tri-Council",
                        'sign_off' => "Sign-Off",
                        'other_funding' => "Other Funding",
                        'sources' => "Sources",
                        'ci1' => 'CI1',
                        'ci1_institution' => 'CI1 Institution',
                        'ci2' => 'CI2',
                        'ci2_institution' => 'CI2 Institution',
                        'involve' => "Involve",
                        'amount' => "Amount");
        
        $fields = array();
        foreach($survey as $key => $label){
            if($key == "involve"){
                $field = new CheckboxReportItem();
                $field->setBlobType(BLOB_ARRAY);
            }
            else{
                $field = new TextReportItem();
                $field->setBlobType(BLOB_TEXT);
            }
            $field->setBlobSection("SURVEY");
            $field->setBlobItem(strtoupper($key));
            $field->setId($key);
            $fields[$label] = $field;
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_DATA_TECH', null, 2020, "2020", $fields));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateOpenRound2(){
        global $wgOut;

        $survey = array('title' => 'Title',
                        'applicant' => 'Applicant',
                        'position' => 'Position',
                        'dept' => 'Department',
                        'institution' => 'Institution',
                        'dept' => 'Department',
                        'tri_council' => "Tri-Council",
                        'sign_off' => "Sign-Off",
                        'other_funding' => "Other Funding",
                        'sources' => "Sources",
                        'ci1' => 'CI1',
                        'ci1_institution' => 'CI1 Institution',
                        'ci2' => 'CI2',
                        'ci2_institution' => 'CI2 Institution',
                        'involve' => "Involve",
                        'amount' => "Amount");
        
        $fields = array();
        foreach($survey as $key => $label){
            if($key == "involve"){
                $field = new CheckboxReportItem();
                $field->setBlobType(BLOB_ARRAY);
            }
            else{
                $field = new TextReportItem();
                $field->setBlobType(BLOB_TEXT);
            }
            $field->setBlobSection("SURVEY");
            $field->setBlobItem(strtoupper($key));
            $field->setId($key);
            $fields[$label] = $field;
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_OPEN2', null, 2020, "2020", $fields));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    function generateOpenCall2022(){
        global $wgOut;

        $survey = array('title' => 'Title',
                        'applicant' => 'Applicant',
                        'position' => 'Position',
                        'dept' => 'Department',
                        'institution' => 'Institution',
                        'dept' => 'Department',
                        'tri_council' => "Tri-Council",
                        'sign_off' => "Sign-Off",
                        'other_funding' => "Other Funding",
                        'sources' => "Sources",
                        'ci1' => 'CI1',
                        'ci1_institution' => 'CI1 Institution',
                        'ci2' => 'CI2',
                        'ci2_institution' => 'CI2 Institution',
                        'involve' => "Involve",
                        'amount' => "Amount",
                        'stream' => "Stream",
                        'pillar' => "Pillars");
        
        $fields = array();
        foreach($survey as $key => $label){
            if($key == "involve" || $key == "stream" || $key == "pillar"){
                $field = new CheckboxReportItem();
                $field->setBlobType(BLOB_ARRAY);
            }
            else{
                $field = new TextReportItem();
                $field->setBlobType(BLOB_TEXT);
            }
            $field->setBlobSection("SURVEY");
            $field->setBlobItem(strtoupper($key));
            $field->setId($key);
            $fields[$label] = $field;
        }
        
        $tabbedPage = new InnerTabbedPage("reports");
        $tabbedPage->addTab(new ApplicationTab('RP_OPEN2022', null, 2022, "2022", $fields));
        $wgOut->addHTML($tabbedPage->showPage());
    }
    
    static function getBlobValue($rpType, $rpSection, $blobItem, $subBlobItem, $blobType, $year, $personId, $projectId){
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address($rpType, $rpSection, $blobItem, $subBlobItem);
        $result = $blb->load($addr);
        $data = $blb->getData();
        return $data;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ApplicationsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Reports", "$wgServer$wgScriptPath/index.php/Special:ApplicationsTable", $selected);
        }
        return true;
    }

}

?>

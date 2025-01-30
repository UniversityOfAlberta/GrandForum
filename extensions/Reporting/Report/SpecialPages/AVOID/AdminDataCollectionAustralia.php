<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminDataCollectionAustralia'] = 'AdminDataCollectionAustralia'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminDataCollectionAustralia'] = $dir . 'AdminDataCollectionAustralia.i18n.php';
$wgSpecialPageGroups['AdminDataCollectionAustralia'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminDataCollectionAustralia::createSubTabs';

class AdminDataCollectionAustralia extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("AdminDataCollectionAustralia", STAFF.'+', true);
    }
    
    
    function updateProgramAttendance(){
        $person = Person::newFromId($_GET['user']);
        foreach($_POST as $key => $value){
            IntakeSummary::saveBlobData($value, "ATTENDANCE", "{$key}", $person, 0, "RP_SUMMARY");
        }
    }
    
    static function usageHeaderTop(){
        return "<th rowspan='2'>Action Plans</th>
                <th colspan='".count(IntakeSummary::$topics)."'>Education Views</th>
                <th colspan='4'>Data Collected</th>";
    }
    
    static function usageHeaderBottom(){
        return "<th style='width:1px;'>".implode("</th><th style='width:1px;'>", IntakeSummary::$topics)."</th>
                <th style='width:1px;'>Program Library</th>
                <th style='width:1px;'>Frailty Report Views</th>
                <th style='width:1px;'>Progress Report Views</th>
                <th style='width:1px;'>Logins</th>";
    }
    
    static function usageRow($person){
        $html = "";
        // Action Plans
        $plans = array();
        foreach(ActionPlan::newFromUserId($person->getId()) as $plan){
            $plans[] = $plan;
        }
        
        $submittedPlans = array();
        $components = array('A' => 0, 
                            'V' => 0, 
                            'O' => 0, 
                            'I' => 0, 
                            'D' => 0, 
                            'S' => 0, 
                            'F' => 0);
        foreach($plans as $plan){
            foreach($plan->getComponents() as $comp => $val){
                if($val == 1){
                    @$components[$comp]++;
                }
            }
            if($plan->getSubmitted()){
                $submittedPlans[] = $plan;
            }
        }
        
        $html .= "<td style='white-space:nowrap;'><b>Created:</b> ".count($plans)."<br /><b>Submitted:</b> ".count($submittedPlans)."<br /></td>";
    
        $resource_data = DBFunctions::select(array('grand_data_collection'),
                                             array('*'),
                                             array('user_id' => $person->getId()));

        // Topics
        foreach(IntakeSummary::$topics as $key => $topic){
            $html .= "<td style='padding:0;' valign='top' align='right'>";
            $html .= "<table class='wikitable' style='border-collapse: collapse; table-layout: auto; width: 100%; margin-top:0px; margin-bottom:0;'>";
            $totalTime = 0;
            foreach($resource_data as $page){
                $page_name = trim($page["page"]);
                $page_data = json_decode($page["data"], true);
                
                if($page_name == "Special:Report?report=EducationModules/$key"){
                    @$html .= "<tr style=''><td nowrap>Hits:</td> <td align='right'>{$page_data['hits']}</td></tr>\n";
                }
                if($page_name == $key){
                    foreach($page_data as $key2 => $value){
                        if(strlen($key2) < 4){
                            continue;
                        }
                        if(is_array($value)){
                            continue;
                            $value = implode("|",$value);
                        }
                        $key2 = str_replace("Time", " Time", $key2);
                        if(strpos($key2, "Time")){
                            $totalTime += $value;
                        }
                    }
                }
            }
            $hours = floor($totalTime / 3600);
            $minutes = floor(($totalTime / 60) % 60);
            $seconds = $totalTime % 60;
            $value = str_pad($hours,2,"0", STR_PAD_LEFT).":".str_pad($minutes,2,"0", STR_PAD_LEFT).":".str_pad($seconds,2,"0", STR_PAD_LEFT);
            if($value != "00:00:00"){
                $html .= "<tr><td>Time:</td> <td align='right'>{$value}</td></tr>\n";
            }
            $html .= "</table>";
            $html .= "</td>";
        }
            
        // Program Library
        $html .= "<td style='padding:0;' valign='top'><table class='wikitable' style='border-collapse: collapse; table-layout: auto; width: 100%; margin-top:0px; margin-bottom:0;'>";
        foreach($resource_data as $page){
            $page_name = trim($page["page"]);
            if(strstr($page_name, "ProgramLibrary") !== false){
                $page_name = str_replace("ProgramLibrary-", "", trim($page["page"]));
                $page_data = json_decode($page["data"],true);
                $views = isset($page_data["pageCount"]) ? $page_data["pageCount"] : 0;
                $websiteClicks = isset($page_data["websiteClicks"]) ? $page_data["websiteClicks"] : 0;
                $html .= "<tr><td style='white-space:nowrap;'>$page_name</td> <td nowrap>Views: $views</td></tr>\n";
            }
        }
            
        // Links
        $html .= "</table></td>";
        $fviews = 0;
        $ftime = 0;
        $pviews = 0;
        $ptime = 0;
        $logins = 0;
        foreach($resource_data as $page){
            $page_name = trim($page["page"]);
            $page_data = json_decode($page["data"],true);
            if(strstr($page_name, "FrailtyReport") !== false){
                $fviews += isset($page_data["count"]) ? $page_data["count"] : (isset($page_data["hits"]) ? $page_data["hits"] : 0);
                $ftime += @$page_data["time"];
            }
            else if(strstr($page_name, "ProgressReport") !== false){
                $pviews += isset($page_data["count"]) ? $page_data["count"] : (isset($page_data["hits"]) ? $page_data["hits"] : 0);
                $ptime += @$page_data["time"];
            }
            else if(strstr($page_name, "loggedin") !== false){
                $logins += count($page_data["log"]);
            }
        }
        $html .= "<td align='right'>$fviews</td>";
        if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO")){
            $html .= "<td align='right'>$pviews</td>";
        }
        else{
            $html .= "<td align='right'>N/A</td>";
        }
        $html .= "<td align='right'>$logins</td>";
        return $html;
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        if(isset($_GET['updateProgramAttendance'])){
            $this->updateProgramAttendance();
            exit;
        }
        $this->getOutput()->setPageTitle("Users");
        $people = array();
        foreach(Person::getAllPeople() as $person){
            if($person->isRoleAtLeast(STAFF)){
                continue;
            }
            $people[] = $person;
        }
        $wgOut->addHTML("<style>
            div#adminDataCollectionMessages {
                position: fixed; 
                top: 122px; 
                right: 40px; 
                width: 500px;
                opacity: 0.95;
                z-index: 1001;
            }

            div#adminDataCollectionMessages > div {
                box-shadow: 3px 3px 3px rgba(0,0,0,0.5);
            }
        </style>");
        $wgOut->addHTML("<b>Active User Count:</b> ".count($people));
        if(count($people) > 0){
            $wgOut->addHTML("<table id='data' class='wikitable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                                <thead>
                                    <tr style='background:#EEEEEE;'>
                                        <th colspan='9'>User Data</th>
                                        ".self::usageHeaderTop()."
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Date Registered</th>
                                        <th>Last Logged In</th>
                                        <th>Logins after Intake</th>
                                        <th>Submitted Intake Survey</th>
                                        <th>Submitted 3Month Survey</th>
                                        <th>Intake Survey Date</th>
                                        <th>3Month Survey Date</th>
                                        ".self::usageHeaderBottom()."
                                    </tr>
                                </thead>
                                <tbody>");
            foreach($people as $person){
                $name = $person->getRealName();
                
                $evaluation1 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation1", $person->getId());
                $evaluation2 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation2", $person->getId());
                
                $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID")))/86400;
                
                                
                $submitted = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID")) ? "Yes" : "No");
                $submitted3 = $person->isRole("Provider") ? "N/A" : (
                    (AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO")) ? "Yes" : (
                        ($baseDiff >= 30*3 && $baseDiff < 30*6) ? "Due" : (
                            ($baseDiff < 30*3) ? "Not Due" : "No"
                )));
                
                $date = ($submitted == "Yes") ? substr(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID"), 0, 10) : "N/A";
                $date3 = ($submitted3 == "Yes") ? substr(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID_THREEMO"), 0, 10) : "N/A";

                $registration_str = $person->getRegistration();
                $registration_date = substr($registration_str,0,4)."-".substr($registration_str,4,2)."-".substr($registration_str,6,2);
                $touched_str = $person->getTouched();
                $touched_date = substr($touched_str,0,4)."-".substr($touched_str,4,2)."-".substr($touched_str,6,2);
                
                $logins = DataCollection::newFromUserId($person->getId(), 'loggedin');
                $nLoginsAfterIntake = 0;
                //foreach($logins as $login){
                    $log = $logins->getField('log');
                    if(is_array($log)){
                        foreach($log as $d){
                            if($date != "N/A" && $d >= $date){
                                $nLoginsAfterIntake++;
                            }
                        }
                    }
                //}
                
                $wgOut->addHTML("<tr style='background:#FFFFFF;' data-id='{$person->getId()}' VALIGN=TOP>
                                    <td>$name</td>
                                    <td>{$person->getRoleString()}</td>
                                    <td nowrap>{$registration_date}</td>
                                    <td nowrap>{$touched_date}</td>
                                    <td>{$nLoginsAfterIntake}</td>");

                $wgOut->addHTML("
                <td>{$submitted}</td>
                <td>{$submitted3}</td>
                <td>{$date}</td>
                <td>{$date3}</td>
                ".self::usageRow($person)."
                </tr>");
            }
            $wgOut->addHTML("</tbody>
                        </table>
                        <div id='adminDataCollectionMessages'></div>
                        <div id='usageDialog' style='display:none;'></div>
                        <script type='text/javascript'>
                            var buttons = {
                                exportOptions: {
                                    format: {
                                        body: function ( data, row, column, node ) {
                                            return $('<div>' + data.replaceAll('<br>', String.fromCharCode(10)) + '</div>').text().trim();
                                        }
                                    }
                                }
                            };
                            table = $('#data').DataTable({
                                aLengthMenu: [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                                iDisplayLength: -1,
                                'dom': 'Blfrtip',
                                'buttons': [ $.extend( true, {}, buttons, {
                                        extend: 'excelHtml5',
                                        text: 'Excel'
                                    }
                                )],
                                scrollX: true,
                                scrollY: $('#bodyContent').height() - 400
                            });
                            $('#data_length').append('<button id=\"copyEmails\" style=\"margin-left: 15px;\">Copy Visible Email Addresses</button>');
                            $('#copyEmails').click(function(){
                                var emails = [];
                                $('.emailCell:visible').each(function(){
                                    var email = $(this).text().trim();
                                    if(email != ''){
                                        emails.push(email);
                                    }
                                });
                                navigator.clipboard.writeText(emails.join(','));
                                clearAllMessages('#adminDataCollectionMessages');
                                $('#adminDataCollectionMessages').stop();
                                $('#adminDataCollectionMessages').show();
                                $('#adminDataCollectionMessages').css('opacity', 0.95);
                                addSuccess('Visible email addresses copied', false, '#adminDataCollectionMessages');
                                $('#adminDataCollectionMessages').fadeOut(5000);
                            });
                            _.defer(function(){
                                table.draw();
                            });
                            
                            $('.viewUsage').click(function(){
                                var id = $(this).closest('tr').attr('data-id');
                                $('#data_' + id + ' input[type=date_tmp]').attr('type', 'date');
                                $('#usageDialog').html($('#data_' + id).html());
                                $('#usageDialog').dialog({
                                    width: 'auto',
                                    height: 'auto',
                                    title: 'Program Attendance',
                                    buttons: {
                                        'Save' : function(e){
                                            var dataStr = $('#usageDialog form').serialize();
                                            $(e.currentTarget).prop('disabled', true);
                                            $.ajax({
                                                type: 'POST',
                                                url: $('#usageDialog form').attr('action'),
                                                data: dataStr,
                                                success: function (data) {
                                                    $('#usageDialog input[type=submit]').click();
                                                    $('#usageDialog input').each(function(i, el){
                                                        $(el).attr('value', $(el).val());
                                                        if($(el).prop('checked')){
                                                            $(el).attr('checked', 'checked');
                                                        }
                                                        else{
                                                            $(el).removeAttr('checked');
                                                        }
                                                    });
                                                    $('#data_' + id).html($('#usageDialog').html());
                                                    $(e.currentTarget).prop('disabled', false);
                                                    $(this).dialog('close');
                                                }.bind(this),
                                                error: function(data){
                                                    $(e.currentTarget).prop('disabled', false);
                                                }.bind(this)
                                            });
                                        },
                                        'Cancel' : function(){
                                            $(this).dialog('close');
                                        }
                                    }
                                });
                            });
                        </script>");
        }
    }

    static function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
        if ($userId === null) {
          $userId = $this->user_id;
        }
        $blb = new ReportBlob($blobType, $year, $userId, $projectId);
        $addr = ReportBlob::create_address($reportType, $reportSection, $blobItem, $subItem);
        $result = $blb->load($addr);
        $data = $blb->getData();

        return $data;
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "AdminDataCollectionAustralia") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Users (AUS)", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollectionAustralia", $selected);
        }
        return true;
    }

}

?>

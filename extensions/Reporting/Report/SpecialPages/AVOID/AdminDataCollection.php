<?php
require_once('AdminDataCollection.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminDataCollection'] = 'AdminDataCollection'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminDataCollection'] = $dir . 'AdminDataCollection.i18n.php';
$wgSpecialPageGroups['AdminDataCollection'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminDataCollection::createSubTabs';

class AdminDataCollection extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("AdminDataCollection", STAFF.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Usage Stats");
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
        $topics = array("IngredientsForChange","Activity","Vaccination","OptimizeMedication","Interact","DietAndNutrition","Sleep","FallsPrevention");
        if(count($people) > 0){
            $wgOut->addHTML("<table id='data' class='wikitable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                                <thead>
                                    <tr style='background:#EEEEEE;'>
                                        <th rowspan='2'>Name</th>
                                        <th rowspan='2'>Email</th>
                                        <th rowspan='2'>Age</th>
                                        <th rowspan='2'>Postal Code</th>
                                        <th rowspan='2'>Role</th>
                                        <th rowspan='2'>Date Registered</th>
                                        <th rowspan='2'>Last Logged In</th>
                                        <th rowspan='2'>Extra</th>
                                        <th rowspan='2'>Hear about us</th>
                                        <th rowspan='2'>In person opportunity</th>
                                        <th rowspan='2'>Fitbit</th>
                                        <th rowspan='2'>Submitted Intake Survey</th>
                                        <th rowspan='2'>Submitted 3Month Survey</th>
                                        <th rowspan='2'>Submitted 6Month Survey</th>
                                        <th rowspan='2'>Action Plans</th>
                                        <th colspan='10'>Data Collected</th>
                                    </tr>
                                    <tr>
                                        <th>".implode("</th><th>", $topics)."</th>
                                        <th>Program Library</th>
                                        <th>Links</th>
                                    </tr>
                                </thead>
                                <tbody>");
            foreach($people as $person){
                $name = $person->getRealName();
                $email = $person->getEmail();
                $avoid_age = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "avoid_age", $person->getId());
                $avoid_age = str_replace("less than", "<", $avoid_age);
                $avoid_age = str_replace("more than", ">", $avoid_age);
                $hear = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "program_avoid", $person->getId());
                $hear = ($hear == "") ? $person->getExtra('hearField', '') : $hear;
                $postal_code = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
                $evaluation1 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation1", $person->getId());
                $evaluation2 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation2", $person->getId());
                $fitbit1 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "FITBIT", "fitbit1", $person->getId());
                $fitbit2 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "FITBIT", "fitbit2", $person->getId());
                $submitted = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID")) ? "Yes" : "No");
                $submitted3 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO")) ? "Yes" : "No");
                $submitted6 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO")) ? "Yes" : "No");
                $registration_str = $person->getRegistration();
                $registration_date = substr($registration_str,0,4)."-".substr($registration_str,4,2)."-".substr($registration_str,6,2);
                $touched_str = $person->getTouched();
                $touched_date = substr($touched_str,0,4)."-".substr($touched_str,4,2)."-".substr($touched_str,6,2);
                $wgOut->addHTML("<tr style='background:#FFFFFF;' VALIGN=TOP>
                                    <td>$name</td>
                                    <td class='emailCell'>$email</td>
                                    <td nowrap>$avoid_age</td>
                                    <td>$postal_code</td>
                                    <td>{$person->getRoleString()}</td>
                                    <td nowrap>{$registration_date}</td>
                                    <td nowrap>{$touched_date}</td>");

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

                $phone = $person->getExtra('phone', $person->getPhoneNumber());
                //grab clinician data
                $age_lovedone = $person->getExtra('ageOfLovedOne', '');
                $age = $person->getExtra('ageField', '');
                $practice = $person->getExtra('practiceField', '');
                $rolefield = $person->getExtra('roleField', '');
                
                $wgOut->addHTML("<td style='white-space:nowrap;'>");
                if($phone != ''){
                    $wgOut->addHTML("<b>Phone:</b> $phone<br />");
                }
                if($age_lovedone != ''){
                    $wgOut->addHTML("<b>AgeOfLovedOne:</b> $age_lovedone<br />");
                }
                if($age != ''){
                    $wgOut->addHTML("<b>Age:</b> $age<br />");
                }
                if($practice != ''){
                    $wgOut->addHTML("<b>Practice:</b> $practice<br />");
                }
                if($rolefield != ''){
                    $wgOut->addHTML("<b>Role:</b> $rolefield<br />");
                }
                $wgOut->addHTML("</td>");
                $wgOut->addHTML("<td>{$hear}</td>");
                $wgOut->addHTML("<td>
                    <b>Q1:</b> {$evaluation1}<br />
                    <b>Q2:</b> {$evaluation2}
                </td>
                <td>
                    <b>Q1:</b> {$fitbit1}<br />
                    <b>Q2:</b> {$fitbit2}
                </td>
                <td>{$submitted}</td>
                <td>{$submitted3}</td>
                <td>{$submitted6}</td>
                <td style='white-space:nowrap;'>
                    <b>Created:</b> ".count($plans)."<br />
                    <b>Submitted:</b> ".count($submittedPlans)."<br />
                </td>");

                $resource_data_sql = "SELECT * FROM `grand_data_collection` WHERE user_id = {$person->getId()}";
                $resource_data = DBFunctions::execSQL($resource_data_sql);
                $links = array();

                // Topics
                foreach($topics as $topic){
                    $wgOut->addHTML("<td style='padding:0;'>");
                    foreach($resource_data as $page){
                        $page_name = trim($page["page"]);
                        $page_data = json_decode($page["data"], true);
                        if($page_name == $topic){
                            $wgOut->addHTML("<table style='border-collapse: collapse; table-layout: auto; width: 100%;'>");
                            $x_num = 0;
                            foreach($page_data as $key => $value){
                                if(strlen($key) < 4){
                                    continue;
                                }
                                if(is_array($value)){
                                    continue;
                                    $value = implode("|",$value);
                                }
                                $key = str_replace("PageCount", " Views", $key);
                                $key = str_replace("Time", " Time", $key);
                                if(strpos($key, "Time")){
                                    $init = $value;
                                    $hours = floor($init / 3600);
                                    $minutes = floor(($init / 60) % 60);
                                    $seconds = $init % 60;
                                    $value = "$hours:$minutes:$seconds";
                                }
                                if($x_num%2==0){
                                    $wgOut->addHTML("<tr style='background-color:#ececec'><td nowrap>$key</td><td align='right'>$value</td></tr>");
                                }
                                else{
                                    $wgOut->addHTML("<tr style=''><td nowrap>$key</td><td align='right'>$value</td></tr>");
                                }
                                $x_num++;
                            }
                            $wgOut->addHTML("</table>");
                        }
                    }
                    $wgOut->addHTML("</td>");
                }
                
                // Program Library
                $wgOut->addHTML("<td style='padding:0;'><table style='border-collapse: collapse; table-layout: auto; width: 100%;'>");
                $x_num = 0;
                foreach($resource_data as $page){
                    $page_name = trim($page["page"]);
                    if(strstr($page_name, "ProgramLibrary") !== false){
                        $page_name = str_replace("ProgramLibrary-", "", trim($page["page"]));
                        $page_data = json_decode($page["data"],true);
                        $views = isset($page_data["pageCount"]) ? $page_data["pageCount"] : 0;
                        $websiteClicks = isset($page_data["websiteClicks"]) ? $page_data["websiteClicks"] : 0;
                        if($x_num%2==0){
                            $wgOut->addHTML("
                                <tr style='background-color:#ececec'>
                                    <td rowspan='2' style='white-space:nowrap;'>$page_name</td>
                                    <td nowrap>Views: $views</td>
                                </tr>
                                <tr style='background-color:#ececec'>
                                    <td nowrap>Website: $websiteClicks</td>
                                </tr>");
                        }
                        else{
                            $wgOut->addHTML("
                                <tr style=''>
                                    <td rowspan='2' style='white-space:nowrap;'>$page_name</td>
                                    <td nowrap>Views: $views</td>
                                </tr>
                                <tr>
                                    <td nowrap>Website: $websiteClicks</td>
                                </tr>");
                        }
                        $x_num++;
                    }
                    else if(!in_array($page_name, $topics) && $page_name != ""){
                        $links[] = $page;
                    }
                }
                
                // Links
                $wgOut->addHTML("</table></td><td style='padding:0;'><table style='border-collapse: collapse; table-layout: auto; width: 100%;'>");
                $x_num = 0;
                foreach($links as $link){
                    $page_name = trim($link["page"]);
                    $page_data = json_decode($link["data"],true);
                    $views = isset($page_data["count"]) ? $page_data["count"] : (isset($page_data["hits"]) ? $page_data["hits"] : 0);
                    $time = @$page_data["time"];
                    if($x_num%2==0){
                        $wgOut->addHTML("
                            <tr style='background-color:#ececec'>
                                <td rowspan='2'>$page_name</td>
                                <td nowrap>Views: $views</td>
                            </tr>
                            <tr style='background-color:#ececec'>
                                <td nowrap>Time: $time</td>
                            </tr>");
                    }
                    else{
                        $wgOut->addHTML("
                            <tr style=''>
                                <td rowspan='2'>$page_name</td>
                                <td nowrap>Views: $views</td>
                            </tr>
                            <tr style=''>
                                <td nowrap>Time: $time</td>
                            </tr>");
                    }
                    $x_num++;
                }

                $wgOut->addHTML("</table></td>");
            }
            $wgOut->addHTML("</tbody>
                            </table>
                            <div id='adminDataCollectionMessages'></div>
                            <script type='text/javascript'>
                                table = $('#data').DataTable({
                                    aLengthMenu: [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                                    iDisplayLength: -1,
                                    'dom': 'Blfrtip',
                                    'buttons': [
                                        'excel'
                                    ],
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
            $selected = @($wgTitle->getText() == "AdminDataCollection") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Usage Stats", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollection", $selected);
        }
        return true;
    }

}

?>
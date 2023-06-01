<?php
require_once('AdminDataCollection.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminDataCollection'] = 'AdminDataCollection'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminDataCollection'] = $dir . 'AdminDataCollection.i18n.php';
$wgSpecialPageGroups['AdminDataCollection'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminDataCollection::createSubTabs';

class AdminDataCollection extends SpecialPage{

    static $programs = array("otago" => "Otago", 
                             "coached_by_peer" => "Coached by Peer", 
                             "peer_choach" => "Peer-Coach",
                             "community_connector" => "Community Connector",
                             "peer_navigator" => "Peer Navigator",
                             "ask_an_expert" => "Ask an Expert",
                             "tech_training" => "Tech Training");

    function __construct() {
        SpecialPage::__construct("AdminDataCollection", STAFF.'+', true);
    }
    
    function dataCollectionTable($person){
        global $wgServer, $wgScriptPath;
        $html = "<div id='data_{$person->getId()}' style='display:none;'>";
        $html .= "<form action='{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollection?updateProgramAttendance&user={$person->getId()}' method='post'><table class='wikitable program_attendance' cellpadding='5' cellspacing='1' style='width:100%;'>
                    <thead>
                        <tr>
                            <th>".implode("</th><th>", AdminDataCollection::$programs)."</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        $html .= "<tr>";
        foreach(AdminDataCollection::$programs as $key => $program){
            $checked = (IntakeSummary::getBlobData("ATTENDANCE", "{$key}", $person, 0, "RP_SUMMARY") == 1) ? "checked" : "";
            $span = ($checked != "") ? "Yes" : "No";
            $html .= "<td align='center' style='width:1px;'>
                <input type='hidden' value='0' name='{$key}' />
                <input type='checkbox' value='1' name='{$key}' $checked />
                <span style='display:none;'>$span</span>
            </td>";
        }
        
        $html .= "</tr><tr>";
        foreach(AdminDataCollection::$programs as $key => $program){
            $date = IntakeSummary::getBlobData("ATTENDANCE", "{$key}_date", $person, 0, "RP_SUMMARY");
            $html .= "<td align='center' style='width:1px;'>
                <input type='date' value='$date' name='{$key}_date' style='width: 8em;' />
                <span style='display:none;'>$date</span>
            </td>";
        }
        
        $html .= "</tr></tbody></table></form>";
        
        $html .= "</div>";
        return $html;
    }
    
    function updateProgramAttendance(){
        $person = Person::newFromId($_GET['user']);
        foreach($_POST as $key => $value){
            IntakeSummary::saveBlobData($value, "ATTENDANCE", "{$key}", $person, 0, "RP_SUMMARY");
        }
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
            foreach($people as $person){
                $wgOut->addHTML($this->dataCollectionTable($person));
            }
            $wgOut->addHTML("<table id='data' class='wikitable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                                <thead>
                                    <tr style='background:#EEEEEE;'>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Age</th>
                                        <th>Postal Code</th>
                                        <th>Role</th>
                                        <th>Date Registered</th>
                                        <th>Last Logged In</th>
                                        <th>Extra</th>
                                        <th>Hear about us</th>
                                        <th>In person opportunity</th>
                                        <th>Fitbit</th>
                                        <th>Attendance</th>
                                        <th>Submitted Intake Survey</th>
                                        <th>Submitted 3Month Survey</th>
                                        <th>Submitted 6Month Survey</th>
                                        <th>Submitted 9Month Survey</th>
                                        <th>Submitted 12Month Survey</th>
                                    </tr>
                                </thead>
                                <tbody>");
            foreach($people as $person){
                $name = $person->getRealName();
                $email = $person->getEmail();
                $avoid_age = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "avoid_age", $person->getId());
                $avoid_age = str_replace("less than", "<", $avoid_age);
                $avoid_age = str_replace("more than", ">", $avoid_age);
                
                $hear = implode("<br />", array_filter(array($this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "program_avoid", $person->getId()), 
                                                             $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "PROGRAMLOCATIONSPECIFY", $person->getId()),
                                                             $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "platform_avoid", $person->getId()),
                                                             $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "PROGRAMPLATFORMOTHERSPECIFY", $person->getId()),
                                                             $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "PROGRAMOTHERSPECIFY", $person->getId()))));
                $hear = ($hear == "") ? implode("<br />", array_filter(array($person->getExtra('hearField', ''),
                                                                             $person->getExtra('hearLocationSpecify'),
                                                                             $person->getExtra('hearPlatformSpecify'),
                                                                             $person->getExtra('hearPlatformOtherSpecify'),
                                                                             $person->getExtra('hearProgramOtherSpecify')))) : $hear;
                
                $postal_code = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
                $evaluation1 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation1", $person->getId());
                $evaluation2 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "ONSITE_EVALUATION", "evaluation2", $person->getId());
                $fitbit1 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "FITBIT", "fitbit1", $person->getId());
                $fitbit2 = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "FITBIT", "fitbit2", $person->getId());
                $submitted = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID")) ? "Yes" : "No");
                $submitted3 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO")) ? "Yes" : "No");
                $submitted6 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO")) ? "Yes" : "No");
                $submitted9 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_NINEMO")) ? "Yes" : "No");
                $submitted12 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_TWELVEMO")) ? "Yes" : "No");
                $registration_str = $person->getRegistration();
                $registration_date = substr($registration_str,0,4)."-".substr($registration_str,4,2)."-".substr($registration_str,6,2);
                $touched_str = $person->getTouched();
                $touched_date = substr($touched_str,0,4)."-".substr($touched_str,4,2)."-".substr($touched_str,6,2);
                $wgOut->addHTML("<tr style='background:#FFFFFF;' data-id='{$person->getId()}' VALIGN=TOP>
                                    <td>$name</td>
                                    <td class='emailCell'>$email</td>
                                    <td nowrap>$avoid_age</td>
                                    <td>$postal_code</td>
                                    <td>{$person->getRoleString()}</td>
                                    <td nowrap>{$registration_date}</td>
                                    <td nowrap>{$touched_date}</td>");

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
                <td align='center'><a href='#' class='viewUsage'>View</a></td>
                <td>{$submitted}</td>
                <td>{$submitted3}</td>
                <td>{$submitted6}</td>
                <td>{$submitted9}</td>
                <td>{$submitted12}</td>
                </tr>");
            }
            $wgOut->addHTML("</tbody>
                        </table>
                        <div id='adminDataCollectionMessages'></div>
                        <div id='usageDialog' style='display:none;'></div>
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
                            
                            $('.viewUsage').click(function(){
                                var id = $(this).closest('tr').attr('data-id');
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
            $selected = @($wgTitle->getText() == "AdminDataCollection") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Users", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollection", $selected);
        }
        return true;
    }

}

?>

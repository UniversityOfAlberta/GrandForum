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
                $submitted9 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_NINEMO")) ? "Yes" : "No");
                $submitted12 = $person->isRole("Provider") ? "N/A" : ((AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_TWELVEMO")) ? "Yes" : "No");
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
                <td>{$submitted9}</td>
                <td>{$submitted12}</td>
                </tr>");
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
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Users", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollection", $selected);
        }
        return true;
    }

}

?>

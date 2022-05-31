<?php
require_once('AdminDataCollection.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminDataCollection'] = 'AdminDataCollection'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminDataCollection'] = $dir . 'AdminDataCollection.i18n.php';
$wgSpecialPageGroups['AdminDataCollection'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminDataCollection::createSubTabs';

class AdminDataCollection extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("AdminDataCollection", HQP.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Admin Data Collection");
        $people = Person::getAllPeople();
        $wgOut->addHTML("<b>Active User Count:</b> ".count($people));
        if(count($people) > 0){
            $wgOut->addHTML("<table class='wikitable sortable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                                <tr style='background:#EEEEEE;'>
                                    <th>Name</th> <th>Age</th> <th>Postal Code</th><th>Role</th><th>Date Registered</th><th>Extra</th><th>Data Collected</th>
                                </tr>");
            foreach($people as $person){
                $name = $person->getRealName();
                $avoid_age = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "avoid_age", $person->getId());
                $avoid_age = str_replace("less than", "<", $avoid_age);
                $avoid_age = str_replace("more than", ">", $avoid_age);
                $postal_code = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
                $registration_str = $person->getRegistration();
                $registration_date = substr($registration_str,0,4)."-".substr($registration_str,4,2)."-".substr($registration_str,6,2);
                $wgOut->addHTML("<tr style='background:#FFFFFF;' VALIGN=TOP>
                                    <td>$name</td> <td nowrap>$avoid_age</td> <td>$postal_code</td><td>{$person->getRoleString()}</td><td nowrap>{$registration_date}</td>");

                //grab clinician data
                $age_lovedone = $person->getExtra('ageOfLovedOne', '');
                $age = $person->getExtra('ageField', '');
                $practice = $person->getExtra('practiceField', '');
                $rolefield = $person->getExtra('roleField', '');
                
                $wgOut->addHTML("<td style='white-space:nowrap;'>");
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

                $resource_data_sql = "SELECT * FROM `grand_data_collection` WHERE user_id = {$person->getId()}";
                $resource_data = DBFunctions::execSQL($resource_data_sql);
                $links = array();
                foreach($resource_data as $page){
                    $topics = array("IngredientsForChange","Activity","OptimizeMedication","Vaccination","Interact","DietAndNutrition","Sleep","FallsPrevention");
                    $page_name = trim($page["page"]);
                    $page_data = json_decode($page["data"], true);
                    if($page_name != ""){
                        if(in_array($page_name, $topics)){
                            $wgOut->addHTML("<td VALIGN=TOP><b>$page_name</b><br />");
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
                        else{
                            $links[] = $page;
                        }
                    }
                }
                if(count($links)>0){
                    $wgOut->addHTML("<td VALIGN=TOP><b>Resource Links</b><br /><table>");
                    $x_num = 0;
                    foreach($links as $link){
                        $page_name = trim($link["page"]);
                        $page_data = json_decode($link["data"],true);
                        $views = isset($page_data["count"]) ? $page_data["count"] : 0;
                        if($x_num%2==0){
                            $wgOut->addHTML("
                                <tr style='background-color:#ececec'><td>
                                    $page_name</td><td nowrap>
                                    Views: $views
                                </td></tr>");
                        }
                        else{
                            $wgOut->addHTML("
                                <tr style=''><td>
                                $page_name</td><td nowrap>
                                Views: $views
                                </td>
                                </tr>");
                        }
                        $x_num++;
                    }
                    $wgOut->addHTML("</table></td>");
                }
            }
            $wgOut->addHTML("</table>");
        }
        else{
            $wgOut->addHTML("You have not created any polls.");
        }
    }

    function getBlobValue($blobType, $year, $reportType, $reportSection, $blobItem, $userId=null, $projectId=0, $subItem=0){
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
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Data Collection", "{$wgServer}{$wgScriptPath}/index.php/Special:AdminDataCollection", $selected);
        }
        return true;
    }

}

?>

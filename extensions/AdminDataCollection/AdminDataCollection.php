<?php
require_once('AdminDataCollection.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminDataCollection'] = 'AdminDataCollection'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminDataCollection'] = $dir . 'AdminDataCollection.i18n.php';
$wgSpecialPageGroups['AdminDataCollection'] = 'other-tools';


class AdminDataCollection extends SpecialPage{

    function __construct() {
	SpecialPage::__construct("AdminDataCollection", HQP.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
	$this->getOutput()->setPageTitle("Admin Data Collection");
	$people = Person::getAllPeople();
	$wgOut->addHTML("Active User Count:".count($people));
	if(count($people) > 0){
            $wgOut->addHTML("<table class='wikitable sortable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                        <tr style='background:#EEEEEE;'>
                            <th>Name</th> <th>Age</th> <th>Postal Code</th><th>Role</th><th>Data Collected</th>
			</tr>");
            foreach($people as $person){
                $name = $person->getRealName();
		$avoid_age = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "avoid_age", $person->getId());
		$avoid_age = str_replace("less than", "<", $avoid_age);
                $avoid_age = str_replace("more than", ">", $avoid_age);
                $postal_code = $this->getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
                $wgOut->addHTML("<tr style='background:#FFFFFF;' VALIGN=TOP>
                            <td>$name</td> <td nowrap>$avoid_age</td> <td>$postal_code</td><td>{$person->getRoleString()}</td>");
                $resource_data_sql = "SELECT * FROM `grand_data_collection` WHERE user_id = {$person->getId()}";
                $resource_data = DBFunctions::execSQL($resource_data_sql);
                $links = array();
                foreach($resource_data as $page){
                    $topics = array("IngredientsForChange","Activity","OptimizeMedication","Vaccination","Interact","DietAndNutrition");
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
		$wgOut->addHTML("<td VALIGN=TOP>");
		foreach($links as $link){
		    $page_name = trim($link["page"]);
		    $page_data = json_decode($link["data"],true);
		    $wgOut->addHTML("
				<b>$page_name</b><br />
				Views: {$page_data["count"]}
				<br /><br />");

		}
		$wgOut->addHTML("</td>");
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


}

?>

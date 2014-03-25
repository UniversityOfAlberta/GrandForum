<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';
$wgHooks['SubLevelTabs'][] = 'AcknowledgementsTable::createSubTabs';

$wgSpecialPages['AcknowledgementsTable'] = 'AcknowledgementsTable';
$wgExtensionMessagesFiles['AcknowledgementsTable'] = $dir . 'AcknowledgementsTable.i18n.php';
$wgSpecialPageGroups['AcknowledgementsTable'] = 'network-tools';


function runAcknowledgementsTable($par) {
	AcknowledgementsTable::run($par);
}

function getack($action, $article){
    global $wgOut;
    if($action == 'getack'){
        $ack = Acknowledgement::newFromMd5(@$_GET['ack']);
        if($ack == null || $ack->getPdf() == ""){
            return true;
        }
        else{
            $wgOut->disable();
	        ob_clean();
	        header('Content-Type: application/pdf');
	        header('Content-Disposition: attachment; filename="Acknowledgement_'.$ack->getName().'.pdf"');
	        header('Cache-Control: private, max-age=0, must-revalidate');
	        header('Pragma: public');
	        ini_set('zlib.output_compression','0');
	        echo $ack->getPdf();
	        exit;
	    }
    }
    return true;
}

class AcknowledgementsTable extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('AcknowledgementsTable');
		SpecialPage::SpecialPage("AcknowledgementsTable", STAFF.'+', true, 'runAcknowledgementsTable');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	    
	    if(isset($_POST['submit'])){
            if(isset($_POST['name']) && 
               isset($_FILES['pdf']) && 
               $_POST['name'] != "" &&
               $_FILES['pdf']['size'] > 0 &&
               $_FILES['pdf']['type'] == 'application/pdf'){
                $person = Person::newFromNameLike($_POST['name']);
            	if($person == null || $person->getName() == ""){
                    $id = -1;
                	$name = @addslashes($_POST['name']);
                }
                else{
                    $id = $person->getId();
                	$name = $person->getName();
                }
                
                $university = @addslashes($_POST['university']);
                $php_date = date_create_from_format('d-m-Y', $_POST['date']);
                if($php_date){
			        $date =  $php_date->format('Y-m-d');
			    }else{
			    	$date = @addslashes($_POST['date']);
			    }
                $super = Person::newFromNameLike($_POST['supervisor']);
                if($super == null || $super->getName() == ""){
					$supervisor = @addslashes($_POST['supervisor']);
                }else{
                	$supervisor = $super->getName();
                }

                $filename = $_FILES['pdf']['tmp_name'];
                $pdf = file_get_contents($_FILES['pdf']['tmp_name']);
                $md5 = md5($pdf);
                $pdf = mysql_real_escape_string($pdf);
                
                $sql = "INSERT INTO `grand_acknowledgements`
                       (`user_id`, `user_name`, `university` , `date` ,`supervisor`,  `md5`,  `pdf`)
                VALUES ('$id'    , '$name'    , '$university', '$date','$supervisor', '$md5', '$pdf')";
                DBFunctions::execSQL($sql, true);
                Person::$cache = array();
                Acknowledgement::$cache = array();
                $wgMessage->addSuccess("The acknowledgement was added successfully");
            }
            else{
                if(!isset($_POST['name']) || $_POST['name'] == ""){
                    $wgMessage->addError("A name must be provided.");
                }
                if(!isset($_FILES['pdf']) || $_FILES['pdf']['size'] == 0){
                    $wgMessage->addError("A pdf must be provided");
                }
                if(isset($_FILES['pdf']) && $_FILES['pdf']['type'] != 'application/pdf'){
                    $wgMessage->addError("The uploaded file is not a pdf file.");
                }
            }
        }
	    
	    $hqps = array();
	    $nis = array();
	    
	    $people = Person::getAllPeople();
	    foreach($people as $person){
	        $roles = $person->getRoles(true);
	        foreach($roles as $role){
	            if($role->getRole() == HQP){
	                $hqps[$person->getId()] = $person;
	            }
	            else if($role->getRole() == PNI || 
	                    $role->getRole() == CNI){
	                $nis[$person->getId()] = $person;
	            }
	        }
	    }
	    
	    $wgOut->setPageTitle("Acknowledgements");
	    $wgOut->addHTML("<div id='ackTabs'>
	                        <ul>
		                        <li><a href='#hqp'>".HQP."</a></li>
		                        <li><a href='#ni'>NI</a></li>
		                        <li><a href='#unreg'>Unregistered</a></li>
		                        <li><a href='#addAck'>Add Acknowledgement</a></li>
	                        </ul>
	                        <div id='hqp'>");
	    AcknowledgementsTable::hqpTable($hqps);           
	    $wgOut->addHTML("</div>
	                        <div id='ni'>");
		AcknowledgementsTable::niTable($nis);
	    $wgOut->addHTML("</div>
	                        <div id='unreg'>");
		AcknowledgementsTable::unregTable();
	    $wgOut->addHTML("</div>
	                        <div id='addAck'>");
	    AcknowledgementsTable::addAck();
	    $wgOut->addHTML("</div>");
	    $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
	                                $('.indexTable').dataTable({'iDisplayLength': 100,
	                                                            'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
                                    $('.dataTables_filter input').css('width', 250);
                                    $('#ackTabs').tabs();
                                    
                                    $('input[name=date]').datepicker();
                                    $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
                                });
                            </script>");
    }
    
    static function hqpTable($hqps){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Name</th>
	                                <th>Type</th>
	                                <th>University</th>
	                                <th>Date</th>
	                                <th>PDF</th>
	                                <th>Supervisor</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");
	    foreach($hqps as $hqp){
	        $uni = $hqp->getUniversity();
            $title = $uni['position'];
            $university = $uni['university'];
            $type = "Other";
            if($title == "Masters Student" ||
               $title == "PhD Student" || 
               $title == "PostDoc"){
                $type = "Student";
            }
	        $inactive = "";
	        if($hqp->isRole(INACTIVE)){
	            $inactive = " (Inactive)";
	        }
	        $acks = $hqp->getAcknowledgements();
	        if(count($acks) == 0){
	            $supervisors = $hqp->getSupervisors();
	            if(count($supervisors) > 0){
	                foreach($supervisors as $supervisor){
	                    $wgOut->addHTML("<tr>
	                                        <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>{$inactive}</td>
	                                        <td>$type</td>
	                                        <td>$university</td>
	                                        <td></td>
	                                        <td></td>
	                                        <td>{$supervisor->getReversedName()}</td>
	                                     </tr>\n");
	                }
	            }
	            else{
	                $wgOut->addHTML("<tr>
                                        <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>{$inactive}</td>
                                        <td>$type</td>
                                        <td>$university</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                     </tr>\n");
	            }
	        }
	        else{
	            $countedSupervisors = array();
	            foreach($acks as $ack){
	                $wgOut->addHTML("<tr>
	                                    <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>{$inactive}</td>
	                                    <td>$type</td>
	                                    <td>{$ack->getUniversity()}</td>
	                                    <td align='center'>{$ack->getDate()}</td>
	                                    <td><a href='{$ack->getUrl()}'>Download PDF</a></td>
	                                    <td>{$ack->getSupervisor()}</td>
	                                 </tr>\n");
	                $countedSupervisors[$ack->getSupervisor()] = $ack->getSupervisor();
	            }
	            foreach($hqp->getSupervisors() as $supervisor){
	                if(!isset($countedSupervisors[$supervisor->getName()]) &&
	                   !isset($countedSupervisors[$supervisor->getNameForForms()])){
	                      $wgOut->addHTML("<tr>
	                                        <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>{$inactive}</td>
	                                        <td>$type</td>
	                                        <td>$university</td>
	                                        <td></td>
	                                        <td></td>
	                                        <td>{$supervisor->getReversedName()}</td>
	                                     </tr>\n");
	                }
	            }
	        }
	    }
	    $wgOut->addHTML("</tbody></table>");
    }
    
    static function niTable($nis){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Name</th>
	                                <th>Type</th>
	                                <th>University</th>
	                                <th>Date</th>
	                                <th>PDF</th>
	                                <th>Supervisor</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");
	    foreach($nis as $ni){
	        $uni = $ni->getUniversity();
            $university = $uni['university'];

	        $roles = $ni->getRoles();
	        $r = array();
	        foreach($roles as $role){
	            $r[] = $role->getRole();
	        }
	        $type = implode(", ", $r);
	        $inactive = "";
	        if($ni->isRole(INACTIVE)){
	            $inactive = " (Inactive)";
	        }
	        $acks = $ni->getAcknowledgements();
	        if(count($acks) == 0){
	            $wgOut->addHTML("<tr>
	                                <td><a href='{$ni->getUrl()}' target='_blank'>{$ni->getReversedName()}</a>{$inactive}</td>
	                                <td>$type</td>
	                                <td>$university</td>
	                                <td></td>
	                                <td></td>
	                                <td></td>
	                             </tr>\n");
	        }
	        else{
	            foreach($acks as $ack){
	                
	                $wgOut->addHTML("<tr>
	                                    <td><a href='{$ni->getUrl()}' target='_blank'>{$ni->getReversedName()}</a>{$inactive}</td>
	                                    <td>$type</td>
	                                    <td>{$ack->getUniversity()}</td>
	                                    <td align='center'>{$ack->getDate()}</td>
	                                    <td><a href='{$ack->getUrl()}'>Download PDF</a></td>
	                                    <td>{$ack->getSupervisor()}</td>
	                                 </tr>\n");
	            }
	        }
	    }
	    $wgOut->addHTML("</tbody></table>");
    }
    
    function unregTable(){
        global $wgOut, $wgServer, $wgScriptPath;
        $acks = Acknowledgement::getAllAcknowledgements();
        $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Name</th>
	                                <th>University</th>
	                                <th>Date (DD-MM-YY)</th>
	                                <th>PDF</th>
	                                <th>Supervisor</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");
        foreach($acks as $ack){
            if($ack->getPerson() == null || $ack->getPerson()->getName() == ""){
                $wgOut->addHTML("<tr>
                                    <td>{$ack->getName()}</td>
                                    <td>{$ack->getUniversity()}</td>
                                    <td align='center'>{$ack->getDate()}</td>
                                    <td><a href='{$ack->getUrl()}'>Download PDF</a></td>
                                    <td>{$ack->getSupervisor()}</td>
                                 </tr>\n");
            }
        }
        $wgOut->addHTML("</tbody></table>");
    }
    
    function addAck(){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage;
        $people = Person::getAllPeople();
        $names = array();
        foreach($people as $person){
            $names[] = $person->getNameForForms();
        }
        $universities = Person::getAllUniversities();
        
        $wgOut->addScript("<script type='text/javascript'>
            var names = ['".implode("',\n'", $names)."'];
            var uniNames = ['".implode("',\n'", $universities)."'];
            $(document).ready(function(){
                $('input[name=name]').autocomplete({source: names});
                $('input[name=university]').autocomplete({source: uniNames});
                $('input[name=supervisor]').autocomplete({source: names});
            });
        </script>");
        
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:AcknowledgementsTable' method='post' enctype='multipart/form-data'>
	                            <table>
	                                <tr><td align='right'><b>Name:</b></td><td><input type='text' name='name' /></td></tr>
	                                <tr><td align='right'><b>Date:</b></td><td><input type='text' name='date' /></td></tr>
	                                <tr><td align='right'><b>University:</b></td><td><input type='text' name='university' /></td></tr>
	                                <tr><td align='right'><b>Supervisor:</b></td><td><input type='text' name='supervisor' /></td></tr>
	                                <tr><td align='right'><b>PDF Upload:</b></td><td><input type='file' name='pdf' /></td></tr>
	                                <tr><td align='right'></td><td><input type='submit' name='submit' value='Add Acknowledgement' /></td></tr>
	                            </table>
	                            </form>
	                        </div>");
    }
    
    static function createSubTabs($tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "AcknowledgementsTable") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Acknowledgements", "$wgServer$wgScriptPath/index.php/Special:AcknowledgementsTable", $selected);
	    }
	    return true;
    }
}

?>

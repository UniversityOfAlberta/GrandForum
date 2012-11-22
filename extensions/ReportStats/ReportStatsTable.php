<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['ReportStatsTable'] = 'ReportStatsTable';
$wgExtensionMessagesFiles['ReportStatsTable'] = $dir . 'ReportStatsTable.i18n.php';
$wgSpecialPageGroups['ReportStatsTable'] = 'grand-tools';

function runReportStatsTable($par) {
	ReportStatsTable::run($par);
}

/*
function getack($action, $article){
    global $wgOut;
    if($action == 'getack'){
        $ack = ReportStats::newFromMd5(@$_GET['ack']);
        if($ack == null || $ack->getPdf() == ""){
            return false;
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
    return false;
}*/

class ReportStatsTable extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('ReportStatsTable');
		SpecialPage::SpecialPage("ReportStatsTable", STAFF.'+', true, 'runReportStatsTable');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	    
	    $overall = array( "HQP"=>array('total'=>0, 'report'=>0, 'pdf'=>0, 'submitted'=>0),
						  "NI" =>array('total'=>0, 'report'=>0, 'pdf'=>0, 'submitted'=>0),
						  "CNI"=>array('total'=>0, 'report'=>0, 'pdf'=>0, 'submitted'=>0),
						  "PNI"=>array('total'=>0, 'report'=>0, 'pdf'=>0, 'submitted'=>0));
	    
	    $hqps = array();
	    $nis = array();
	    $cnis = array();
	    $pnis = array();
	    
	    $people = Person::getAllPeople();
	    foreach($people as $person){
	        $roles = $person->getRoles();
	        foreach($roles as $role){
	            if($role->getRole() == HQP){
	                $hqps[$person->getId()] = $person;
	            }
	            else if($role->getRole() == PNI){
	            	$pnis[$person->getId()] = $person;
	            	$nis[$person->getId()] = $person;
	            }
	            else if($role->getRole() == CNI){ 
	                $cnis[$person->getId()] = $person;  
	                $nis[$person->getId()] = $person;
	            }
	        }
	    }
	    
	    $wgOut->setPageTitle("Reporting Statistics");
	    $wgOut->addHTML("<div id='ackTabs'>
	                        <ul>
		                        <li><a href='#hqp'>HQP</a></li>
		                        <li><a href='#ni'>NI</a></li>
		                        <li><a href='#cni'>CNI</a></li>
		                        <li><a href='#pni'>PNI</a></li>
		                        <li><a href='#all'>Overall</a></li>
	                        </ul>");


		$wgOut->addHTML("<div id='hqp'>");
	    $overall['HQP'] = ReportStatsTable::hqpTable($hqps);           
	    $wgOut->addHTML("</div><div id='ni'>");
		$overall['NI'] = ReportStatsTable::niTable($nis);
		$wgOut->addHTML("</div><div id='cni'>");
		$overall['CNI'] = ReportStatsTable::niTable($cnis);
		$wgOut->addHTML("</div><div id='pni'>");
		$overall['PNI'] = ReportStatsTable::niTable($pnis);
		$wgOut->addHTML("</div><div id='all'>");
		ReportStatsTable::overallTable($overall);
	    $wgOut->addHTML("</div></div>");
	    
	    //                    <div id='unreg'>");
		/*ReportStatsTable::unregTable();
	    $wgOut->addHTML("</div>
	                        <div id='addAck'>");
	    ReportStatsTable::addAck();
	    $wgOut->addHTML("</div>");*/
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
    	 //print_r($overall);

    }
    
    static function overallTable($overall){
    	global $wgOut;
    	$wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Type</th>
	                                <th>All</th>
	                                <th>Started Report</th>
	                                <th>Uploaded Budget</th>
	                                <th>Generated PDF</th>
	                                <th>Submitted PDF</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");
    	foreach($overall as $type => $stats){
    		$wgOut->addHTML("<tr><td>{$type}</td>");
    		foreach($stats as $cell => $number){
    			$wgOut->addHTML("<td>$number</td>");
    		}

    		$wgOut->addHTML("</tr>");
    	}
    	$wgOut->addHTML("</tbody></table>");
    }

    static function hqpTable($hqps){
        global $wgOut, $wgServer, $wgScriptPath;
        $overall = array('total'=>0, 'report'=>0, 'budget'=>'N/A', 'pdf'=>0, 'submitted'=>0);

        $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Name</th>
	                                <th>Type</th>
	                                <th>University</th>
	                                <th>Supervisor</th>
	                                <th>Report</th>
	                                <th>PDF</th>
	                                <th>Submitted</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");

        
	    foreach($hqps as $hqp){
	    	$overall['total']++;

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
	        
            $supervisors = $hqp->getSupervisors();
            
            $names = array();
            foreach($supervisors as $supervisor){
            	$names[] = $supervisor->getReversedName();
            }
            $names = implode('; ', $names);

            //Report Stats
            $rep_year = REPORTING_YEAR;
            $p_id = $hqp->getId();
            $rp_type = 2;
            $rep_started = "No";
            $check_report_record = "SELECT * FROM grand_report_blobs WHERE year=$rep_year AND user_id=$p_id AND rp_type=$rp_type";
            $res = DBFunctions::execSQL($check_report_record);
            
            if(count($res)>0){
            	$rep_started = "Yes";
            	$overall['report']++;
            }	

            $sto = new ReportStorage($hqp);

            $check = array_merge($sto->list_reports($hqp->getId(), SUBM, 10000, 0, 9), $sto->list_reports($hqp->getId(), NOTSUBM, 10000,0,9));
            $largestDate = "2012-09-01 00:00:00";
	    	//if($hqp->getId() == 634){
	    	//	print_r($check);
	    	//}
	    	$latest_pdf = null;
	    	foreach($check as $c){
	    	    $tok = $c['token'];
	    	    $sto->select_report($tok);
	    	    $year = $c['year'];
	    	    $tst = $sto->metadata('timestamp');

	    	    if($year == $rep_year && strcmp($tst, $largestDate) > 0){
	    	        $largestDate = $tst;
	    	        $latest_pdf = $c;
	    	    	
	    	    }
	    	}
	    	$pdf_found = "No";
	    	$submitted = "No";

	    	if(!is_null($latest_pdf)){
	    		$pdf_found = "Yes";
	    		$overall['pdf']++;
	    		$submitted = ($latest_pdf['submitted'])? "Yes" : "No";
	    	}

	    	if($submitted == "Yes"){
	    		$overall['submitted']++;
	    	}

            $wgOut->addHTML("<tr>
                                <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a>{$inactive}</td>
                                <td>$type</td>
                                <td>$university</td>
                                <td>{$names}</td>
                                <td>{$rep_started}</td>
                                <td>{$pdf_found}</td>
                                <td>{$submitted}</td>
                             </tr>\n");
            
     
        
	        
	    }
	    $wgOut->addHTML("</tbody></table>");
	    return $overall;
    }
    
    static function niTable($nis){
        global $wgOut, $wgServer, $wgScriptPath;
        $overall = array('total'=>0, 'report'=>0, 'budget'=>0,  'pdf'=>0,'submitted'=>0);
        $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Name</th>
	                                <th>Type</th>
	                                <th>University</th>
	                                <th>Report</th>
	                                <th>PDF</th>
	                                <th>Budget</th>
	                                <th>Submitted</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");

        
	    foreach($nis as $ni){
	    	$overall['total']++;

	        $uni = $ni->getUniversity();
            $title = $uni['position'];
            $university = $uni['university'];
            $type = $title;

            //Report Stats
            $rep_year = REPORTING_YEAR;
            $p_id = $ni->getId();
            $rp_type = 1;
            $rep_started = "No";
            $check_report_record = "SELECT * FROM grand_report_blobs WHERE year=$rep_year AND user_id=$p_id AND rp_type=$rp_type";
            $res = DBFunctions::execSQL($check_report_record);
            
            if(count($res)>0){
            	$rep_started = "Yes";
            	$overall['report']++;
            }	

            $sto = new ReportStorage($ni);

            $check = array_merge($sto->list_reports($ni->getId(), SUBM, 10000, 0, 0), $sto->list_reports($ni->getId(), NOTSUBM, 10000,0,0));
            $largestDate = "2012-09-01 00:00:00";
	    	//if($ni->getId() == 634){
	    	//	print_r($check);
	    	//}
	    	$latest_pdf = null;
	    	foreach($check as $c){
	    	    $tok = $c['token'];
	    	    $sto->select_report($tok);
	    	    $year = $c['year'];
	    	    $tst = $sto->metadata('timestamp');

	    	    if($year == $rep_year && strcmp($tst, $largestDate) > 0){
	    	        $largestDate = $tst;
	    	        $latest_pdf = $c;
	    	    	
	    	    }
	    	}
	    	$pdf_found = "No";
	    	$submitted = "No";

	    	if(!is_null($latest_pdf)){
	    		$pdf_found = "Yes";
	    		$overall['pdf']++;
	    		$submitted = ($latest_pdf['submitted'])? "Yes" : "No";
	    	}

	    	if($submitted == "Yes"){
	    		$overall['submitted']++;
	    	}

	    	$budget = "No";
            $check_budget = "SELECT * FROM grand_report_blobs WHERE year=$rep_year AND user_id=$p_id AND rp_type=$rp_type AND rp_section=8 AND rp_item=0";
            $res = DBFunctions::execSQL($check_budget);
            
            if(count($res)>0){
            	$budget = "Yes";
            	$overall['budget']++;
            }	

            $wgOut->addHTML("<tr>
                                <td><a href='{$ni->getUrl()}' target='_blank'>{$ni->getReversedName()}</a></td>
                                <td>$type</td>
                                <td>$university</td>
                                <td>{$rep_started}</td>
                                <td>{$pdf_found}</td>
                                <td>{$budget}</td>
                                <td>{$submitted}</td>
                             </tr>\n");
            
     
        
	        
	    }
	    $wgOut->addHTML("</tbody></table>");
	    return $overall;
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
        
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:ReportStatsTable' method='post' enctype='multipart/form-data'>
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
}

?>

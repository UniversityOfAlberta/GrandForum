<?php
$dir = dirname(__FILE__) . '/';

//$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['SanityChecks'] = 'SanityChecks';
$wgExtensionMessagesFiles['SanityChecks'] = $dir . 'SanityChecks.i18n.php';
$wgSpecialPageGroups['SanityChecks'] = 'grand-tools';

function runSanityChecks($par) {
	SanityChecks::run($par);
}


class SanityChecks extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SanityChecks');
		SpecialPage::SpecialPage("SanityChecks", CNI.'+', true, 'runSanityChecks');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	    
	    $me = Person::newFromId($wgUser->getId());

	    
	    if($me->isRoleAtLeast(STAFF)){
			$wgOut->setPageTitle("NI Data Quality");
		    $wgOut->addHTML("<div id='ackTabs'>
		                        <ul>
			                        <li><a href='#ni'>NIs</a></li>
		                        </ul>
		                        <div id='ni'>");

		    SanityChecks::niTable();           
		    
		    $wgOut->addHTML("</div></div>");
		   
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
		else if($me->isRoleAtLeast(CNI)){
			$wgOut->setPageTitle("Data Quality Issues");
			SanityChecks::personalizedTable();
    	}
    }    
    
    // static function errorsTable(){
    // 	global $wgUser;

    // 	$me = Person::newFromId($wgUser->getId());
    
    // 	if($me->isRoleAtLeast(STAFF)){
    // 		SanityChecks::niTable();
    // 	}
    // 	else if($me->isRoleAtLeast(CNI)){
    // 		SanityChecks::personalizedTable();
    // 	}

    // }

    static function personalizedTable(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;

        $html =<<<EOF
        	<p><br /></p>
        	<table style='background:#ffffff;' width='100%' cellspacing='1' cellpadding='7' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th width="15%">Budget Errors</th>
                    <th width="55%">Paper Errors</th>
                   	<th width="30%">Student Errors</th> 
                </tr>
            </thead>
            <tbody>
EOF;
	    $all_errors = SanityChecks::getErrors($wgUser->getId());

	    if(empty($all_errors)){
	    	$html .=<<<EOF
	    	<tr><td colspan='3'>No Errors Found</td></tr>
EOF;
	    }
	    else{
	    	foreach($all_errors as $name => $errors){
	    		$ni = Person::newFromName($name);
	    		$niname_normal = $ni->getNameForForms();
	    		$niname_link = $ni->getUrl();
	    		//$html .= "<tr><td><a href='{$niname_link}'>{$niname_normal}</a></td>";

	    		$html .= "<tr><td>";
				if(!empty($errors['budget_errors'])){
					foreach ($errors['budget_errors'] as $name => $es){
						$html .= "<strong>{$es}</strong>";
					}
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}
				$html .= "</td>";

				$html .= "<td>";
				if(!empty($errors['paper_errors'])){
					$html .= "<strong>Papers with incomplete records:</strong><ul>";
					foreach ($errors['paper_errors'] as $id => $es){
						$paper = Paper::newFromId($id);
						$name = $paper->getTitle();
						$paper_link = $paper->getUrl();
						$html .= "<li><a href='{$paper_link}'>{$name}</a></li>";
						//$html .= "{$name}:<ul>";
						//foreach ($es as $e){
						//	$html .= "<li>{$e}</li>";
						//}
						//$html .= "</ul>";
					}
					$html .= "</ul>";
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}
				$html .= "</td>";

				$html .= "<td>";

				if(!empty($errors['student_errors'])){
					$error_students = array();
					foreach ($errors['student_errors'] as $name => $es){
						$student = Person::newFromName($name);
						$name_normal = $student->getNameForForms();
						$name_link = "<a href='".$student->getUrl()."'>{$name_normal}</a>";
						$html .= "{$name_link}:<ul>";
						foreach ($es as $e){
							//$error_students["{$e}"][] = $name_link;
							$html .= "<li>{$e}</li>";
						}
						$html .= "</ul>";
					}
					// $html .= "<ul>";
					// foreach($error_students as $e=>$s){
					// 	$html .= "<li><b>{$e}:</b> ". implode(', ', $s) ."</li>";
					// }
					// $html .= "</ul>";
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}

				$html .= "</td>";
				
	    	}
	    }
		
		$html .= "</tbody></table>";

	    $wgOut->addHTML($html);
    }

    static function niTable(){
        global $wgOut, $wgServer, $wgScriptPath;
        
        $html =<<<EOF
        	<table class='indexTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width="12%">NI Name</th>
                    <th width="14%">Budget Errors</th>
                    <th width="37%">Paper Errors</th>
                   	<th width="37%">Student Errors</th> 
                </tr>
            </thead>
            <tbody>
EOF;
	    $all_errors = SanityChecks::getErrors();

	    if(empty($all_errors)){
	    	$html .=<<<EOF
	    	<tr><td colspan='4'>No Errors Found</td></tr>
EOF;
	    }
	    else{
	    	foreach($all_errors as $name => $errors){
	    		$ni = Person::newFromName($name);
	    		$niname_normal = $ni->getNameForForms();
	    		$niname_link = $ni->getUrl();
	    		$ni_role = ($ni->isCNI())? "role:CNI" : (($ni->isPNI())? "role:PNI" : "role:Other"); 
	    		$projects = $ni->getProjects();
	    		$project_names = array();
	    		foreach ($projects as $p) {
	    			$project_names[] = "project:".$p->getName();
	    		}
	    		$project_names = implode(', ', $project_names);
	    		$uni = $ni->getUni();
	    		$uni = "university:".$uni;
	    		$html .= "<tr><td><span style='display:none;'>{$ni_role} {$project_names} {$uni}</span><a href='{$niname_link}'>{$niname_normal}</a></td>";

	    		$html .= "<td>";
				if(!empty($errors['budget_errors'])){
					foreach ($errors['budget_errors'] as $name => $es){
						$html .= "<strong>{$es}</strong>";
					}
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}
				$html .= "</td>";

				$html .= "<td>";
				if(!empty($errors['paper_errors'])){
					$html .= "<strong>Papers with incomplete records:</strong><ul>";
					foreach ($errors['paper_errors'] as $id => $es){
						$paper = Paper::newFromId($id);
						$name = $paper->getTitle();
						$paper_link = $paper->getUrl();
						$html .= "<li><a href='{$paper_link}'>{$name}</a></li>";
						//$html .= "{$name}:<ul>";
						//foreach ($es as $e){
						//	$html .= "<li>{$e}</li>";
						//}
						//$html .= "</ul>";
					}
					$html .= "</ul>";
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}
				$html .= "</td>";

				$html .= "<td>";

				if(!empty($errors['student_errors'])){
					$error_students = array();
					foreach ($errors['student_errors'] as $name => $es){
						//$html .= "{$name}:<ul>";
						$student = Person::newFromName($name);
						$name_normal = $student->getNameForForms();
						$name_link = "<a href='".$student->getUrl()."'>{$name_normal}</a>";
						foreach ($es as $e){
							$error_students["{$e}"][] = $name_link;
							//$html .= "<li>{$e}</li>";
						}
						//$html .= "</ul>";
					}
					$html .= "<ul>";
					foreach($error_students as $e=>$s){
						$html .= "<li><b>{$e}:</b> ". implode(', ', $s) ."</li>";
					}
					$html .= "</ul>";
				}
				else{
					$html .= "<strong>No Errors</strong>";
				}

				$html .= "</td>";
				
	    	}
	    }
		
		$html .= "</tbody></table>";

	    $wgOut->addHTML($html);
    }
    

    static function getErrors($ni_id = null){
    	
    	if(is_null($ni_id)){
    		$cnis = Person::getAllPeople('CNI');
			$pnis = Person::getAllPeople('PNI');
			$all_people = array_merge($cnis, $pnis);
		}else{
			$ni = Person::newFromId($ni_id);
			$all_people = array($ni);
		}

		$unique = array();
		$ni_errors = array();

		foreach($all_people as $person){
			//echo $person->getName() . "\n";
			$name = $person->getName();
			$name_normal = $person->getNameForForms();
			
			if($person->isActive() && !in_array($person->getId(), $unique)){
				$unique[] = $person->getId();
				
				//Allocated Budget Upload
				//$person->getAllocatedBudget(2012);
				$year = 2012;
				$uid = $person->getId();
		        $blob_type=BLOB_EXCEL;
		        $rptype = RP_RESEARCHER;
		    	$section = RES_ALLOC_BUDGET;
		    	$item = 0;
		    	$subitem = 0;
		        $rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		        $budget_blob = new ReportBlob($blob_type, ($year-1), $uid, 0);
		        $budget_blob->load($rep_addr);
		        $data = $budget_blob->getData();
		        if(is_null($data)){
		        	$ni_errors["{$name}"]['budget_errors'] = array("No revised budget");
		        }

				//Product completeness
				$papers = $person->getPapersAuthored("all", "2012-01-01 00:00:00", "2013-05-01 00:00:00", false);
				$person_paper_errors = array();

				foreach($papers as $paper){
					$paper_id = $paper->getId();
					$paper_title = $paper->getTitle();

					$errors = array();
					$completeness = $paper->getCompleteness();
					if(!$completeness['venue']){
						$errors[] = "Does not have a venue.";
					}

					if(!$completeness['pages']){
						$errors[] = "Does not have page information.";
					}

					if(!$completeness['publisher']){
						$errors[] = "Does not have a publisher.";
					}

					if(!empty($errors)){
						$person_paper_errors["{$paper_id}"] = $errors;
					}

				}
			
				$ni_errors["{$name}"]['paper_errors'] = $person_paper_errors;
			

				//Students moved on vs thesis
				$student_errors = array();
				$students = $person->getStudents('all', true);
				foreach($students as $s){
					$student_name = $s->getName();
					$position = $s->getPosition();
					$university = $s->getUni();
					$department = $s->getDepartment();
					$errors = array();
					$ishqp = $s->isHQP();
					$related = $person->relatedTo($s, 'Supervises');

					//Check for Ethics tutorial completion
					$ethics = $s->getEthics();
					if($ethics['completed_tutorial'] == 0 && $ishqp && $related){
						$errors[] = "Not Completed TCPS2";
					}

					//Acknowledgements
					if($ishqp && $related){
						$acks = $s->getAcknowledgements();
						if(count($acks) > 0){
							$ack_found = false;
							foreach ($acks as $a){
								$supervisor = $a->getSupervisor();
								if($supervisor == $name_normal){
									$ack_found = true;
									break;
								}
							}
							if(!$ack_found){
								$errors[] = "No Acknowledgement";
							}
						}
						else{
							$errors[] = "No Acknowledgement";
						}
					}

					if($ishqp && $related && ($university == "" || $department == "" || $position == "")){
						$errors[] = "Missing University/Department/Position";
					}

					//Only care about Masters and PhDs for thesis errors
					if(($position == "Masters Student" || $position == "PhD Student") && $ishqp && $related){
						
						//Check for thesis and no exit data
						$thesis = $s->getThesis();
						if(!is_null($thesis)){
							$moved = $s->getMovedOn();
							if(empty($moved['studies']) && empty($moved['city']) && empty($moved['works']) && empty($moved['employer']) && empty($moved['country'])){
								$errors[] = "Thesis but no exit data";
							}
						}
						// else if(is_null($thesis) && !$ishqp){
						// 	$moved = $s->getMovedOn();
						// 	if(empty($moved['studies']) && empty($moved['city']) && empty($moved['works']) && empty($moved['employer']) && empty($moved['country'])){
						// 		$errors[] = "Past student is no longer an HQP, however has no thesis records, and is not marked as moved on.";
						// 	}
						// 	else{
						// 		$errors[] = "Past student is marked as moved on, however has no thesis record.";
						// 	}
						// }
					}

					if(!empty($errors)){
						$student_errors["{$student_name}"] = $errors;
					}
				}

				$ni_errors["{$name}"]['student_errors'] = $student_errors;

			}
		}
		return $ni_errors;
    }
    
}

?>

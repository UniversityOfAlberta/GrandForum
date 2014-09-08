<?php

define("EVAL_YEAR", REPORTING_YEAR);
class Evaluate_Form {
	static function cniOutput($person){
        global $wgOut;
		$subs = $person->getEvaluateCNIs();
		$rptype = RP_EVAL_CNI;
        foreach($subs as $sub){
            if($sub instanceof Person){
                $name = str_replace(".", "", $sub->getName());
                $wgOut->addHTML("<h2><a style='cursor:pointer;' onClick='show(\"$name\")'>Review {$sub->getName()}</a></h2>
                                    <div id='sub$name' style='display:none;margin-left:40px'>");
                $wgOut->addHTML("<h3>I. Excellence of the Research Program</h3>");
                Evaluate_Form::questionI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>II. Development of ".HQP."</h3>");
                Evaluate_Form::questionII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>III. Networking and Partnerships</h3>");
                Evaluate_Form::questionIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>IV. Knowledge and Technology Exchange and Exploitation</h3>");
                Evaluate_Form::questionIV($person, $sub, $rptype);
                $wgOut->addHTML("<h3>V. Management of the Network</h3>");
                Evaluate_Form::questionV($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VI. Overall Score</h3>");
                Evaluate_Form::questionVI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VII. Other Comments</h3>");
                Evaluate_Form::questionVII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VIII. Rating for Quality of Report</h3>");
                Evaluate_Form::questionVIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>IX. Confidence Level of Evaluator</h3>");
                Evaluate_Form::questionIX($person, $sub, $rptype);
                $wgOut->addHTML("</div>");
            }
        }
	}
	
    static function pniOutput($person){
        global $wgOut;
		$subs = $person->getEvaluateSubs();
		$rptype = RP_EVAL_RESEARCHER;
        foreach($subs as $sub){
            if($sub instanceof Person){
                $name = str_replace(".", "", $sub->getName());
                $wgOut->addHTML("<h2><a style='cursor:pointer;' onClick='show(\"$name\")'>Review {$sub->getName()}</a></h2>
                                    <div id='sub$name' style='display:none;margin-left:40px'>");
                $wgOut->addHTML("<h3>I. Excellence of the Research Program</h3>");
                Evaluate_Form::questionI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>II. Development of ".HQP."</h3>");
                Evaluate_Form::questionII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>III. Networking and Partnerships</h3>");
                Evaluate_Form::questionIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>IV. Knowledge and Technology Exchange and Exploitation</h3>");
                Evaluate_Form::questionIV($person, $sub, $rptype);
                $wgOut->addHTML("<h3>V. Management of the Network</h3>");
                Evaluate_Form::questionV($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VI. Overall Score</h3>");
                Evaluate_Form::questionVI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VII. Other Comments</h3>");
                Evaluate_Form::questionVII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VIII. Rating for Quality of Report</h3>");
                Evaluate_Form::questionVIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>IX. Confidence Level of Evaluator</h3>");
                Evaluate_Form::questionIX($person, $sub, $rptype);
                $wgOut->addHTML("</div>");
            }
        }
	}
	
	static function pniOutputPDF($person=0, $sub=0){
        global $wgOut;
		if( !($sub instanceof Person) || !($person instanceof Person) ){
	        $sub = (isset($_GET['pni']))? Person::newFromName($_GET['pni']) : 0;
			$person  = (isset($_GET['eval']))? Person::newFromName($_GET['eval']) : 0;
		}
		
		//Report adresses
		$rptype = RP_EVAL_RESEARCHER;
		$rpsections = array(
							EVL_EXCELLENCE => "I. Excellence of the Research Program",
							EVL_HQPDEVELOPMENT => "II. Development of HQP",
							EVL_NETWORKING => "III. Networking and Partnerships",
							EVL_KNOWLEDGE => "IV. Knowledge and Technology Exchange and Exploitation",
							EVL_MANAGEMENT => "V. Management of the Network",
							EVL_OVERALLSCORE => "VI. Overall Score",
							EVL_OTHERCOMMENTS => "VII. Other Comments",
							EVL_REPORTQUALITY => "VIII. Rating for Quality of Report",
							EVL_CONFIDENCE => "IX. Confidence Level of Evaluator"
							);
		
		if($sub instanceof Person && $person instanceof Person){
			$wgOut->clearHTML();
			
			$eval_name =  $person->getName();
			$eval_id =  $person->getId();
			
			$sub_name = $sub->getName();
			$sub_id = $sub->getId();
			
			$html = "<h1>Evaluator PNI Review</h1>";
			$html .= "<table cellspacing='3' style='font-size:16px; font-weight:bold;'><tbody>
					  <tr><td style='padding-right:65px;'>Evaluator:</td><td>$eval_name</td></tr>
					  <tr><td style='padding-right:65px;'>PNI:</td><td>$sub_name</td></tr>
					  </tbody></table>";
			//$wgOut->addHTML($html);
			
			foreach ($rpsections as $rpsection => $heading){
				$data = Evaluate_Form::getData("", $rptype, $rpsection, $sub, $eval_id);
				$rating = ucfirst($data['rating']);
				$comment = nl2br($data['comment']);
				$feedback = nl2br($data['feedback']);
				
				$html .= "<h2>$heading</h2>";
	
				if($rating){
					$html .= "<p><b style='font-size: 13px;'>Rating: $rating</b></p><br /><br />";
				}
				
				$html .= "<p><b style='font-size: 13px;'>Comments for Committee:</b><br />$comment</p>";
				
				if($feedback){
					$html .= "<br /><br /><p><b style='font-size: 13px;'>Feedback for Investigator:</b><br />$feedback</p>";
				}			
				
			}
			
			//Generate
			$dompdf = "";
			try {
				$dompdf = PDFGenerator::generate("Report" , $html, "", null, false);
				//exit;
			}
			catch(DOMPDF_Internal_Exception $e){
				echo "ERROR!!!";
				echo $e->getMessage();
			}
			
			//$pdfdata = $dompdf->stream("PNI_Review-{$eval_name}_vs_{$sub_name}.pdf");
		
			//Save it in a BLOB
			$pdf_data = $dompdf->output();
			$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PNI, $sub_id, 0);
			$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $eval_id, 0);
			
			if($blb->store($pdf_data, $addr)){
				$blb->load($addr);
				$blob_id = $blb->getId();
				echo json_encode(array("error"=>0, "blob_id"=>$blob_id));
				return;
			}
		}
		echo json_encode(array("error"=>1, "blob_id"=>0));
		return;
	}

    static function projOutput($person){
		global $wgOut;
		$rptype = RP_EVAL_PROJECT;
        foreach($person->getEvaluateSubs() as $sub){
            if($sub instanceof Project){
                $name = str_replace(".", "", $sub->getName());
                $wgOut->addHTML("<h2><a style='cursor:pointer;' onClick='show(\"$name\")'>Review {$sub->getName()}</a></h2>
                                    <div id='sub$name' style='display:none;margin-left:40px'>");
                $wgOut->addHTML("<h3>I. Excellence of the Research Program</h3>");
                Evaluate_Form::questionI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>II. Development of ".HQP."</h3>");
                Evaluate_Form::questionII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>III. Networking and Partnerships</h3>");
                Evaluate_Form::questionIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>IV. Knowledge and Technology Exchange and Exploitation</h3>");
                Evaluate_Form::questionIV($person, $sub, $rptype);
                //Evaluate_Form::questionV($person, $sub, $rptype);
                $wgOut->addHTML("<h3>V. Overall Score</h3>");
                Evaluate_Form::questionVI($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VI. Other Comments</h3>");
                Evaluate_Form::questionVII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VII. Rating for Quality of Report</h3>");
                Evaluate_Form::questionVIII($person, $sub, $rptype);
                $wgOut->addHTML("<h3>VIII. Confidence Level of Evaluator</h3>");
                Evaluate_Form::questionIX($person, $sub, $rptype);
                $wgOut->addHTML("</div>");
            }
        }
    }
	
	static function projOutputPDF($person=0, $sub=0){
        global $wgOut;
		if( !($sub instanceof Project) || !($person instanceof Person) ){
			$sub = (isset($_GET['proj']))? Project::newFromName($_GET['proj']) : 0;
			$person  = (isset($_GET['eval']))? Person::newFromName($_GET['eval']) : 0;
		}
		//Report adresses
		$rptype = RP_EVAL_PROJECT;
		$rpsections = array(
							EVL_EXCELLENCE => "I. Excellence of the Research Program",
							EVL_HQPDEVELOPMENT => "II. Development of HQP",
							EVL_NETWORKING => "III. Networking and Partnerships",
							EVL_KNOWLEDGE => "IV. Knowledge and Technology Exchange and Exploitation",
							//EVL_MANAGEMENT => "V. Management of the Network",
							EVL_OVERALLSCORE => "V. Overall Score",
							EVL_OTHERCOMMENTS => "VI. Other Comments",
							EVL_REPORTQUALITY => "VII. Rating for Quality of Report",
							EVL_CONFIDENCE => "VIII. Confidence Level of Evaluator"
							);
		
		if($sub instanceof Project && $person instanceof Person){
			$wgOut->clearHTML();
			
			$eval_name =  $person->getName();
			$eval_id =  $person->getId();
			
			$sub_name = $sub->getName();
			$sub_id = $sub->getId();
			
			$html = "<h1>Evaluator Project Review</h1>";
			$html .= "<table cellspacing='3' style='font-size:16px; font-weight:bold;'><tbody>
					  <tr><td style='padding-right:65px;'>Evaluator:</td><td>$eval_name</td></tr>
					  <tr><td style='padding-right:65px;'>Project:</td><td>$sub_name</td></tr>
					  </tbody></table>";
			
			foreach ($rpsections as $rpsection => $heading){
				$data = Evaluate_Form::getData("", $rptype, $rpsection, $sub, $eval_id);
				$rating = ucfirst($data['rating']);
				$comment = nl2br($data['comment']);
				$feedback = nl2br($data['feedback']);
				
				$html .= "<h2>$heading</h2>";
	
				if($rating){
					$html .= "<p><b style='font-size: 13px;'>Rating: $rating</b></p><br /><br />";
				}
				
				$html .= "<p><b style='font-size: 13px;'>Comments for Committee:</b><br />$comment</p>";
				
				if($feedback){
					$html .= "<br /><br /><p><b style='font-size: 13px;'>Feedback for Investigator:</b><br />$feedback</p>";
				}			
				
			}
			
			//Generate
			$dompdf = "";
			try {
				$dompdf = PDFGenerator::generate("Report" , $html, "", null, false);
				//exit;
			}
			catch(DOMPDF_Internal_Exception $e){
				echo "ERROR!!!";
				echo $e->getMessage();
			}
			
			//$pdfdata = $dompdf->stream("PNI_Review-{$eval_name}_vs_{$sub_name}.pdf");
		
			//Save it in a BLOB
			$pdf_data = $dompdf->output();
			$addr = ReportBlob::create_address(RP_EVAL_PDF, PDF_PROJ, $sub_id, 0);
			$blb = new ReportBlob(BLOB_PDF, EVAL_YEAR, $eval_id, 0);
			
			if($blb->store($pdf_data, $addr)){
				$blb->load($addr);
				$blob_id = $blb->getId();
				echo json_encode(array("error"=>0, "blob_id"=>$blob_id));
				return;
			}
		}
		echo json_encode(array("error"=>1, "blob_id"=>0));
		return;
	}
	
	static function getData($k, $rptype, $rpsection, $sub, $eval_id=0, $evalYear=EVAL_YEAR){
	    global $reporteeId;
		
		if($eval_id == 0){
			$eval_id = $reporteeId;
		}
	    $addr = ReportBlob::create_address($rptype, $rpsection, $sub->getId(), 0);
	    $blb = new ReportBlob(BLOB_ARRAY, $evalYear, $eval_id, 0);
		
	    $submit = "";
	    if($rptype == RP_EVAL_RESEARCHER){
	        $submit = ($_POST && isset($_POST['pni_review']))? $_POST['pni_review'] : "";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $submit = ($_POST && isset($_POST['proj_review']))? $_POST['proj_review'] : "";
	    }
		else if($rptype == RP_EVAL_CNI){
	        $submit = ($_POST && isset($_POST['cni_review']))? $_POST['cni_review'] : "";
	    }
	    $data = array();
	    $data['rating'] = "";
	    $data['comment'] = "";
	    $data['feedback'] = "";
	    if($submit == "Save"){
	        foreach($_POST as $key => $post){
	            if(preg_match("/".str_replace("_", "[_]", $k)."$/", $key) > 0){
	                $data[str_replace($k, "", $key)] = $post;
	            }
	        }
	        $blb->store($data, $addr);
	    }
	    $result = $blb->load($addr);
	    if($result !== false && $blb->getData() != null && !empty($data) && !is_string($data)){
		    $data = $blb->getData();
		    if(!isset($data['rating'])) $data['rating'] = "";
		    if(!isset($data['comment'])) $data['comment'] = ""; 
		    if(!isset($data['feedback'])) $data['feedback'] = "";
		}
		return $data;
	}
	
	static function questionI($person, $sub, $rptype){
	    global $wgOut, $reporteeId;
	    $type = "";
	    
	    if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_Ir{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_Ip{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_Ic{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_EXCELLENCE, $sub);
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                         <b>Elements being considered are:</b>
	                         <ul>
	                            <li>The excellence, focus and coherence of the research program</li>
                                <li>The balance between research into new discoveries and the application of research breakthroughs to address practical problems facing Canadians.</li>
                                <li>The achievements of the researchers in the continuum of research and their ability to contribute to the realization of the network’s objectives.</li>
                                <li>The value added by the network’s multifaceted approach, in terms of having all the critical linkages in place to generate world-class research breakthroughs, apply that knowledge to practical solutions, and commercialize innovations that produce social and economic benefits.</li>
                                <li>The extent to which the program will contribute to Canada’s abilities and reputation for international leadership in areas of high economic and social importance to Canada.</li>
                                <li>The extent to which new and emerging social and ethical challenges are an integral part of the research program.</li>
                                <li>The relationship of the proposed research program to similar work conducted in Canada and abroad.</li>
                             </ul>
                             <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Exceptional", "Satisfactory", "Unsatisfactory")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
                         </div>");

	}
	
	static function questionII($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
		if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_IIr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_IIp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_IIc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_HQPDEVELOPMENT, $sub);
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                         <b>Elements being considered are:</b>
	                         <ul>
	                            <li>The ability to attract, develop and retain outstanding researchers in research areas and technologies critical to Canadian productivity, economic growth, public policy and quality of life.</li>
                                <li>Training strategies that expose trainees to the full range of economic, social, and ethical implications of the network’s research by involving them in activities from the initial research discovery to its application through to practical social and economic benefits.</li>
                             </ul>
                             <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Exceptional", "Satisfactory", "Unsatisfactory")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
                         </div>");
	
	}
	
	static function questionIII($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
		if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_IIIr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_IIIp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_IIIc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_NETWORKING, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                         <b>Elements being considered are:</b>
	                         <ul>
	                            <li>Effective research and technology development links between national and international academic institutions, federal and provincial agencies, non-governmental organizations and private sector participants.</li>
                                <li>Multidisciplinary, multi-sectoral approaches in the research program.</li>
                                <li>Demonstration that the right partners/individuals are at the table to address the proposed issue, including international partners when applicable.</li>
                                <li>Optimization of resources through the sharing of equipment and research facilities, databases and personnel.</li>
                                <li>Presence, nature and extent of contributions from the private, public and not-for-profit sectors, and from international partners, as well as the prospect for increasing commitments as the work progresses.</li>
                             </ul>
                             <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Exceptional", "Satisfactory", "Unsatisfactory")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
                         </div>");
	}
	
	static function questionIV($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
		if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_IVr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_IVp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_IVc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_KNOWLEDGE, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                         <b>Elements being considered are:</b>
	                         <ul>
	                            <li>The new products, processes or services to be commercialized by firms operating in Canada as a result of network activities and the extent to which these will strengthen the Canadian economic base, enhance productivity, and contribute to long-term economic growth and social benefits.</li>
                                <li>The social innovations to be implemented as a result of the network and the extent to which these will generate social and health benefits for Canadians, and contribute to more effective public policy in Canada.</li>
                                <li>Effective collaboration with the private and public sectors in technology, market development, and public policy development.</li>
                                <li>The extent to which the network will help partners develop strong receptor capacity to exploit current and future research breakthroughs.</li>
                                <li>Effective management and protection of intellectual property resulting from network-funded research.</li>
                                <li>The extent to which additional/complementary knowledge, and/or technology a foreign counterpart is contributing to Canada, when international partnerships are relevant.</li>
                             </ul>
                             <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Exceptional", "Satisfactory", "Unsatisfactory")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
                         </div>");
	}
	
	static function questionV($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
		if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_Vr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_Vp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_Vc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_MANAGEMENT, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                         <b>Elements being considered are:</b>
	                         <ul>
	                            <li>A board and committee structure to ensure that appropriate policy and financial decisions are made and implemented.</li>
                                <li>The presence of effective leadership and expertise in the research and the business management functions.</li>
                                <li>Effective research planning and budgeting mechanisms.</li>
                                <li>Effective internal and external communications strategies.</li>
                             </ul>
                             <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Exceptional", "Satisfactory", "Unsatisfactory")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
                         </div>");
	}
	
	static function questionVI($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    $typeName = "";
		
		if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_VIr{$sub->getId()}";
	        $type = "Investigator";
			$typeName = "researcher";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_VIp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
			$typeName = "project";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_VIc{$sub->getId()}";
	        $type = "CNI";
			$typeName = "researcher";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_OVERALLSCORE, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                        Of the three Tiers, where do you place this $typeName?  It is understood that the majority will likely fall in the middle tier.<br />
	                        <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio3($data, $k, "Top Tier", "Middle Tier", "Lower Tier")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>
                             </table>
	                     </div>");
	}
	
	static function questionVII($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
	    if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_VIIr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_VIIp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_VIIc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_OTHERCOMMENTS, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                        <table>
	                            <tr>
                                    <td valign='top' align='right'><b>For Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='8' style='width:630px;'>{$data['comment']}</textarea></td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='8' style='width:630px;'>{$data['feedback']}</textarea></td>
                                </tr>
                            </table>
                         </div>");
	}
	
	static function questionVIII($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
	    if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_VIIIr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_VIIIp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_VIIIc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_REPORTQUALITY, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                        <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio4($data, $k, "Excellent", "Good", "Fair", "Poor")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>");
            $wgOut->addHTML("<tr>
                                    <td valign='top' align='right'><b>Feedback for $type:</b></td><td valign='top' align='left'><textarea name='feedback$k' rows='6' style='width:500px;'>{$data['feedback']}</textarea></td>
                                </tr>");
                             $wgOut->addHTML("</table>
	                     </div>");
	}
	
	static function questionIX($person, $sub, $rptype){
	    global $wgOut, $new_post;
	    $type = "";
	    
	    if($rptype == RP_EVAL_RESEARCHER){
	        $k = "_IXr{$sub->getId()}";
	        $type = "Investigator";
	    }
	    else if($rptype == RP_EVAL_PROJECT){
	        $k = "_IXp{$sub->getId()}";
	        $type = "Project Leader<br /> and co-Leader";
	    }
		else if($rptype == RP_EVAL_CNI){
			$k = "_IXc{$sub->getId()}";
	        $type = "CNI";
		}
	    $data = Evaluate_Form::getData($k, $rptype, EVL_CONFIDENCE, $sub);
	    $text = "";
	    if(isset($new_post["r$k"])){
	        $text = $new_post["r$k"];
	    }
	    $wgOut->addHTML("<div style='margin-left:25px;'>
	                        <table>
                                <tr>
                                    <td valign='top' align='right'><b>Rating:</b></td><td valign='top' align='left'>".Evaluate_Form::radio4($data, $k, "Excellent", "Good", "Fair", "Poor")."</td>
                                </tr>
                                <tr>
                                    <td valign='top' align='right'><b>Comments for Committee:</b></td><td valign='top' align='left'><textarea name='comment$k' rows='6' style='width:500px;'>{$data['comment']}</textarea></td>
                                </tr>
                             </table>
	                     </div>");
	                     //print_r($data);
	                     //var_dump($data);
	                     //echo "HI";
	}
	
	static function radio3($data, $k, $first, $second, $third){
	    $checked = array(0 => ' ', 1 => ' ', 2 => ' ');
	    if(isset($data['rating'])){
            if($data['rating'] == strtolower($first)){
                $checked[0] = "checked";
            }
            else if($data['rating'] == strtolower($second)){
                $checked[1] = "checked";
            }
            else if($data['rating'] == strtolower($third)){
                $checked[2] = "checked";
            }
        }
	    return "<input type='radio' name='rating$k' value='".strtolower($first)."' {$checked[0]} />$first &nbsp;&nbsp;&nbsp;&nbsp
                <input type='radio' name='rating$k' value='".strtolower($second)."' {$checked[1]} />$second &nbsp;&nbsp;&nbsp;&nbsp
                <input type='radio' name='rating$k' value='".strtolower($third)."' {$checked[2]} />$third &nbsp;&nbsp;&nbsp;&nbsp\n";
	}
	
	static function radio4($data, $k, $first, $second, $third, $fourth){
	    $checked = array(0 => ' ', 1 => ' ', 2 => ' ', 3 => ' ');
	    if(isset($data['rating'])){
            if($data['rating'] == strtolower($first)){
                $checked[0] = "checked";
            }
            else if($data['rating'] == strtolower($second)){
                $checked[1] = "checked";
            }
            else if($data['rating'] == strtolower($third)){
                $checked[2] = "checked";
            }
            else if($data['rating'] == strtolower($fourth)){
                $checked[3] = "checked";
            }
        }
	    return "<input type='radio' name='rating$k' value='".strtolower($first)."' {$checked[0]} />$first &nbsp;&nbsp;&nbsp;&nbsp
                <input type='radio' name='rating$k' value='".strtolower($second)."' {$checked[1]} />$second &nbsp;&nbsp;&nbsp;&nbsp
                <input type='radio' name='rating$k' value='".strtolower($third)."' {$checked[2]} />$third &nbsp;&nbsp;&nbsp;&nbsp
                <input type='radio' name='rating$k' value='".strtolower($fourth)."' {$checked[3]} />$fourth &nbsp;&nbsp;&nbsp;&nbsp\n";
	}
}
?>

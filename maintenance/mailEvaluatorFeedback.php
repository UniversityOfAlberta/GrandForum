<?php
// Sends an email to all the PNIs/Project Leaders 

require_once( 'commandLine.inc' );

$questionNames = array(1 => "Excellence of the Research Program",
                       2 => "Development of HQP",
                       3 => "Networking and Partnerships",
                       4 => "Knowledge and Technology Exchange and Exploitation",
                       5 => "Management of the Network",
                       6 => "Overall Score",
                       7 => "Other Comments",
                       8 => "Rating for Quality of Report"
                      );
$emailData = array();

prepareEmailFor(PNI);
prepareEmailFor(CNI);
prepareEmailFor("Project");

sendEmail($emailData);

function prepareEmailFor($type){
    global $reporteeId, $emailData;
    $people = Person::getAllPeople();
    //$people = array_merge($people, Person::getAllStaff());
    foreach($people as $person){
        if($person->isEvaluator()){
            $reporteeId = $person->getId();
            $subs = $person->getEvaluateSubs();
            foreach($subs as $sub){
                $id = "";
                if($sub instanceof Person && (($type == PNI && $sub->isRole(PNI)) || ($type == CNI && $sub->isRole(CNI)))){
                    $id = "person";
                    $ctype = "p";
                    $rtype = RP_EVAL_RESEARCHER;
                    $array = isset($peopleTiers[$sub->getName()]) ? $peopleTiers[$sub->getName()] : array();
                }
                else if($sub instanceof Project && $type == "Project"){
                    $id = "project";
                    $ctype = "r";
                    $rtype = RP_EVAL_PROJECT;
                    $array = isset($projectTiers[$sub->getName()]) ? $projectTiers[$sub->getName()] : array();
                }
                if($id == ""){
                    continue;
                }
                $feedback = array();
                $rating = array();
                
                $post = Evaluate_Form::getData('', $rtype, EVL_EXCELLENCE, $sub);
                $feedback[1] = $post["feedback"];
                $rating[1] = $post["rating"];
                
                $post = Evaluate_Form::getData('', $rtype, EVL_HQPDEVELOPMENT, $sub);
                $feedback[2] = $post["feedback"];
                $rating[2] = $post["rating"];

                $post = Evaluate_Form::getData('', $rtype, EVL_NETWORKING, $sub);
                $feedback[3] = $post["feedback"];
                $rating[3] = $post["rating"];

                $post = Evaluate_Form::getData('', $rtype, EVL_KNOWLEDGE, $sub);
                $feedback[4] = $post["feedback"];
                $rating[4] = $post["rating"];
                
                // Not in projects
                $post = Evaluate_Form::getData('', $rtype, EVL_MANAGEMENT, $sub);
                $feedback[5] = $post["feedback"];
                $rating[5] = $post["rating"];

                $post = Evaluate_Form::getData('', $rtype, EVL_OVERALLSCORE, $sub);
                $feedback[6] = $post["feedback"];
                $rating[6] = $post["rating"];

                $post = Evaluate_Form::getData('', $rtype, EVL_OTHERCOMMENTS, $sub);
                $feedback[7] = $post["feedback"];
                $rating[7] = $post["rating"];
                
                $post = Evaluate_Form::getData('', $rtype, EVL_REPORTQUALITY, $sub);
                $feedback[8] = $post["feedback"];
                $rating[8] = $post["rating"];
                if($type == "Project"){
                    $proj = Project::newFromId($sub->getId());
                    $leaders = array_merge($proj->getLeaders(), $proj->getCoLeaders());
                    foreach($leaders as $leader){
                        $emailData[$leader->getId()][$type][$proj->getId()][$reporteeId]['feedback'] = $feedback;
                        $emailData[$leader->getId()][$type][$proj->getId()][$reporteeId]['rating'] = $rating;
                    }
                }
                else{
                    $emailData[$sub->getId()][$type][$reporteeId]['feedback'] = $feedback;
                    $emailData[$sub->getId()][$type][$reporteeId]['rating'] = $rating;
                }
            }
        }
    }
}

function sendEmail($emailData){
    global $questionNames;
    foreach($emailData as $id => $array1){
        $emailString = "";
        $person = Person::newFromId($id);
        foreach($array1 as $type => $array2){
            if($type == PNI){
                $emailString .= "<h1>PNI Feedback</h1>\n";
                $qArray = array();
                $rArray = array();
                foreach($array2 as $evalId => $array3){
                    $feedback = $array3;
                    $evaluator = Person::newFromId($evalId);
                    //printf("%24s - PNI                      - %24s\n", $person->getName(), $evaluator->getName());
                    $qArray[1][] = $feedback['feedback'][1];
                    $qArray[2][] = $feedback['feedback'][2];
                    $qArray[3][] = $feedback['feedback'][3];
                    $qArray[4][] = $feedback['feedback'][4];
                    $qArray[5][] = $feedback['feedback'][5];
                    $qArray[6][] = $feedback['feedback'][6];
                    $qArray[7][] = $feedback['feedback'][7];
                    $qArray[8][] = $feedback['feedback'][8];
                    
                    $rArray[1][] = $feedback['rating'][1];
                    $rArray[2][] = $feedback['rating'][2];
                    $rArray[3][] = $feedback['rating'][3];
                    $rArray[4][] = $feedback['rating'][4];
                    $rArray[5][] = $feedback['rating'][5];
                    $rArray[6][] = $feedback['rating'][6];
                    $rArray[7][] = $feedback['rating'][7];
                    $rArray[8][] = $feedback['rating'][8];
                }
                $emailString .= "<ol>\n";
                foreach($qArray as $q => $feedback){
                    $emailString .= "   <li><b>{$questionNames[$q]}:</b>\n";
                    $emailString .= "       <ul>\n";
                    foreach($feedback as $key => $fed){
                        if($fed != "" || $rArray[$q][$key] != ""){
                            $emailString .= "   <li><b>Reviewer: ".($key+1)."</b><br />
                                                    <b>Rating:</b> {$rArray[$q][$key]}<br />
                                                    <b>Feedback:</b> ".nl2br($fed)."</li>\n";
                        }
                    }
                    $emailString .= "       </ul>\n";
                    $emailString .= "   </li>\n";
                }
                $emailString .= "</ol>\n";
            }
            else if($type == CNI){
                $emailString .= "<h1>PNI Feedback</h1>\n";
                $qArray = array();
                foreach($array2 as $evalId => $array3){
                    $feedback = $array3;
                    $evaluator = Person::newFromId($evalId);
                    //printf("%24s - CNI                      - %24s\n", $person->getName(), $evaluator->getName());
                    $qArray[1][] = $feedback['feedback'][1];
                    $qArray[2][] = $feedback['feedback'][2];
                    $qArray[3][] = $feedback['feedback'][3];
                    $qArray[4][] = $feedback['feedback'][4];
                    $qArray[5][] = $feedback['feedback'][5];
                    $qArray[6][] = $feedback['feedback'][6];
                    $qArray[7][] = $feedback['feedback'][7];
                    $qArray[8][] = $feedback['feedback'][8];
                    
                    $rArray[1][] = $feedback['rating'][1];
                    $rArray[2][] = $feedback['rating'][2];
                    $rArray[3][] = $feedback['rating'][3];
                    $rArray[4][] = $feedback['rating'][4];
                    $rArray[5][] = $feedback['rating'][5];
                    $rArray[6][] = $feedback['rating'][6];
                    $rArray[7][] = $feedback['rating'][7];
                    $rArray[8][] = $feedback['rating'][8];
                }
                $emailString .= "<ol>\n";
                foreach($qArray as $q => $feedback){
                    $emailString .= "   <li><b>{$questionNames[$q]}:</b>\n";
                    $emailString .= "       <ul>\n";
                    foreach($feedback as $key => $fed){
                        if($fed != "" || $rArray[$q][$key] != ""){
                            $emailString .= "   <li><b>Reviewer ".($key+1)."</b><br />
                                                    <b>Rating:</b> {$rArray[$q][$key]}<br />
                                                    <b>Feedback:</b> ".nl2br($fed)."</li>\n";
                        }
                    }
                    $emailString .= "       </ul>\n";
                    $emailString .= "   </li>\n";
                }
                $emailString .= "</ol>\n";
            }
            else if($type == "Project"){
                foreach($array2 as $pId => $array3){
                    $project = Project::newFromId($pId);
                    $emailString .= "<h1>Project Leader Feedback ({$project->getName()})</h1>\n";
                    $qArray = array();
                    $rArray = array();
                    foreach($array3 as $evalId => $array4){
                        $feedback = $array4;
                        $evaluator = Person::newFromId($evalId);
                        //printf("%24s - Project %16s - %24s\n", $person->getName(), $project->getName(), $evaluator->getName());
                        $qArray[1][] = $feedback['feedback'][1];
                        $qArray[2][] = $feedback['feedback'][2];
                        $qArray[3][] = $feedback['feedback'][3];
                        $qArray[4][] = $feedback['feedback'][4];
                        $qArray[6][] = $feedback['feedback'][6];
                        $qArray[7][] = $feedback['feedback'][7];
                        $qArray[8][] = $feedback['feedback'][8];
                        
                        $rArray[1][] = $feedback['rating'][1];
                        $rArray[2][] = $feedback['rating'][2];
                        $rArray[3][] = $feedback['rating'][3];
                        $rArray[4][] = $feedback['rating'][4];
                        $rArray[6][] = $feedback['rating'][6];
                        $rArray[7][] = $feedback['rating'][7];
                        $rArray[8][] = $feedback['rating'][8];
                    }
                    $emailString .= "<ol>\n";
                    foreach($qArray as $q => $feedback){
                        $emailString .= "   <li><b>{$questionNames[$q]}:</b>\n";
                        $emailString .= "       <ul>\n";
                        foreach($feedback as $key => $fed){
                            if($fed != "" || $rArray[$q][$key] != ""){
                                $emailString .= "   <li><b>Reviewer ".($key+1)."</b><br />
                                                    <b>Rating:</b> {$rArray[$q][$key]}<br />
                                                    <b>Feedback:</b> ".nl2br($fed)."</li>\n";
                            }
                        }
                        $emailString .= "       </ul>\n";
                        $emailString .= "   </li>\n";
                    }
                    $emailString .= "</ol>\n";
                }
            }
        }
        /*
        if($person->getId() == 3){
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            
            mail($person->getEmail(), "2011 Evaluator Feedback", $emailString, $headers);
            mail("dwt@ualberta.ca", "2011 Evaluator Feedback", $emailString, $headers);
            exit;
        }
        */
        $fp = fopen("mail/{$person->getName()}.html", 'w');
        fwrite($fp, $emailString);
        fclose($fp);
    }
}

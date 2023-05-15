<?php
  
class UserFrailtyIndexAPI extends API{

    static $checkanswers = array(
        "Physical Activity" => array(
            array( //Question: During the last 7 days, how much time did you spend sitting during the day?
                "ReportSection"=>"behaviouralassess", 
                "blobItem"=>"behave1_avoid",
                "answer_scores"=> array(
                    "Some of the day"=>0,
                    "Most of the day"=>0.5,
                    "All day"=>1,
                )
            ),
            array( //Question: During the last 7 days, on how many days did you walk for at least 10 minutes at a time?
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"behave0_avoid",
                "answer_scores"=> array(
                    "Most days (5-7 days)"=>0,
                    "Some days(2-4 days)"=>0.5,
                    "Rarely or not at all"=>1,
                )
            ),
            array( //During the last 7 days, on how many days did you do moderate physical ... or other fitness activities?
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"behave2_avoid",
                "answer_scores"=> array(
                    "Most days (5-7 days)"=>0,
                    "Some days(2-4 days)"=>0.5,
                    "Rarely or not at all"=>1,
                )
            )
        ),
        "Multiple Medications" => array(
            array( //Question: How many prescription medications do you take?
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"meds1_avoid",
                "answer_scores"=> array(
                    "0-4"=>0,
                    "5 or more"=>1,
                )
            )
        ),
        "Fatigue" => array(
            array( //Question: During the past week, how often have you felt that everything was an effort or you could not get going?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid1",
                "answer_scores"=> array(
                    "Rarely"=>0,
                    "Occasional amount"=>0.5,
                    "Most of the time"=>1,
                )
            ),
            array( //Question: During your waking time, how often do you feel tired or fatigued?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid2",
                "answer_scores"=> array(
                    "Rarely"=>0,
                    "Occasional amount"=>0.5,
                    "Most of the time"=>1,
                )
            ),
            array( //Question: During the past month how would you rate your sleep quality overall?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid9",
                "answer_scores"=> array(
                    "Good"=>0,
                    "Bad"=>1
                )
            )
        ),
        "Mental Health" => array(
            array( //Question: Over the past two weeks have you been bothered by not being able to stop or control worrying?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid3",
                "answer_scores"=> array(
                    "Not at all"=>0,
                    "Several days"=>0.5,
                    "More days than not"=>1,
                )
            ),
            array( //Question: Over the past two weeks have you been bothered by little interest or pleasure in doing things?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid4",
                "answer_scores"=> array(
                    "Not at all"=>0,
                    "Several days"=>0.5,
                    "More days than not"=>1,
                )
            )
        ),
        "Memory" => array(
            array( //Question: How would you rate your memory overall?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid5",
                "answer_scores"=> array(
                    "Good"=>0,
                    "Fair"=>0.5,
                    "Poor"=>1,
                )
            )
        ),
        "Falls and Balance" => array(
            array( //Question: How many falls (including slips, trips, and falls to the ground) did you have in the last year?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid6",
                "answer_scores"=> array(
                    "No falls"=>0,
                    "1 fall"=>0.5,
                    "2 or more falls"=> 1,
                )
            ),
            array( //Question: Do you have any problems keeping your balance?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid21",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                )
            )
        ),
        "Walking Speed" => array(
            array( //Question: Which of the following best describes your walking speed?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid7",
                "answer_scores"=> array(
                    "Normal or brisk"=>0,
                    "Stroll at an easy pace"=>0.5,
                    "Very slow/unable to walk"=>1,
                )
            ),
            array( //Question: Is the distance that you are able to walk limited by your health?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"SYMPTOMS8SPECIFY",
                "answer_scores"=> array(
                    "Less than 1 kilometer occasionally" => 0.25,
                    "Less than 1 kilometer most days" => 0.5,
                    "Less than 100 meters most occasionally" => 0.75,
                    "Less than 100 meters most days" => 1
                )
            )
        ),
        "Nutritional Status" => array(
            array( //Question: Has your food intake decreased over the last 3 months due to loss of appetite, digestive problems, chewing or swallowing difficulties?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid10",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                )
            ),
            array( //Question: Have you lost more than 3kg in weight over the last 3 months?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid11",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                    "Don't know"=>1,
                )
            )
        ),
        "Oral Health" => array(
            array( //Question: Have you had any pain in your mouth while chewing?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid12",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                )
            ),
            array( //Question: Have you had to interrupt meals because of problems with poorly fitting dentures, not enough teeth, or a dry mouth?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid13",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                )
            )
        ),
        "Pain" => array(
            array( //Question: How much bodily pain have you had during the past four weeks?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid14",
                "answer_scores"=> array(
                    "None or mild"=>0,
                    "Moderate"=>0.5,
                    "Severe"=>1,
                )
            ),
            array( //Question: Do you have pain in your feet:
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid15",
                "answer_scores"=> array(
                    "Rarely"=>0,
                    "Occasional amount"=>0.5,
                    "Most of the time"=>1,
                )
            )
        ),
        "Strength" => array(
            array( //Question: Do you experience problems in your daily life due to weakness in your hands?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid16",
                "answer_scores"=> array(
                    "No"=>0,
                    "Sometimes"=>0.5,
                    "Yes"=>1,
                )
            ),
            array( //Question: Do you experience problems in your daily life due to weakness in your legs or feet?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid17",
                "answer_scores"=> array(
                    "No"=>0,
                    "Sometimes"=>0.5,
                    "Yes"=>1,
                )
            )
        ),
        "Urinary Continence" => array(
            array( //Question: During the last 3 months, have you leaked urine (even a small amount)? 
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid18",
                "answer_scores"=> array(
                    "No"=>0,
                    "Yes"=>1,
                )
            )
        ),
        "Sensory: Hearing and Vision" => array(
            array( //Question: In the past month, how much has your eyesight interfered with your life in general?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid19",
                "answer_scores"=> array(
                    "Not at all"=>0,
                    "A little"=>0.5,
                    "A fair amount"=>1,
                )
            ),
            array( //Question: In the past month, how much has your hearing interfered with your life in general?
                "ReportSection"=>"clinicalfrailty",
                "blobItem"=>"symptoms_avoid20",
                "answer_scores"=> array(
                    "Not at all"=>0,
                    "A little"=>0.5,
                    "A fair amount"=>1,
                )
            )
        )
    );


    function processParams($params){

    }
    
    static function interactScore($answer){
        $score = 0;
        switch($answer){
            case "None": $score += 0; break;
            case "1": $score += 1; break;
            case "2": $score += 2; break;
            case "3-4": $score += 3; break;
            case "5-8": $score += 4; break;
            case "9+": $score += 5; break;
        }
        return $score;
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
    
    function getSymptomsScore($user_id, $reportType){
        $score = 0;
        $ans = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "clinicalfrailty", "symptoms_avoid", $user_id);
        if($ans != null && isset($ans["symptoms_avoid"])){
            foreach($ans["symptoms_avoid"] as $symptom){
                if($symptom != "None"){
                    $score += 1;
                }
            }
        }
        return $score;
    }
    
    function getSelfPerceivedHealth($user_id, $reportType, $raw=false){
        $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid6", $user_id);
        if($raw){
            return $ans;
        }
        if($ans != null && $ans <= 50){
            return 1;
        }
        return 0;
    }
    
    function getBehavioralScores($user_id, $reportType){
        $scores = array();
        
        // Activity
        $answers = array($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "behave1_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "behave0_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "behave2_avoid", $user_id));
        $score = 0;
        foreach($answers as $answer){
            if($answer == "All day" || $answer == "Rarely or not at all"){
                $score += 1;
            }
        }
        $scores["Activity"] = ($score >= 1) ? 1 : 0;
        
        // Vaccination
        $answers = array($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "vaccinate2_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "vaccinate3_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "vaccinate4_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "vaccinate5_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "vaccinate6_avoid", $user_id));
        $score = 0;
        foreach($answers as $answer){
            if($answer == "No"){
                $score += 1;
            }
        }
        $scores["Vaccination"] = ($score >= 1) ? 1 : 0;
        
        // Optimize Medication
        $answer = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "meds3_avoid", $user_id);
        $scores["Optimize Medication"] = ($answer == "No") ? 1 : 0;
        
        // Interact
        $answers = array($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact1_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact2_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact3_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact4_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact5_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact6_avoid", $user_id));
        $score = 0;
        foreach($answers as $answer){
            $score += self::interactScore($answer);
        }
        $scores["Interact"] = ($score >= 12) ? 1 : 0;
        
        // Diet and Nutrition
        $answers = array($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "diet1_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "diet2_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "diet3_avoid", $user_id),
                         $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "diet4_avoid", $user_id));
        $score = 0;
        foreach($answers as $answer){
            if($answer == "No"){
                $score += 1;
            }
        }
        $scores["Diet and Nutrition"] = ($score > 0) ? 1 : 0;

        return $scores;
    }
    
    function getLonelinessScores($user_id, $reportType){
        $a1s = array("Hardly ever","Some of the time","Often");
        $a2s = array("Hardly ever","Some of the time","Often");
        $a3s = array("Hardly ever","Some of the time","Often");
    
        $a1 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact7_avoid", $user_id));
        $a2 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact8_avoid", $user_id));
        $a3 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "interact9_avoid", $user_id));
        
        $answers = array(array_search($a1, $a1s)+1,
                         array_search($a2, $a2s)+1,
                         array_search($a3, $a3s)+1);
        
        return $answers;
    }
    
    function getHealthScores($user_id, $reportType){
        $a1s = array("I have no problems in walking about",
                     "I have slight problems in walking about",
                     "I have moderate problems in walking about",
                     "I have severe problems in walking about",
                     "I am unable to walk about");
                     
        $a2s = array("I have no problems washing or dressing myself",
                     "I have slight problems washing or dressing myself",
                     "I have moderate problems washing or dressing myself",
                     "I have severe problems washing or dressing myself",
                     "I am unable to wash or dress myself");
                     
        $a3s = array("I have no problems doing my usual activities",
                     "I have slight problems doing my usual activities",
                     "I have moderate problems doing my usual activities",
                     "I have severe problems doing my usual activities",
                     "I am unable to do my usual activities");
                     
        $a4s = array("I have no pain or discomfort",
                     "I have slight pain or discomfort",
                     "I have moderate pain or discomfort",
                     "I have severe pain or discomfort",
                     "I have extreme pain or discomfort");
                     
        $a5s = array("I am not anxious or depressed",
                     "I am slightly anxious or depressed",
                     "I am moderately anxious or depressed",
                     "I am severely anxious or depressed",
                     "I am extremely anxious or depressed");
    
        $a1 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid", $user_id));
        $a2 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid2", $user_id));
        $a3 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid3", $user_id));
        $a4 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid4", $user_id));
        $a5 = trim($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid5", $user_id));
        
        $answers = array(array_search($a1, $a1s)+1,
                         array_search($a2, $a2s)+1,
                         array_search($a3, $a3s)+1,
                         array_search($a4, $a4s)+1,
                         array_search($a5, $a5s)+1);
        
        return $answers;
    }
    
    function getCFS($user_id, $reportType){
        $q66 = array_sum(array(($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help2_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help3_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help4_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help5_avoid", $user_id) == "Yes") ? 1 : 0));
        
        $q67 = array_sum(array(($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help6_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help7_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help8_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help9_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help10_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help11_avoid", $user_id) == "Yes") ? 1 : 0,
                               ($this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "help12_avoid", $user_id) == "Yes") ? 1 : 0));
        
        $q63 = $this->getSymptomsScore($user_id, $reportType);
        $q43 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "HEALTH_QUESTIONS", "healthstatus_avoid6", $user_id);
        $q44 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "clinicalfrailty", "symptoms_avoid1", $user_id);
        $q17 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "behaviouralassess", "behave2_avoid", $user_id);
        
        if($q66 > 0 && $q66 <= 2){
            return 6;
        }
        else if ($q66 >= 3){
            return 7;
        }
        else if($q66 == 0){
            if($q67 >= 1 && $q67 <= 4){
                return 5;
            }
            else if($q67 >= 5){
                return 6;
            }
            else if($q67 == 0){
                if($q63 >= 10){
                    return 4;
                }
                else if($q63 >= 0 && $q63 <= 9){
                    if($q43 >= 0 && $q43 <= 50){
                        // Poor/Very Poor
                        return 4;
                    }
                    else if($q43 >= 51 && $q43 <= 75){
                        // Good/Very Good
                        if($q44 == "Rarely" || $q44 == "Occasional amount"){
                            if($q17 == "Rarely or not at all"){
                                return 3;
                            }
                            else if($q17 == "Most days (5-7 days)" || $q17 == "Some days(2-4 days)"){
                                return 2;
                            }
                        }
                        else if($q44 == "Most of the time"){
                            return 4;
                        }
                    }
                    else if($q43 >= 76){
                        // Excellent
                        if($q44 == "Rarely"){
                            if($q17 == "Rarely or not at all"){
                                return 2;
                            }
                            else if($q17 == "Most days (5-7 days)" || $q17 == "Some days(2-4 days)"){
                                return 1;
                            }
                        }
                        else if($q44 == "Occasional amount"){
                            if($q17 == "Rarely or not at all"){
                                return 3;
                            }
                            else if($q17 == "Most days (5-7 days)" || $q17 == "Some days(2-4 days)"){
                                return 2;
                            }
                        }
                        else if($q44 == "Most of the time"){
                            return 4;
                        }
                    }
                }
            }
        }
        return 0;
    }

    function getFrailtyScore($user_id, $reportType="RP_AVOID"){
        $scores = array();
        $hasSubmitted = AVOIDDashboard::hasSubmittedSurvey($user_id, $reportType);
        if($hasSubmitted){
            foreach(self::$checkanswers as $category => $categories){
                $score = 0;
                foreach($categories as $answer){
                    $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, $answer["ReportSection"], $answer["blobItem"], $user_id);
                    $check_answers_list = $answer["answer_scores"];
                    foreach($check_answers_list as $key=>$value){
                        if($key == $ans){
                            $score = $score + $value;
                        }
                    }
                }
                $scores[$category] = $score;
            }
        }
        $scores["Health Conditions"] = ($hasSubmitted) ? $this->getSymptomsScore($user_id, $reportType) : 0;
        $scores["Self-Perceived Health"] = ($hasSubmitted) ? $this->getSelfPerceivedHealth($user_id, $reportType) : 0;
        $scores["Total"] = 0;
        foreach($scores as $key => $score){
            if($key != "Total"){
                $scores["Total"] += $score;
            }
        }
        $scores["Behavioral"] = ($hasSubmitted) ? $this->getBehavioralScores($user_id, $reportType) : array("Activity" => 0, 
                                                                                                            "Vaccination" => 0,
                                                                                                            "Optimize Medication" => 0,
                                                                                                            "Interact" => 0,
                                                                                                            "Diet and Nutrition" => 0);
        $scores["Health"] = ($hasSubmitted) ? $this->getHealthScores($user_id, $reportType) : array(0,0,0,0,0);
        $scores["VAS"] = ($hasSubmitted) ? $this->getSelfPerceivedHealth($user_id, $reportType, true) : 0;
        $scores["CFS"] = ($hasSubmitted) ? $this->getCFS($user_id, $reportType) : 0;
        
        // Labels
        if($scores["Total"] >= 0 && $scores["Total"] <= 3){
            $scores["Label"] = "very low risk";
            $scores["LabelFr"] = "risque très faible";
        }
        else if($scores["Total"] > 3 && $scores["Total"] <= 8){
            $scores["Label"] = "low risk";
            $scores["LabelFr"] = "risque faible";
        }
        else if($scores["Total"] > 8 && $scores["Total"] <= 16){
            $scores["Label"] = "medium risk";
            $scores["LabelFr"] = "risque moyen";
        }
        else if($scores["Total"] > 16){
            $scores["Label"] = "high risk";
            $scores["LabelFr"] = "risque élevé";
        }
        return $scores;
    }
    
    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang, $wgRequest, $wgOut, $wgMessage;
        //get user
        header("Content-type: text/json");
        $user = Person::newFromId($wgUser->getId());
        if(!isset($_GET['id'])){
                $user_id = $wgUser->getId();
        }
        else{
            if(!$user->isRoleAtLeast(MANAGER)){
                echo "Permission Required\n";
            }
            $user_id = $_GET['id'];
        }
        $scores = $this->getFrailtyScore($user_id);
        $myJSON = json_encode($scores);
        echo $myJSON;
        exit;
    }


    function isLoginRequired(){
            return true;
        }
    }


?>

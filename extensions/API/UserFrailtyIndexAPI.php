<?php
  
class UserFrailtyIndexAPI extends API{

    static $checkanswers = array(
        array(
            "reportType"=> "RP_AVOID", //Question: During the last 7 days, how much time did you spend sitting during the day?
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"behave1_avoid",
            "answer_scores"=> array(
                "Some of the day"=>0,
                "Most of the day"=>0.5,
                "All day"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During the last 7 days, on how many days did you walk for at least 10 minutes at a time?
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"behave0_avoid",
            "answer_scores"=> array(
                "Most days (5-7 days)"=>0,
                "Some days(2-4 days)"=>0.5,
                "Rarely or not at all"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //During the last 7 days, on how many days did you do moderate physical ... or other fitness activities?
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"behave2_avoid",
            "answer_scores"=> array(
                "Most days (5-7 days)"=>0,
                "Some days(2-4 days)"=>0.5,
                "Rarely or not at all"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: How many prescription medications do you take?
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds1_avoid",
            "answer_scores"=> array(
                "0-4"=>0,
                "5 or more"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During the last 7 days, how much time did you spend sitting during the day?
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"behave0_avoid",
            "answer_scores"=> array(
                "Most days (5-7 days)"=>0,
                "Some days(2-4 days)"=>0.5,
                "Rarely or not at all"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During the past week, how often have you felt that everything was an effort or you could not get going?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid1",
            "answer_scores"=> array(
                "Rarely"=>0,
                "Occasional amount"=>0.5,
                "Most of the time"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid2",
            "answer_scores"=> array(
                "Rarely"=>0,
                "Occasional amount"=>0.5,
                "Most of the time"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid3",
            "answer_scores"=> array(
                "Not at all"=>0,
                "Several days"=>0.5,
                "More days than not"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid4",
            "answer_scores"=> array(
                "Not at all"=>0,
                "Several days"=>0.5,
                "More days than not"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid5",
            "answer_scores"=> array(
                "Good"=>0,
                "Fair"=>0.5,
                "Poor"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid6",
            "answer_scores"=> array(
                "No falls"=>0,
                "1 fall"=>0.5,
                "2 or more falls"=> 1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid21",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid7",
            "answer_scores"=> array(
                "Normal or brisk"=>0,
                "Stroll at an easy pace"=>0.5,
                "Very slow/unable to walk"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid9",
            "answer_scores"=> array(
                "Good"=>0,
                "Bad"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid10",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid11",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
                "Don't know"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid12",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid13",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid14",
            "answer_scores"=> array(
                "None or mild"=>0,
                "Moderate"=>0.5,
                "Severe"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid15",
            "answer_scores"=> array(
                "Rarely"=>0,
                "Occasional amount"=>0.5,
                "Most of the time"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid16",
            "answer_scores"=> array(
                "No"=>0,
                "Sometimes"=>0.5,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid17",
            "answer_scores"=> array(
                "No"=>0,
                "Sometimes"=>0.5,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid18",
            "answer_scores"=> array(
                "No"=>0,
                "Yes"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid19",
            "answer_scores"=> array(
                "Not at all"=>0,
                "A little"=>0.5,
                "A fair amount"=>1,
            )
        ),
        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid20",
            "answer_scores"=> array(
                "Not at all"=>0,
                "A little"=>0.5,
                "A fair amount"=>1,
            )
	),

        array(
            "reportType"=> "RP_AVOID", //Question: During your waking time, how often do you feel tired or fatigued?
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"SYMPTOMS8SPECIFY",
            "answer_scores"=> array(

                "Less than 1 kilometer occasionally"=>0.25,
                "Less than 1 kilometer most days"=>0.5,
                "Less than 100 meters most occasionally"=>0.75,
                "Less than 100 meters most days"=>1,
            )
        ),
    );


    function processParams($params){

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

    function getFrailtyScore($user_id){
	    $score = 0;
	    foreach(self::$checkanswers as $answer){

            $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $answer["reportType"], $answer["ReportSection"], $answer["blobItem"], $user_id);
	    
            $check_answers_list = $answer["answer_scores"];
            foreach($check_answers_list as $key=>$value){
                if($key == $ans){
                    $score = $score + $value;
                }
            }
	    }
	    return $score;
        
    }


    function getSymptomsScore($user_id){
        $checkanswers = array(
        	array(
            		"reportType"=> "RP_AVOID", //Question: During the last 7 days, how much time did you spend sitting during the day?
            		"ReportSection"=>"clinicalfrailty",
            		"blobItem"=>"symptoms_avoid",
		    )
	    );
	    $score = 0;
        foreach($checkanswers as $answer){
	    $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $answer["reportType"], $answer["ReportSection"], $answer["blobItem"], $user_id);
	    if($ans != null){
		    $score = $score + count($ans["symptoms_avoid"]);
	    }
        }
        return $score;


    }
    
    
    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang,$wgRequest,$wgOut, $wgMessage;
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
        $score = $this->getFrailtyScore($user_id);
        $score = $score + $this->getSymptomsScore($user_id);
        $myJSON = json_encode($score);
        echo $myJSON;
        exit;
        }


        function isLoginRequired(){
            return true;
        }
    }


?>

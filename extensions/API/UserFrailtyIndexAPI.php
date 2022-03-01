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
                "Most"=>0,
                "Some"=>0.5,
                "Rarely"=>1,
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
                "Most"=>0,
                "Some"=>0.5,
                "Rarely"=>1,
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
	    $testarray = array();
	    $test = false;
	    foreach(self::$checkanswers as $answer){

            $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $answer["reportType"], $answer["ReportSection"], $answer["blobItem"], $user_id);
	    
            $check_answers_list = $answer["answer_scores"];
	    foreach($check_answers_list as $key=>$value){
		    if($key == $ans){
			$testarray[] = $ans;
			$score = $score + $value;
		    }
            }
	    }
	    if($test){
		return $testarray;
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

        $myJSON = json_encode($score);
        echo $myJSON;
        exit;
    }

   function isLoginRequired(){
       return true;
   }
}


?>

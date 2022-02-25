<?php

class UserTagsAPI extends API{

    static $checkanswers = array(
        array("reportType"=> "RP_AVOID",
            "ReportSection"=>"AVOID_Questions_tab0",
            "blobItem"=>"internetissues_avoid",
            "answer"=> "No",
            "tags"=>array("cyber seniors")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"active_specify_end",
            "answer"=> "I am physically and/or mentally unable to be active",
            "tags"=>array("peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"active_specify_end",
            "answer"=> "I don't know where/how to get help in my community",
            "tags"=>array("activity programs")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"active_specify_end",
            "answer"=> "I have trouble maintaining a routine when it comes to activity",
            "tags"=>array("activity module","ingredients for change module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"vax_end",
            "answer"=> "I was not aware of the recommended vaccines",
            "tags"=>array("vaccination module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"vax_end",
            "answer"=> "I don't know where or how to get vaccinated",
            "tags"=>array("vaccination programs", "peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"vax_end",
            "answer"=> "I don't see the point of getting vaccinated",
            "tags"=>array("vaccination module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds_end",
            "answer"=> "I was not aware that this is recommended",
            "tags"=>array("optimize medication", "optimize medication module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds_end",
            "answer"=> "I do not feel comfortable and/or prepared to have this conversation with a healthcare provider",
            "tags"=>array("peer coaching participant", "medtrack")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds_end",
            "answer"=> "I do not know who to talk to about this",
            "tags"=>array("optimize medication programs")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds_end",
            "answer"=> "I have had my medication reviewed in the past, but find it hard to remember each year",
            "tags"=>array("peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"meds_end",
            "answer"=> "I do not understand why this is important",
            "tags"=>array("optimize medication", "optimize medication module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"interact_end",
            "answer"=> "I have been restricted due to COVID-19 public health measures",
            "tags"=>array("cyber seniors", "peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"interact_end",
            "answer"=> "I find it physically/mentally difficult to participate in social interactions",
            "tags"=>array("peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"interact_end",
            "answer"=> "I am not aware of opportunities for social interaction in my community",
            "tags"=>array("interact programs", "health connections")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"interact_end",
            "answer"=> "I have trouble maintaining social connections over time",
            "tags"=>array("interact programs", "peer coaching participant")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"interact_end",
            "answer"=> "I do not feel that I need more interaction than I already have",
            "tags"=>array("interact module", "Interaction Vid")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"diet_end",
            "answer"=> "I find it physically/mentally difficult to do this",
            "tags"=>array("peer coaching participant", "dietitian services")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"diet_end",
            "answer"=> "I was not aware of one or more of the recommendations in the questions above",
            "tags"=>array("peer coaching participant", "diet and nutrition module")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"diet_end",
            "answer"=> "It's difficult for me to access nutritious and/or culturally appropriate food because of where I live",
            "tags"=>array("diet and nutrition programs", "driving programs")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"diet_end",
            "answer"=> "I can't afford the type of food that I would like to eat",
            "tags"=>array("food banks and stands")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"behaviouralassess",
            "blobItem"=>"diet_end",
            "answer"=> "I have trouble maintaining a healthy eating routine",
            "tags"=>array("peer coaching participant", "dietitian services")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid21",
            "answer"=> "Yes",
            "tags"=>array("movement and mindfulness programs")
        ),
        array(
            "reportType"=> "RP_AVOID",
            "ReportSection"=>"clinicalfrailty",
            "blobItem"=>"symptoms_avoid19",
            "answer"=> "Yes",
            "tags"=>array("movement and mindfulness programs")
        )
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

    function checkReadinessForChange($user_id){
        $answers_list = array();
        $readinessanswers = array(
                array("reportType"=> "RP_AVOID",
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"lifestyle"),
                array("reportType"=> "RP_AVOID",
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"lifestyle2"),
                array("reportType"=> "RP_AVOID",
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"lifestyle3"),
                array("reportType"=> "RP_AVOID",
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"lifestyle4"),
                array("reportType"=> "RP_AVOID",
                "ReportSection"=>"behaviouralassess",
                "blobItem"=>"lifestyle5"),
            );
            foreach($readinessanswers as $ranswer){
                $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $ranswer["reportType"], $ranswer["ReportSection"], $ranswer["blobItem"], $user_id);
                //break;
                if($ans == null){
                    return null;
                }
                else{
                    $answers_list[] = $ans;
                }
        }
            if(!in_array("I don’t think I need to make a change in this area but am still interested in what this program has to offer (pre-contemplation)", $answers_list)){
                if(!in_array("I am already engaged in some healthy behaviours in this area but want to create lasting habits (action)", $answers_list)
                &&
                !in_array("I am already engaged in health behaviours and am interested to continue to improve even more (maintenance)", $answers_list)
                ){
                    return "Peer coaching participant";
                }
                else if(!in_array("I am interested in changing my lifestyle in this area and need some help getting started (contemplation)", $answers_list)
                        &&
                        !in_array("I am interested in changing my lifestyle in this area and would like to start planning my first steps (preparation)", $answers_list)
                        ){
                            return "Peer coaching volunteer";
                        }
                }
                else{
                    return null;
                }
        
        return null;
    }
    
    function getTags($user_id){
        $tags = array();
        foreach(self::$checkanswers as $answer){
            //$answer = self::$checkanswers[1];
            $ans = $this->getBlobValue(BLOB_TEXT, YEAR, $answer["reportType"], $answer["ReportSection"], $answer["blobItem"], $user_id);
            //$tags[] = $ans;
            //break;
            if($ans == null){
                continue;
            }
            if(is_array($ans)){
                if(array_key_exists($answer["blobItem"], $ans))
                {
                    if(in_array($answer["answer"], $ans[$answer["blobItem"]])){
                        $tags_check = $answer["tags"];
                        foreach($tags_check as $tag){
                            if(!in_array($tag, $tags)){
                                $tags[] = $tag;
                            }
                        }
                    }
                }
            }
            else{
                if($ans == $answer["answer"]){
                    $tags_check = $answer["tags"];
                    foreach($tags_check as $tag){
                        if(!in_array($tag, $tags)){
                            $tags[] = $tag;
                        }
                    }
                }
            }
            //break;
        }
        $readiness = $this->checkReadinessForChange($user_id);
        if($readiness != null){
            $tags[] = $readiness;
        }
        return $tags;
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
        $tags = $this->getTags($user_id);
        //$myJSON = json_encode($ans[$answer["blobItem"]]);

        $myJSON = json_encode($tags);
        echo $myJSON;
        exit;
    }

   function isLoginRequired(){
       return true;
   }
}
?>

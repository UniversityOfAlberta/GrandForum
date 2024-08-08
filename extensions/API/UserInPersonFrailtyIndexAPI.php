<?php
  
class UserInPersonFrailtyIndexAPI extends UserFrailtyIndexAPI {
    
    static $checkanswers = array(
        "Vision" => array(
            "avoid_vision" => array()
        ),
        "Hearing" => array(
            "avoid_hearing" => array(),
            "avoid_hearing_whisper1" => array()
        ),
        "Communication" => array(
            "avoid_communication" => array(),
            "avoid_communication2" => array(),
            "avoid_communication3" => array()
        ),
        "Cognition" => array(
            "avoid_cognition3" => array(),
            "avoid_cognition2_3" => array(),
            "avoid_cognition3_3" => array(),
            "avoid_cognition4_3" => array()
        ),
        "Dementia" => array(
            "avoid_dementia" => array(),
            "avoid_dementia2" => array(),
            "avoid_dementia3" => array()
        ),
        "Depression" => array(
            "avoid_depression" => array()
        ),
        "Balance" => array(
            "avoid_balance3" => array(),
            "avoid_balance4" => array(),
            "avoid_balance5" => array(),
            "avoid_balance6" => array(),
            "avoid_balance2" => array(),
            "avoid_balance12" => array(),
            "avoid_balance9" => array()
        ),
        "ADL" => array(
            "avoid_adl" => array()
        ),
        "IADL" => array(
            "avoid_iadl" => array()
        ),
        "Caregiver" => array(
            "avoid_caregiver" => array(),
            "avoid_caregivere_fup" => array()
        ),
        "Urinary" => array(
            "avoid_urinary" => array()
        ),
        "Bowel" => array(
            "avoid_bowel" => array(),
            "avoid_bowel3" => array(),
            "avoid_bowel4" => array()
        ),
        "Medications" => array(
            "avoid_meds" => array(),
            "avoid_meds3" => array()
        ),
        "Fatigue" => array(
            "avoid_fatigue" => array(),
            "avoid_fatigue2" => array()
        ),
        "Strength" => array(
            "avoid_strength2" => array(),
            "avoid_strength5" => array()
        ),
        "Nutrition" => array(
            "avoid_nutrition1_2_2" => array(),
            "avoid_nutrition1_3" => array(),
            "avoid_nutrition4" => array()
        ),
        "Osteoporosis" => array(
            "avoid_osteo" => array(),
            "avoid_osteo2" => array()
        ),
        "Pain" => array(
            "avoid_pain" => array(),
            "avoid_pain2" => array()
        ),
        "Dental" => array(
            "avoid_dental" => array(),
            "avoid_dental2" => array(),
            "avoid_dental3" => array()
        ),
        "Lifestyle" => array(
            "avoid_lifestyle2" => array(),
            "avoid_lifestyle3" => array(),
            "avoid_lifestyle_ex" => array()
        ),
        "Chronic" => array(
            "avoid_chronic" => array()
        )
    );
    
    function getFrailtyScore($user_id, $reportType = 'RP_AVOID_INPERSON'){
        $scores = array();
        $age = $this->getBlobValue(BLOB_TEXT, YEAR, 'RP_AVOID', "AVOID_Questions_tab0", "avoid_age", $user_id);
        $gender = $this->getBlobValue(BLOB_TEXT, YEAR, 'RP_AVOID', "AVOID_Questions_tab0", "avoid_gender", $user_id);
        
        // 1. Vision
        $vision1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_vision", $user_id);
        $scores['Vision'] = 0;
        if($vision1 <= 6){
            $scores['Vision'] += 1;
            $scores['Vision#avoid_vision'] = 1;
        }
        
        
        // 2. Hearing
        $hearing1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_hearing", $user_id);
        $hearing2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_hearing_whisper1", $user_id);
        $scores['Hearing'] = 0;
        if($hearing1 == "Yes"){
            $scores['Hearing'] += 1;
            $scores['Hearing#avoid_hearing'] = 1;
        }
        if($hearing2 == "Fail"){
            $scores['Hearing'] += 1;
            $scores['Hearing#avoid_hearing_whisper1'] = 1;
        }
        
        // 3. Communication
        $communication1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication", $user_id);
        $communication2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication2", $user_id);
        $communication3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication3", $user_id);
        $scores['Communication'] = 0;
        if($communication1 == "Yes"){
            $scores['Communication'] += 1;
            $scores['Communication#avoid_communication'] = 1;
        }
        if($communication2 == "Yes"){
            $scores['Communication'] += 1;
            $scores['Communication#avoid_communication2'] = 1;
        }
        if($communication3 == "Yes"){
            $scores['Communication'] += 1;
            $scores['Communication#avoid_communication3'] = 1;
        }
        
        // 4. Cognition
        $cognition1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition3", $user_id);
        $cognition2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition2_3", $user_id);
        $cognition3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition3_3", $user_id);
        $cognition4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition4_3", $user_id);
        $scores['Cognition'] = 0;
        if($cognition1 == "Below Average"){
            $scores['Cognition'] += 1;
            $scores['Cognition#avoid_cognition3'] = 1;
        }
        if($cognition2 == "Below Average"){
            $scores['Cognition'] += 1;
            $scores['Cognition#avoid_cognition2_3'] = 1;
        }
        if($cognition3 == "Below Average"){
            $scores['Cognition'] += 1;
            $scores['Cognition#avoid_cognition3_3'] = 1;
        }
        if($cognition4 == "Below Average"){
            $scores['Cognition'] += 1;
            $scores['Cognition#avoid_cognition4_3'] = 1;
        }
        
        // 5. Dementia
        $dementia1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia", $user_id);
        $dementia2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia2", $user_id);
        $dementia3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia3", $user_id);
        $scores['Dementia'] = 0;
        if($dementia1 <= 1){
            $scores['Dementia'] += 1;
            $scores['Dementia#avoid_dementia'] = 1;
        }
        if($dementia2 < 15){
            $scores['Dementia'] += 1;
            $scores['Dementia#avoid_dementia2'] = 1;
        }
        if($dementia3 == "Mildly Abnormal"){
            $scores['Dementia'] += 0.5;
            $scores['Dementia#avoid_dementia3'] = 0.5;
        }
        else if($dementia3 == "Abnormal"){
            $scores['Dementia'] += 1;
            $scores['Dementia#avoid_dementia3'] = 1;
        }
        
        // 6. Depression
        $depression1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression", $user_id);
        $depression2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression2", $user_id);
        $depression3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression3", $user_id);
        $depression4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression4", $user_id);
        $depression5 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression5", $user_id);
        $scores['Depression'] = 0;
        $subscore = 0;
        if($depression1 == "No"){
            $subscore += 1;
        }
        if($depression2 == "Yes"){
            $subscore += 1;
        }
        if($depression3 == "Yes"){
            $subscore += 1;
        }
        if($depression4 == "Yes"){
            $subscore += 1;
        }
        if($depression5 == "Yes"){
            $subscore += 1;
        }
        
        if($subscore >= 2){
            $scores['Depression'] += 1;
            $scores['Depression#avoid_depression'] = 1;
        }
        
        // 7. Balance
        $balance1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance3", $user_id);
        $balance2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance4", $user_id);
        $balance3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance5", $user_id);
        $balance4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance6", $user_id);
        $balance5 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance2", $user_id);
        $balance6 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance12", $user_id);
        $balance7 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance9", $user_id);
        $scores['Balance'] = 0;
        if($balance1 == "Yes"){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance3'] = 1;
        }
        if($balance2 == "Yes"){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance4'] = 1;
        }
        if($balance3 == "Yes"){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance5'] = 1;
        }
        if($balance4 >= 14){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance6'] = 1;
        }
        if($balance5 == "Yes"){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance2'] = 1;
        }
        if($balance6 == "Yes"){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance12'] = 1;
        }
        if($balance7 >= 6){
            $scores['Balance'] += 1;
            $scores['Balance#avoid_balance9'] = 1;
        }
        
        // 8. ADL
        $adl1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_adl", $user_id);
        $scores['ADL'] = @count($adl1['avoid_adl']);
        $scores['ADL#avoid_adl'] = @count($adl1['avoid_adl']);
        
        // 9. IADL
        $iadl1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_iadl", $user_id);
        $scores['IADL'] = @count($iadl1['avoid_iadl']);
        $scores['IADL#avoid_iadl'] = @count($iadl1['avoid_iadl']);
        
        // 10. Caregiver
        $caregiver1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_caregiver", $user_id);
        $caregiver2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_caregivere_fup", $user_id);
        $scores['Caregiver'] = 0;
        if($caregiver1 == "Yes"){
            $scores['Caregiver'] += 1;
            $scores['Caregiver#avoid_caregiver'] = 1;
        }
        if($caregiver2 == "Yes"){
            $scores['Caregiver'] += 1;
            $scores['Caregiver#avoid_caregivere_fup'] = 1;
        }
        
        // 11. Urinary
        $urinary1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_urinary", $user_id);
        $scores['Urinary'] = 0;
        if($urinary1 == "Yes"){
            $scores['Urinary'] += 1;
            $scores['Urinary#avoid_urinary'] = 1;
        }
        
        // 12. Bowel
        $bowel1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel", $user_id);
        $bowel2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel3", $user_id);
        $bowel3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel4", $user_id);
        $scores['Bowel'] = 0;
        if($bowel1 == "Yes"){
            $scores['Bowel'] += 1;
            $scores['Bowel#avoid_bowel'] = 1;
        }
        if($bowel2 == "Yes"){
            $scores['Bowel'] += 1;
            $scores['Bowel#avoid_bowel3'] = 1;
        }
        if($bowel3 == "Yes"){
            $scores['Bowel'] += 1;
            $scores['Bowel#avoid_bowel4'] = 1;
        }
        
        // 13. Medications
        $medications1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_meds", $user_id);
        $medications2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_meds3", $user_id);
        $scores['Medications'] = 0;
        if($medications1 >= 5){
            $scores['Medications'] += 1;
            $scores['Medications#avoid_meds'] = 1;
        }
        if($medications2 == "Yes"){
            $scores['Medications'] += 1;
            $scores['Medications#avoid_meds3'] = 1;
        }
        
        // 14. Fatigue
        $fatigue1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_fatigue", $user_id);
        $fatigue2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_fatigue2", $user_id);
        $scores['Fatigue'] = 0;
        if($fatigue1 == "Yes"){
            $scores['Fatigue'] += 1;
            $scores['Fatigue#avoid_fatigue'] = 1;
        }
        if($fatigue2 >= 3){
            $scores['Fatigue'] += 1;
            $scores['Fatigue#avoid_fatigue2'] = 1;
        }
        
        // 15. Strength
        $strength1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_strength2", $user_id);
        $strength2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_strength5", $user_id);
        $scores['Strength'] = 0;
        if($gender == "Man" && $strength1 < 21){
            $scores['Strength'] += 1;
            $scores['Strength#avoid_strength2'] = 1;
        }
        else if($gender == "Woman" && $strength1 < 14){
            $scores['Strength'] += 1;
            $scores['Strength#avoid_strength2'] = 1;
        }
        
        if($gender == "Man"){
            if(($age < 65 && $strength2 < 14) ||
               ($age < 70 && $strength2 < 12) ||
               ($age < 75 && $strength2 < 12) ||
               ($age < 80 && $strength2 < 11) ||
               ($age < 85 && $strength2 < 10) ||
               ($age < 90 && $strength2 < 8) ||
               ($age < 95 && $strength2 < 7)){
                $scores['Strength'] += 1;
                $scores['Strength#avoid_strength5'] = 1;
            }
        }
        else if($gender == "Woman"){
            if(($age < 65 && $strength2 < 12) ||
               ($age < 70 && $strength2 < 11) ||
               ($age < 75 && $strength2 < 10) ||
               ($age < 80 && $strength2 < 10) ||
               ($age < 85 && $strength2 < 9) ||
               ($age < 90 && $strength2 < 8) ||
               ($age < 95 && $strength2 < 4)){
                $scores['Strength'] += 1;
                $scores['Strength#avoid_strength5'] = 1;
            }
        }
        
        // 16. Nutrition
        $nutrition1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_2_2", $user_id);
        $nutrition2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_3", $user_id);
        $nutrition2b = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_4", $user_id);
        $nutrition3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition4", $user_id);
        $scores['Nutrition'] = 0;
        if($nutrition1 == "Yes"){
            $scores['Nutrition'] += 1;
            $scores['Nutrition#avoid_nutrition1_2_2'] = 1;
        }
        if(($nutrition2 >= 10 && $nutrition2b == "lbs") || 
           ($nutrition2 >= 4.535924 && $nutrition2b == "kg")){
            $scores['Nutrition'] += 1;
            $scores['Nutrition#avoid_nutrition1_3'] = 1;
        }
        if($nutrition3 == "No"){
            $scores['Nutrition'] += 1;
            $scores['Nutrition#avoid_nutrition4'] = 1;
        }
        
        // 17. Osteoporosis
        $osteoporosis1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_osteo", $user_id);
        $osteoporosis2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_osteo2", $user_id);
        $scores['Osteoporosis'] = 0;
        if($osteoporosis1 < 800){
            $scores['Osteoporosis'] += 1;
            $scores['Osteoporosis#avoid_osteo'] = 1;
        }
        if($osteoporosis2 < 3){
            $scores['Osteoporosis'] += 1;
            $scores['Osteoporosis#avoid_osteo2'] = 1;
        }
       
        // 18. Pain
        $pain1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_pain", $user_id);
        $pain2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_pain2", $user_id);
        $scores['Pain'] = 0;
        if($pain1 == "Yes"){
            $scores['Pain'] += 1;
            $scores['Pain#avoid_pain'] = 1;
        }
        if($pain2 >= 5){
            $scores['Pain'] += 1;
            $scores['Pain#avoid_pain2'] = 1;
        }
        
        // 19. Immunization
        
        // 20. Dental
        $dental1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental", $user_id);
        $dental2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental2", $user_id);
        $dental3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental3", $user_id);
        $scores['Dental'] = 0;
        if($dental1 == "No"){
            $scores['Dental'] += 1;
            $scores['Dental#avoid_dental'] = 1;
        }
        if($dental2 == "No"){
            $scores['Dental'] += 1;
            $scores['Dental#avoid_dental2'] = 1;
        }
        if($dental3 == "Yes"){
            $scores['Dental'] += 1;
            $scores['Dental#avoid_dental3'] = 1;
        }
        
        // 21. Lifestyle
        $lifestyle1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle2", $user_id);
        $lifestyle2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle3", $user_id);
        $lifestyle3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle_ex", $user_id);
        $scores['Lifestyle'] = 0;
        if($lifestyle1 > 2){
            $scores['Lifestyle'] += 1;
            $scores['Lifestyle#avoid_lifestyle2'] = 1;
        }
        if($lifestyle2 == "Yes"){
            $scores['Lifestyle'] += 1;
            $scores['Lifestyle#avoid_lifestyle3'] = 1;
        }
        if($lifestyle3 == "No"){
            $scores['Lifestyle'] += 1;
            $scores['Lifestyle#avoid_lifestyle_ex'] = 1;
        }
        
        // 22. Chronic
        $chronic1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_chronic", $user_id);
        $scores['Chronic'] = 0;
        if(@count($chronic1['avoid_chronic']) >= 1 && @count($chronic1['avoid_chronic']) <= 2){
            $scores['Chronic'] += 0.5;
            $scores['Chronic#avoid_chronic'] = 0.5;
        }
        else if(@count($chronic1['avoid_chronic']) >= 3){
            $scores['Chronic'] += 1;
            $scores['Chronic#avoid_chronic'] = 1;
        }
        
        $scores["Total"] = 0;
        foreach($scores as $key => $score){
            if($key != "Total" && strstr($key, "#") == false){
                $scores["Total"] += $score;
            }
        }
        $scores['Score'] = $scores['Total']/63;
        return $scores;
    }
    
    function doAction($noEcho=false){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config, $wgLang, $wgRequest, $wgOut, $wgMessage;
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

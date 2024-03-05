<?php
  
class UserInPersonFrailtyIndexAPI extends UserFrailtyIndexAPI {
    
    function getFrailtyScore($user_id, $reportType = 'RP_AVOID_INPERSON'){
        $age = $this->getBlobValue(BLOB_TEXT, YEAR, 'RP_AVOID', "AVOID_Questions_tab0", "avoid_age", $user_id);
        $gender = $this->getBlobValue(BLOB_TEXT, YEAR, 'RP_AVOID', "AVOID_Questions_tab0", "avoid_gender", $user_id);
        
        $score = 0;
        
        // 1. Vision
        $vision1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_vision", $user_id);
        
        if($vision1 <= 6){
            $score += 1;
        }
        
        // 2. Hearing
        $hearing1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_hearing", $user_id);
        $hearing2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_hearing_whisper1", $user_id);
        
        if($hearing1 == "Yes"){
            $score += 1;
        }
        if($hearing2 == "Fail"){
            $score += 1;
        }
        
        // 3. Communication
        $communication1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication", $user_id);
        $communication2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication2", $user_id);
        $communication3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_communication3", $user_id);
        
        if($communication1 == "Yes"){
            $score += 1;
        }
        if($communication2 == "Yes"){
            $score += 1;
        }
        if($communication3 == "Yes"){
            $score += 1;
        }
        
        // 4. Cognition
        $cognition1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition3", $user_id);
        $cognition2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition2_3", $user_id);
        $cognition3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition3_3", $user_id);
        $cognition4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_cognition4_3", $user_id);
        
        if($cognition1 == "Below Average"){
            $score += 1;
        }
        if($cognition2 == "Below Average"){
            $score += 1;
        }
        if($cognition3 == "Below Average"){
            $score += 1;
        }
        if($cognition4 == "Below Average"){
            $score += 1;
        }
        
        // 5. Dementia
        $dementia1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia", $user_id);
        $dementia2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia2", $user_id);
        $dementia3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dementia3", $user_id);
        
        if($dementia1 <= 1){
            $score += 1;
        }
        if($dementia2 < 15){
            $score += 1;
        }
        if($dementia3 == "Mildly Abnormal"){
            $score += 0.5;
        }
        else if($dementia3 == "Abnormal"){
            $score += 1;
        }
        
        // 6. Depression
        $depression1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression", $user_id);
        $depression2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression2", $user_id);
        $depression3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression3", $user_id);
        $depression4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression4", $user_id);
        $depression5 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_depression5", $user_id);
        
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
        $score += $subscore;
        
        if($subscore >= 2){
            $score += 1;
        }
        
        // 7. Balance
        $balance1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance3", $user_id);
        $balance2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance4", $user_id);
        $balance3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance5", $user_id);
        $balance4 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance6", $user_id);
        $balance5 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance2", $user_id);
        $balance6 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance12", $user_id);
        $balance7 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_balance9", $user_id);
        
        if($balance1 == "Yes"){
            $score += 1;
        }
        if($balance2 == "Yes"){
            $score += 1;
        }
        if($balance3 == "Yes"){
            $score += 1;
        }
        if($balance4 >= 14){
            $score += 1;
        }
        if($balance5 == "Yes"){
            $score += 1;
        }
        if($balance6 == "Yes"){
            $score += 1;
        }
        if($balance7 >= 6){
            $score += 1;
        }
        
        // 8. ADL
        $adl1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_adl", $user_id);
        $score += @count($adl1['avoid_adl']);
        
        // 9. IADL
        $iadl1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_iadl", $user_id);
        $score += @count($iadl1['avoid_iadl']);
        
        // 10. Caregiver
        $caregiver1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_caregiver", $user_id);
        $caregiver2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_caregivere_fup", $user_id);
        
        if($caregiver1 == "Yes"){
            $score += 1;
        }
        if($caregiver2 == "Yes"){
            $score += 1;
        }
        
        // 11. Urinary
        $urinary1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_urinary", $user_id);
        if($urinary1 == "Yes"){
            $score += 1;
        }
        
        // 12. Bowel
        $bowel1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel", $user_id);
        $bowel2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel3", $user_id);
        $bowel3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_bowel4", $user_id);
        
        /*if($bowel1 == "Yes"){
            $score += 1;
        }*/
        if($bowel2 == "Yes"){
            $score += 1;
        }
        if($bowel3 == "Yes"){
            $score += 1;
        }
        
        // 13. Medications
        $medications1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_meds", $user_id);
        $medications2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_meds3", $user_id);
        
        if($medications1 >= 5){
            $score += 1;
        }
        if($medications2 == "Yes"){
            $score += 1;
        }
        
        // 14. Fatigue
        $fatigue1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_fatigue", $user_id);
        $fatigue2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_fatigue2", $user_id);
        
        if($fatigue1 == "Yes"){
            $score += 1;
        }
        if($fatigue2 >= 3){
            $score += 1;
        }
        
        // 15. Strength
        $strength1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_strength2", $user_id);
        $strength2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_strength5", $user_id);
        
        if($gender == "Man" && $age >= 70 && $strength1 < 21){
            $score += 1;
        }
        else if($gender == "Woman" && $age >= 70 && $strength1 < 14){
            $score += 1;
        }
        
        if($gender == "Man"){
            if(($age < 65 && $strength2 < 14) ||
               ($age < 70 && $strength2 < 12) ||
               ($age < 75 && $strength2 < 12) ||
               ($age < 80 && $strength2 < 11) ||
               ($age < 85 && $strength2 < 10) ||
               ($age < 90 && $strength2 < 8) ||
               ($age < 95 && $strength2 < 7)){
                $score += 1;
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
                $score += 1;
            }
        }
        
        // 16. Nutrition
        $nutrition1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_2_2", $user_id);
        $nutrition2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_3", $user_id);
        $nutrition2b = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition1_4", $user_id);
        $nutrition3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_nutrition4", $user_id);
        
        if($nutrition1 == "Yes"){
            $score += 1;
        }
        if(($nutrition2 >= 10 && $nutrition2b == "lbs") || 
           ($nutrition2 >= 4.535924 && $nutrition2b == "kg")){
            $score += 1;
        }
        if($nutrition3 == "No"){
            $score += 1;
        }
        
        // 17. Osteoporosis
        $osteoporosis1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_osteo", $user_id);
        $osteoporosis2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_osteo2", $user_id);
        
        if($osteoporosis1 < 800){
            $score += 1;
        }
        if($osteoporosis2 < 3){
            $score += 1;
        }
       
        // 18. Pain
        $pain1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_pain", $user_id);
        
        if($pain1 == "Yes"){
            $score += 1;
        }
        
        // 19. Immunization
        
        // 20. Dental
        $dental1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental", $user_id);
        $dental2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental2", $user_id);
        $dental3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_dental3", $user_id);
        
        if($dental1 == "No"){
            $score += 1;
        }
        if($dental2 == "No"){
            $score += 1;
        }
        if($dental3 == "Yes"){
            $score += 1;
        }
        
        // 21. Lifestyle
        $lifestyle1 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle2", $user_id);
        $lifestyle2 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle3", $user_id);
        $lifestyle3 = $this->getBlobValue(BLOB_TEXT, YEAR, $reportType, "InPersonAssessment", "avoid_lifestyle_ex", $user_id);
        
        if($lifestyle1 > 2){
            $score += 1;
        }
        if($lifestyle2 == "Yes"){
            $score += 1;
        }
        if($lifestyle3 == "No"){
            $score += 1;
        }
        
        // 22. Chronic
        $chronic1 = $this->getBlobValue(BLOB_ARRAY, YEAR, $reportType, "InPersonAssessment", "avoid_chronic", $user_id);
        
        if(@count($chronic1['avoid_chronic']) >= 1 && @count($chronic1['avoid_chronic']) <= 2){
            $score += 0.5;
        }
        else if(@count($chronic1['avoid_chronic']) >= 3){
            $score += 1;
        }
        
        return $score/65;
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

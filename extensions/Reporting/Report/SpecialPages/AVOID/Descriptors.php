<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Descriptors'] = 'Descriptors'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Descriptors'] = $dir . 'Descriptors.i18n.php';
$wgSpecialPageGroups['Descriptors'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'Descriptors::createSubTabs';

require_once("EQ5D5L.php");

function runDescriptors($par) {
    Descriptors::execute($par);
}

define("AGG_NA", "N/A");
define("AGG_WORSE", "Regression");
define("AGG_SAME", "No Change");
define("AGG_BETTER", "Improvement");
define("AGG_BEST", "No Change (Max)");

class Descriptors extends SpecialPage {
    
    function __construct() {
        SpecialPage::__construct("Descriptors", null, true, 'runDescriptors');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    static function compareProgress(&$aggregates, &$status, $val1, $rp, $blobItem, $category, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $val1;
        }
        $val2 = self::getBlobData("behaviouralassess", $blobItem, $person, YEAR, $rp);
        if($val1 != "" && 
           ($val2 == "" || $val2 == $val1) &&
           UserFrailtyIndexAPI::$checkanswers[$category][$blobItem]["answer_scores"][$val1] == min(UserFrailtyIndexAPI::$checkanswers[$category][$blobItem]["answer_scores"])){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $val1;
        }
        if($val2 != ""){
            if(UserFrailtyIndexAPI::$checkanswers[$category][$blobItem]["answer_scores"][$val1] > 
               UserFrailtyIndexAPI::$checkanswers[$category][$blobItem]["answer_scores"][$val2]){
                // Improvement
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = AGG_WORSE;
            }
            $val1 = $val2;
        }
        return $val1;
    }
    
    static function compareVaccines(&$aggregates, &$status, $count1, $rp, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $count1;
        }
        $MAX = 5;
        $count2 = 0;
        $v1 = self::getBlobData("behaviouralassess", "vaccinate1_avoid", $person, YEAR, $rp);
        $v2 = self::getBlobData("behaviouralassess", "vaccinate2_avoid", $person, YEAR, $rp);
        $v3 = self::getBlobData("behaviouralassess", "vaccinate3_avoid", $person, YEAR, $rp);
        $v4 = self::getBlobData("behaviouralassess", "vaccinate4_avoid", $person, YEAR, $rp);
        $v5 = self::getBlobData("behaviouralassess", "vaccinate5_avoid", $person, YEAR, $rp);
        $v6 = self::getBlobData("behaviouralassess", "vaccinate6_avoid", $person, YEAR, $rp);
        
        if($v1 == "Yes"){
            // Exclude from dataset
            $status = AGG_NA;
            return 0;
        }
        
        $count2 += ($v2 == "Yes") ? 1 : 0;
        $count2 += ($v3 == "Yes") ? 1 : 0;
        $count2 += ($v4 == "Yes") ? 1 : 0;
        $count2 += ($v5 == "Yes") ? 1 : 0;
        $count2 += ($v6 == "Yes") ? 1 : 0;
        
        if($count1 == $MAX && ($v1 == "" || $count2 == $count1)){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $count1;
        }
        
        if($v1 != ""){
            if($count2 > $count1){
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = ($count1 == $count2) ? AGG_SAME: AGG_WORSE;
            }
            $count1 = $count2;
        }
        return $count1;
    }
    
    static function compareMeds(&$aggregates, &$status, $val1, $rp, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $val1;
        }
        $val2 = self::getBlobData("behaviouralassess", "meds3_avoid", $person, YEAR, $rp);
        
        $score1 = ($val1 == "Yes") ? 0 : 1;
        $score2 = ($val2 == "Yes") ? 0 : 1;
        
        if($val1 != "" && 
           ($val2 == "" || $val2 == $val1) &&
           $score1 == 0){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $val1;
        }
        if($val2 != ""){
            if($score1 > $score2){
                // Improvement
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = ($score1 == $score2) ? AGG_SAME: AGG_WORSE;
            }
            $val1 = $val2;
        }
        return $val1;
    }
    
    static function compareInteract(&$aggregates, &$status, $score1, $rp, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $score1;
        }
        $score2 = 0;
        $v1 = self::getBlobData("behaviouralassess", "interact1_avoid", $person, YEAR, $rp);
        $v2 = self::getBlobData("behaviouralassess", "interact2_avoid", $person, YEAR, $rp);
        $v3 = self::getBlobData("behaviouralassess", "interact3_avoid", $person, YEAR, $rp);
        $v4 = self::getBlobData("behaviouralassess", "interact4_avoid", $person, YEAR, $rp);
        $v5 = self::getBlobData("behaviouralassess", "interact5_avoid", $person, YEAR, $rp);
        $v6 = self::getBlobData("behaviouralassess", "interact6_avoid", $person, YEAR, $rp);
        $answers = array($v1, $v2, $v3, $v4, $v5, $v6);

        foreach($answers as $answer){
            $score2 += UserFrailtyIndexAPI::interactScore($answer);
        }
        
        if($score1 >= 12 && ($v1 == "" || $score2 >= 12)){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $score1;
        }
        
        if($v1 != ""){
            if($score2 >= 12){
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = ($score1 == $score2) ? AGG_SAME: AGG_WORSE;
            }
            $score1 = $score2;
        }
        return $score1;
    }
    
    static function compareLoneliness(&$aggregates, &$status, $score1, $rp, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $score1;
        }
        $options = array("Hardly ever","Some of the time","Often");
        $score2 = 0;
        $v1 = self::getBlobData("behaviouralassess", "interact7_avoid", $person, YEAR, $rp);
        $v2 = self::getBlobData("behaviouralassess", "interact8_avoid", $person, YEAR, $rp);
        $v3 = self::getBlobData("behaviouralassess", "interact9_avoid", $person, YEAR, $rp);
        
        $score2 += array_search($v1, $options);
        $score2 += array_search($v2, $options);
        $score2 += array_search($v3, $options);
        
        if($score1 == 0 && ($v1 == "" || $score2 == $score1)){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $score1;
        }
        
        if($v1 != ""){
            if($score2 < $score1){
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = ($score1 == $score2) ? AGG_SAME: AGG_WORSE;
            }
            $score1 = $score2;
        }
        return $score1;
    }
    
    static function compareNutrition(&$aggregates, &$status, $count1, $rp, $person){
        if(!AVOIDDashboard::hasSubmittedSurvey($person->getId(), $rp)){
            return $count1;
        }
        $MAX = 3;
        $count2 = 0;
        $v1 = self::getBlobData("behaviouralassess", "diet1_avoid", $person, YEAR, $rp);
        $v2 = self::getBlobData("behaviouralassess", "diet2_avoid", $person, YEAR, $rp);
        $v3 = self::getBlobData("behaviouralassess", "diet3_avoid", $person, YEAR, $rp);
        
        $count2 += ($v1 == "Yes") ? 1 : 0;
        $count2 += ($v2 == "Yes") ? 1 : 0;
        $count2 += ($v3 == "Yes") ? 1 : 0;
        
        if($count1 == $MAX && ($v1 == "" || $count2 == $count1)){
            // Already max, exclude from dataset
            $status = AGG_BEST;
            return $count1;
        }
        
        if($v1 != ""){
            if($count2 > $count1){
                $aggregates[] = 1;
                $status = AGG_BETTER;
            }
            else{
                // No Improvement
                $aggregates[] = 0;
                $status = ($count1 == $count2) ? AGG_SAME: AGG_WORSE;
            }
            $count1 = $count2;
        }
        return $count1;
    }
    
    static function aggregateStats($person, &$aggregates, &$status){
        $sit = self::getBlobData("behaviouralassess", "behave1_avoid", $person, YEAR, "RP_AVOID");
        $sit = self::compareProgress($aggregates[0][0], $status[0][0], $sit, "RP_AVOID_THREEMO", "behave1_avoid", "Physical Activity", $person);
        $sit = self::compareProgress($aggregates[0][1], $status[0][1], $sit, "RP_AVOID_SIXMO", "behave1_avoid", "Physical Activity", $person);
        $sit = self::compareProgress($aggregates[0][2], $status[0][2], $sit, "RP_AVOID_NINEMO", "behave1_avoid", "Physical Activity", $person);
        $sit = self::compareProgress($aggregates[0][3], $status[0][3], $sit, "RP_AVOID_TWELVEMO", "behave1_avoid", "Physical Activity", $person);
        
        $walk = self::getBlobData("behaviouralassess", "behave0_avoid", $person, YEAR, "RP_AVOID");
        $walk = self::compareProgress($aggregates[1][0], $status[1][0], $walk, "RP_AVOID_THREEMO", "behave0_avoid", "Physical Activity", $person);
        $walk = self::compareProgress($aggregates[1][1], $status[1][1], $walk, "RP_AVOID_SIXMO", "behave0_avoid", "Physical Activity", $person);
        $walk = self::compareProgress($aggregates[1][2], $status[1][2], $walk, "RP_AVOID_NINEMO", "behave0_avoid", "Physical Activity", $person);
        $walk = self::compareProgress($aggregates[1][3], $status[1][3], $walk, "RP_AVOID_TWELVEMO", "behave0_avoid", "Physical Activity", $person);
        
        $activity = self::getBlobData("behaviouralassess", "behave2_avoid", $person, YEAR, "RP_AVOID");
        $activity = self::compareProgress($aggregates[2][0], $status[2][0], $activity, "RP_AVOID_THREEMO", "behave2_avoid", "Physical Activity", $person);
        $activity = self::compareProgress($aggregates[2][1], $status[2][1], $activity, "RP_AVOID_SIXMO", "behave2_avoid", "Physical Activity", $person);
        $activity = self::compareProgress($aggregates[2][2], $status[2][2], $activity, "RP_AVOID_NINEMO", "behave2_avoid", "Physical Activity", $person);
        $activity = self::compareProgress($aggregates[2][3], $status[2][3], $activity, "RP_AVOID_TWELVEMO", "behave2_avoid", "Physical Activity", $person);
        
        // Vaccine Stats
        $count = 0;
        $count = self::compareVaccines($aggregates[3][-1], $status[3][-1], $count, "RP_AVOID", $person);
        $count = self::compareVaccines($aggregates[3][0],  $status[3][0], $count, "RP_AVOID_THREEMO", $person);
        $count = self::compareVaccines($aggregates[3][1],  $status[3][1], $count, "RP_AVOID_SIXMO", $person);
        $count = self::compareVaccines($aggregates[3][2],  $status[3][2], $count, "RP_AVOID_NINEMO", $person);
        $count = self::compareVaccines($aggregates[3][3],  $status[3][3], $count, "RP_AVOID_TWELVEMO", $person);
        
        // Meds
        $meds = self::getBlobData("behaviouralassess", "meds3_avoid", $person, YEAR, "RP_AVOID");
        $meds = self::compareMeds($aggregates[4][0], $status[4][0], $meds, "RP_AVOID_THREEMO", $person);
        $meds = self::compareMeds($aggregates[4][1], $status[4][1], $meds, "RP_AVOID_SIXMO", $person);
        $meds = self::compareMeds($aggregates[4][2], $status[4][2], $meds, "RP_AVOID_NINEMO", $person);
        $meds = self::compareMeds($aggregates[4][3], $status[4][3], $meds, "RP_AVOID_TWELVEMO", $person);
        
        // Interact
        $count = 0;
        $count = self::compareInteract($aggregates[5][-1], $status[5][-1], $count, "RP_AVOID", $person);
        $count = self::compareInteract($aggregates[5][0],  $status[5][0], $count, "RP_AVOID_THREEMO", $person);
        $count = self::compareInteract($aggregates[5][1],  $status[5][1], $count, "RP_AVOID_SIXMO", $person);
        $count = self::compareInteract($aggregates[5][2],  $status[5][2], $count, "RP_AVOID_NINEMO", $person);
        $count = self::compareInteract($aggregates[5][3],  $status[5][3], $count, "RP_AVOID_TWELVEMO", $person);
        
        // Loneliness
        $count = 0;
        $count = self::compareLoneliness($aggregates[6][-1], $status[6][-1], $count, "RP_AVOID", $person);
        $count = self::compareLoneliness($aggregates[6][0],  $status[6][0], $count, "RP_AVOID_THREEMO", $person);
        $count = self::compareLoneliness($aggregates[6][1],  $status[6][1], $count, "RP_AVOID_SIXMO", $person);
        $count = self::compareLoneliness($aggregates[6][2],  $status[6][2], $count, "RP_AVOID_NINEMO", $person);
        $count = self::compareLoneliness($aggregates[6][3],  $status[6][3], $count, "RP_AVOID_TWELVEMO", $person);
        
        // Nutrition
        $count = 0;
        $count = self::compareNutrition($aggregates[7][-1], $status[7][-1], $count, "RP_AVOID", $person);
        $count = self::compareNutrition($aggregates[7][0],  $status[7][0], $count, "RP_AVOID_THREEMO", $person);
        $count = self::compareNutrition($aggregates[7][1],  $status[7][1], $count, "RP_AVOID_SIXMO", $person);
        $count = self::compareNutrition($aggregates[7][2],  $status[7][2], $count, "RP_AVOID_NINEMO", $person);
        $count = self::compareNutrition($aggregates[7][3],  $status[7][3], $count, "RP_AVOID_TWELVEMO", $person);
        return $aggregates;
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut, $EQ5D5L;
        $me = Person::newFromWgUser();
        $wgOut->setPageTitle("Descriptives");
        $people = Person::getAllPeople(CI);
        
        $api = new UserFrailtyIndexAPI();
        $nIntake = 0;
        $n6Month = 0;
        
        $mobility = array(0,0,0,0,0);
        $selfcare = array(0,0,0,0,0);
        $activities = array(0,0,0,0,0);
        $pain = array(0,0,0,0,0);
        $anxiety = array(0,0,0,0,0);
        $srh = array(0,0,0,0);
        
        $mobility6 = array(0,0,0,0,0);
        $selfcare6 = array(0,0,0,0,0);
        $activities6 = array(0,0,0,0,0);
        $pain6 = array(0,0,0,0,0);
        $anxiety6 = array(0,0,0,0,0);
        $srh6 = array(0,0,0,0);
        
        $frailty = array(0,0,0,0);
        $frailty6 = array(0,0,0,0);
        $frailtyByAge = array("All" => array(),
                              "<60-64" => array(),
                              "65-74" => array(),
                              "75+" => array());
        
        $cfs = array(0,0,0,0,0,0,0,0,0,0);
        $cfs6 = array(0,0,0,0,0,0,0,0,0,0);
        $cfsByAge = array("<60-64" => array(0,0,0,0,0,0,0,0,0,0),
                          "65-74" => array(0,0,0,0,0,0,0,0,0,0),
                          "75+" => array(0,0,0,0,0,0,0,0,0,0));
                          
        $eqByAge = array("All" => array(),
                         "<60-64" => array(),
                         "65-74" => array(),
                         "75+" => array());
        $selfHealthByAge = array("All" => array(),
                                 "<60-64" => array(),
                                 "65-74" => array(),
                                 "75+" => array());
        $lonelinessByAge = array("All" => array("3-5" => array(), "6-9" => array()),
                                 "<60-64"  => array("3-5" => array(), "6-9" => array()),
                                 "65-74"  => array("3-5" => array(), "6-9" => array()),
                                 "75+"  => array("3-5" => array(), "6-9" => array()));
        
        $subroles = array(0,0,0,0);
        $ages = array(0,0,0,0,0,0,0);
        $genders = array(0,0,0,0);
        $ethnicities = array(0,0,0,0,0,0,0,0,0);
        $incomes = array(0,0,0,0,0,0,0);
        $livings = array(0,0,0,0);
        $educations = array(0,0,0,0,0,0,0);
        
        $aggregates = array(
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array()),
            array(array(),array(),array(),array())
        );
        
        foreach($people as $person){
            if(!$person->isRoleAtMost(CI)){
                continue;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId()) && self::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $fScores = $api->getFrailtyScore($person->getId(), "RP_AVOID");
                $scores = $fScores["Health"];
                $selfHealth = self::getBlobData("HEALTH_QUESTIONS", "healthstatus_avoid6", $person, YEAR);
                $eqId = implode("", $api->getHealthScores($person->getId(), "RP_AVOID"));
                $eqMean = $EQ5D5L[$eqId];
                $loneliness = array_sum($api->getLonelinessScores($person->getId(), "RP_AVOID"));
                $age = self::getBlobData("AVOID_Questions_tab0", "avoid_age", $person, YEAR);
                $gender = self::getBlobData("AVOID_Questions_tab0", "avoid_gender", $person, YEAR);
                $ethnicity = self::getBlobData("AVOID_Questions_tab0", "ethnicity_avoid", $person, YEAR)["ethnicity_avoid"];
                $income = self::getBlobData("AVOID_Questions_tab0", "income_avoid", $person, YEAR);
                $living = self::getBlobData("AVOID_Questions_tab0", "living_avoid", $person, YEAR);
                $education = self::getBlobData("AVOID_Questions_tab0", "education_avoid", $person, YEAR);
                $total = $fScores["Total"]/36;
                
                if($total >= 0 && $total <= 0.1){
                    $frailty[0]++;
                }
                else if($total >= 0.1 && $total <= 0.21){
                    $frailty[1]++;
                }
                else if($total >= 0.21 && $total < 0.45){
                    $frailty[2]++;
                }
                else {
                    $frailty[3]++;
                }
                
                $frailtyByAge["All"][] = $total;
                $eqByAge["All"][] = $eqMean;
                $selfHealthByAge["All"][] = $selfHealth;
                if($loneliness <= 5){
                    $lonelinessByAge["All"]["3-5"][] = $loneliness;
                }
                else{
                    $lonelinessByAge["All"]["6-9"][] = $loneliness;
                }
                if($age == "less than 60" || $age < 65){
                    $frailtyByAge["<60-64"][] = $total;
                    @$cfsByAge["<60-64"][$fScores["CFS"]]++;
                    $eqByAge["<60-64"][] = $eqMean;
                    $selfHealthByAge["<60-64"][] = $selfHealth;
                    if($loneliness <= 5){
                        $lonelinessByAge["<60-64"]["3-5"][] = $loneliness;
                    }
                    else{
                        $lonelinessByAge["<60-64"]["6-9"][] = $loneliness;
                    }
                }
                else if($age >= 65 && $age < 75){
                    $frailtyByAge["65-74"][] = $total;
                    @$cfsByAge["65-74"][$fScores["CFS"]]++;
                    $eqByAge["65-74"][] = $eqMean;
                    $selfHealthByAge["65-74"][] = $selfHealth;
                    if($loneliness <= 5){
                        $lonelinessByAge["65-74"]["3-5"][] = $loneliness;
                    }
                    else{
                        $lonelinessByAge["65-74"]["6-9"][] = $loneliness;
                    }
                }
                else if($age >= 75){
                    $frailtyByAge["75+"][] = $total;
                    @$cfsByAge["75+"][$fScores["CFS"]]++;
                    $eqByAge["75+"][] = $eqMean;
                    $selfHealthByAge["75+"][] = $selfHealth;
                    if($loneliness <= 5){
                        $lonelinessByAge["75+"]["3-5"][] = $loneliness;
                    }
                    else{
                        $lonelinessByAge["75+"]["6-9"][] = $loneliness;
                    }
                }
                
                if($person->isSubRole('phone-based')){
                    $subroles[1]++;
                }
                else if($person->isSubRole('in-person with volunteer')){
                    $subroles[2]++;
                }
                else if($person->isSubRole('paper copy')){
                    $subroles[3]++;
                }
                else{
                    $subroles[0]++;
                }
                
                if($age == "less than 60" || $age < 65){
                    $ages[0]++;
                }
                else if($age >= 65 && $age < 75){
                    $ages[1]++;
                }
                else if($age >= 75){
                    $ages[2]++;
                }
                
                if($gender == "Man"){
                    $genders[0]++;
                }
                else if($gender == "Woman"){
                    $genders[1]++;
                }
                else if($gender == "Prefer not to say"){
                    $genders[2]++;
                }
                else if($gender == "Prefer to self describe myself"){
                    $genders[3]++;
                }
                
                if(in_array("Black", $ethnicity) !== false){
                    $ethnicities[0]++;
                }
                if(in_array("East/Southeast Asian", $ethnicity) !== false){
                    $ethnicities[1]++;
                }
                if(in_array("Indigenous (First Nations, Metis, Inuk/Inuit)", $ethnicity) !== false){
                    $ethnicities[2]++;
                }
                if(in_array("Latino", $ethnicity) !== false){
                    $ethnicities[3]++;
                }
                if(in_array("Middle Eastern", $ethnicity) !== false){
                    $ethnicities[4]++;
                }
                if(in_array("South Asian", $ethnicity) !== false){
                    $ethnicities[5]++;
                }
                if(in_array("White", $ethnicity) !== false){
                    $ethnicities[6]++;
                }
                if(in_array("Other", $ethnicity) !== false){
                    $ethnicities[7]++;
                }
                if(in_array("Prefer not to answer", $ethnicity) !== false){
                    $ethnicities[8]++;
                }
                
                if($income == "Under $10,000"){
                    $incomes[0]++;
                }
                else if($income == "$10,000 to $24,999"){
                    $incomes[1]++;
                }
                else if($income == "$25,000 to $49,999"){
                    $incomes[2]++;
                }
                else if($income == "$50,000 to $74,999"){
                    $incomes[3]++;
                }
                else if($income == "$75,000 to $99,999"){
                    $incomes[4]++;
                }
                else if($income == "$100,000 or more"){
                    $incomes[5]++;
                }
                else if($income == "Prefer not to say"){
                    $incomes[6]++;
                }
                
                if($living == "Living alone"){
                    $livings[0]++;
                }
                else if($living == "Living with partner (includes with or without children)"){
                    $livings[1]++;
                }
                else if($living == "Living with relatives and non-relatives"){
                    $livings[2]++;
                }
                else if($living == "Prefer not to say"){
                    $livings[3]++;
                }
                
                if($education == "No certificate, diploma, or degree"){
                    $educations[0]++;
                }
                else if($education == "Secondary (highschool) diploma or equivalency certificate"){
                    $educations[1]++;
                }
                else if($education == "Apprenticeship or trades certificate or diploma"){
                    $educations[2]++;
                }
                else if($education == "College, CEGEP, or non-university certificate or diploma"){
                    $educations[3]++;
                }
                else if($education == "University certificate of diploma below bachelor level"){
                    $educations[4]++;
                }
                else if($education == "University certificate, diploma, or degree at bachelor level or above"){
                    $educations[5]++;
                }
                else if($education == "Prefer not to say"){
                    $educations[6]++;
                }
                
                @$cfs[$fScores["CFS"]]++;
                
                @$mobility[$scores[0]]++;
                @$selfcare[$scores[1]]++;
                @$activities[$scores[2]]++;
                @$pain[$scores[3]]++;
                @$anxiety[$scores[4]]++;
                
                if($selfHealth > 0 && $selfHealth <= 25){
                    $srh[0]++;
                }
                else if($selfHealth > 25 && $selfHealth <= 50){
                    $srh[1]++;
                }
                else if($selfHealth > 50 && $selfHealth <= 75){
                    $srh[2]++;
                }
                else if($selfHealth > 75 && $selfHealth <= 100){
                    $srh[3]++;
                }
                
                $nIntake++;
                
                // Aggregate Stats
                $status = array();
                self::aggregateStats($person, $aggregates, $status);
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO") && self::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $fScores = $api->getFrailtyScore($person->getId(), "RP_AVOID_SIXMO");
                $scores = $fScores["Health"];
                $selfHealth = self::getBlobData("HEALTH_QUESTIONS", "healthstatus_avoid6", $person, YEAR, "RP_AVOID_SIXMO");
                $total = $fScores["Total"]/36;
                
                if($total >= 0 && $total <= 0.08){
                    $frailty6[0]++;
                }
                else if($total >= 0.08 && $total <= 0.22){
                    $frailty6[1]++;
                }
                else if($total >= 0.22 && $total < 0.45){
                    $frailty6[2]++;
                }
                else {
                    $frailty6[3]++;
                }
                
                @$cfs6[$fScores["CFS"]]++;
                
                @$mobility6[$scores[0]]++;
                @$selfcare6[$scores[1]]++;
                @$activities6[$scores[2]]++;
                @$pain6[$scores[3]]++;
                @$anxiety6[$scores[4]]++;
                
                if($selfHealth > 0 && $selfHealth <= 25){
                    $srh6[0]++;
                }
                else if($selfHealth > 25 && $selfHealth <= 50){
                    $srh6[1]++;
                }
                else if($selfHealth > 50 && $selfHealth <= 75){
                    $srh6[2]++;
                }
                else if($selfHealth > 75 && $selfHealth <= 100){
                    $srh6[3]++;
                }
                $n6Month++;
            }
        }
        $wgOut->addHTML("<div class='modules'>");
        @$wgOut->addHTML("<div class='module-3cols-outer'>
            <h2>Distribution of EQ-5D-5L</h2>
            <table class='wikitable'>
                <thead>
                    <tr>
                        <th>Dimension</th>
                        <th>Baseline<br />n (%)</th>
                        <th>Follow-up<br />n (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th colspan='4' style='text-align: left;'>Mobility</th></tr>
                    <tr>
                        <td>No problems</td>
                        <td>{$mobility[1]} (".number_format($mobility[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$mobility6[1]} (".number_format($mobility6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Slight problems</td>
                        <td>{$mobility[2]} (".number_format($mobility[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$mobility6[2]} (".number_format($mobility6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Moderate problems</td>
                        <td>{$mobility[3]} (".number_format($mobility[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$mobility6[3]} (".number_format($mobility6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Severe problems</td>
                        <td>{$mobility[4]} (".number_format($mobility[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$mobility6[4]} (".number_format($mobility6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Unable to walk about</td>
                        <td>{$mobility[5]} (".number_format($mobility[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$mobility6[5]} (".number_format($mobility6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    
                    <tr><th colspan='4' style='text-align: left;'>Self-care</th></tr>
                    <tr>
                        <td>No problems</td>
                        <td>{$selfcare[1]} (".number_format($selfcare[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$selfcare6[1]} (".number_format($selfcare6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Slight problems</td>
                        <td>{$selfcare[2]} (".number_format($selfcare[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$selfcare6[2]} (".number_format($selfcare6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Moderate problems</td>
                        <td>{$selfcare[3]} (".number_format($selfcare[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$selfcare6[3]} (".number_format($selfcare6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Severe problems</td>
                        <td>{$selfcare[4]} (".number_format($selfcare[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$selfcare6[4]} (".number_format($selfcare6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Unable to wash or dress</td>
                        <td>{$selfcare[5]} (".number_format($selfcare[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$selfcare6[5]} (".number_format($selfcare6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    
                    <tr><th colspan='4' style='text-align: left;'>Usual activities</th></tr>
                    <tr>
                        <td>No problems</td>
                        <td>{$activities[1]} (".number_format($activities[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$activities6[1]} (".number_format($activities6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Slight problems</td>
                        <td>{$activities[2]} (".number_format($activities[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$activities6[2]} (".number_format($activities6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Moderate problems</td>
                        <td>{$activities[3]} (".number_format($activities[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$activities6[3]} (".number_format($activities6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Severe problems</td>
                        <td>{$activities[4]} (".number_format($activities[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$activities6[4]} (".number_format($activities6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Unable to do usual activities</td>
                        <td>{$activities[5]} (".number_format($activities[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$activities6[5]} (".number_format($activities6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    
                    <tr><th colspan='4' style='text-align: left;'>Pain/discomfort</th></tr>
                    <tr>
                        <td>No pain/discomfort</td>
                        <td>{$pain[1]} (".number_format($pain[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$pain6[1]} (".number_format($pain6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Slight pain/discomfort</td>
                        <td>{$pain[2]} (".number_format($pain[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$pain6[2]} (".number_format($pain6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Moderate pain/discomfort</td>
                        <td>{$pain[3]} (".number_format($pain[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$pain6[3]} (".number_format($pain6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Severe pain/discomfort</td>
                        <td>{$pain[4]} (".number_format($pain[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$pain6[4]} (".number_format($pain6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Extreme pain/discomfort</td>
                        <td>{$pain[5]} (".number_format($pain[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$pain6[5]} (".number_format($pain6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    
                    <tr><th colspan='4' style='text-align: left;'>Anxiety/depression</th></tr>
                    <tr>
                        <td>Not anxious/depressed</td>
                        <td>{$anxiety[1]} (".number_format($anxiety[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$anxiety6[1]} (".number_format($anxiety6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Slightly anxious/depressed</td>
                        <td>{$anxiety[2]} (".number_format($anxiety[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$anxiety6[2]} (".number_format($anxiety6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Moderately anxious/depressed</td>
                        <td>{$anxiety[3]} (".number_format($anxiety[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$anxiety6[3]} (".number_format($anxiety6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Severely anxious/depressed</td>
                        <td>{$anxiety[4]} (".number_format($anxiety[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$anxiety6[4]} (".number_format($anxiety6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>Extremely anxious/depressed</td>
                        <td>{$anxiety[5]} (".number_format($anxiety[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$anxiety6[5]} (".number_format($anxiety6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    
                    <tr><th colspan='4' style='text-align: left;'>Self Reported Health</th></tr>
                    <tr>
                        <td>0-25</td>
                        <td>{$srh[0]} (".number_format($srh[0]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$srh6[0]} (".number_format($srh6[0]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>26-50</td>
                        <td>{$srh[1]} (".number_format($srh[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$srh6[1]} (".number_format($srh6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>51-75</td>
                        <td>{$srh[2]} (".number_format($srh[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$srh6[2]} (".number_format($srh6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>76-100</td>
                        <td>{$srh[3]} (".number_format($srh[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$srh6[3]} (".number_format($srh6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                </tbody>
            </table>
            </div>");
        
        @$wgOut->addHTML("<div class='module-3cols-outer'>
            <h2>Frailty Status</h2>
            <table class='wikitable'>
                <thead>
                    <tr>
                        <th>Frailty Index/36</th>
                        <th>Frailty Status (%Deficits)</th>
                        <th>Baseline<br />n (%)</th>
                        <th>Follow-up<br />n (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0 - 0.1</td>
	                    <td>Non-Frail (0 to 10%)</td>
	                    <td>{$frailty[0]} (".number_format($frailty[0]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6[0]} (".number_format($frailty6[0]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>0.11 - 0.21</td>
	                    <td>Vulnerable (>10 to 21%)</td>
	                    <td>{$frailty[1]} (".number_format($frailty[1]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6[1]} (".number_format($frailty6[1]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>0.22 - 0.45</td>
	                    <td>Frail (>21 to <45%)</td>
	                    <td>{$frailty[2]} (".number_format($frailty[2]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6[2]} (".number_format($frailty6[2]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>0.45+</td>
	                    <td>Severely Frail (≥45%)</td>
	                    <td>{$frailty[3]} (".number_format($frailty[3]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6[3]} (".number_format($frailty6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                </tbody>
            </table>
            
            <b>Baseline: Mean (SD) Frailty index by Age Group</b>
            <table class='wikitable' style='margin-top:0;'>
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>RCHA Total (N=".count($frailtyByAge["All"]).")</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>All</td>
	                    <td>".number_format(array_sum($frailtyByAge['All'])/max(1,count($frailtyByAge['All'])), 2)." (".number_format(stdev($frailtyByAge['All']), 2).")</td>
	                </tr>
	                <tr>
	                    <td><60-64</td>
	                    <td>".number_format(array_sum($frailtyByAge['<60-64'])/max(1,count($frailtyByAge['<60-64'])), 2)." (".number_format(stdev($frailtyByAge['<60-64']), 2).")</td>
	                </tr>
	                <tr>
	                    <td>65-74</td>
	                    <td>".number_format(array_sum($frailtyByAge['65-74'])/max(1,count($frailtyByAge['65-74'])), 2)." (".number_format(stdev($frailtyByAge['65-74']), 2).")</td>
	                </tr>
	                <tr>
	                    <td>75+</td>
	                    <td>".number_format(array_sum($frailtyByAge['75+'])/max(1,count($frailtyByAge['75+'])), 2)." (".number_format(stdev($frailtyByAge['75+']), 2).")</td>
                    </tr>
                </tbody>
            </table>
            </div>");
            
        @$wgOut->addHTML("<div class='module-3cols-outer'>
            <h2>CFS Score</h2>
            <table class='wikitable'>
                <thead>
                    <tr>
                        <th colspan='2'>Score</th>
                        <th>Baseline<br />n (%)</th>
                        <th>Follow-up<br />n (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Very Fit</td>
                        <td>{$cfs[1]} (".number_format($cfs[1]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[1]} (".number_format($cfs6[1]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Fit</td>
                        <td>{$cfs[2]} (".number_format($cfs[2]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[2]} (".number_format($cfs6[2]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Managing Well</td>
                        <td>{$cfs[3]} (".number_format($cfs[3]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[3]} (".number_format($cfs6[3]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Living with Very Mild Frailty</td>
                        <td>{$cfs[4]} (".number_format($cfs[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[4]} (".number_format($cfs6[4]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Living with Mild Frailty</td>
                        <td>{$cfs[5]} (".number_format($cfs[5]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[5]} (".number_format($cfs6[5]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Living with Moderate Frailty</td>
                        <td>{$cfs[6]} (".number_format($cfs[6]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[6]} (".number_format($cfs6[6]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Living with Severe Frailty</td>
                        <td>{$cfs[7]} (".number_format($cfs[7]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[7]} (".number_format($cfs6[7]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Living with Very Severe Frailty</td>
                        <td>{$cfs[8]} (".number_format($cfs[8]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[8]} (".number_format($cfs6[8]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Terminaly Ill</td>
                        <td>{$cfs[9]} (".number_format($cfs[9]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfs6[9]} (".number_format($cfs6[9]/max(1, $n6Month)*100, 1).")</td>
                    </tr>
                </tbody>
            </table>
            
            <b>Baseline: Clinical Frailty Scale</b>
            <table class='wikitable' style='margin-top:0;'>
                <thead>
                    <tr>
                        <th>Score</th>
                        <th>RCHA Total (N={$nIntake})</th>
                        <th><60-64 (N=".count($frailtyByAge['<60-64']).")</th>
                        <th>65-74 (N=".count($frailtyByAge['65-74']).")</th>
                        <th>75+ (N=".count($frailtyByAge['75+']).")</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1-3</td>
                        <td>".($cfs[1] + $cfs[2] + $cfs[3])." (".number_format(($cfs[1] + $cfs[2] + $cfs[3])/max(1, $nIntake)*100, 1).")</td>
                        <td>".($cfsByAge["<60-64"][1] + $cfsByAge["<60-64"][2] + $cfsByAge["<60-64"][3])." (".number_format(($cfsByAge["<60-64"][1] + $cfsByAge["<60-64"][2] + $cfsByAge["<60-64"][3])/max(1, count($frailtyByAge['<60-64']))*100, 1).")</td>
                        <td>".($cfsByAge["65-74"][1] + $cfsByAge["65-74"][2] + $cfsByAge["65-74"][3])." (".number_format(($cfsByAge["65-74"][1] + $cfsByAge["65-74"][2] + $cfsByAge["65-74"][3])/max(1, count($frailtyByAge['65-74']))*100, 1).")</td>
                        <td>".($cfsByAge["75+"][1] + $cfsByAge["75+"][2] + $cfsByAge["75+"][3])." (".number_format(($cfsByAge["75+"][1] + $cfsByAge["75+"][2] + $cfsByAge["75+"][3])/max(1, count($frailtyByAge['75+']))*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>{$cfs[4]} (".number_format($cfs[4]/max(1, $nIntake)*100, 1).")</td>
                        <td>{$cfsByAge["<60-64"][4]} (".number_format($cfsByAge["<60-64"][4]/max(1, count($frailtyByAge['<60-64']))*100, 1).")</td>
                        <td>{$cfsByAge["65-74"][4]} (".number_format($cfsByAge["65-74"][4]/max(1, count($frailtyByAge['65-74']))*100, 1).")</td>
                        <td>{$cfsByAge["75+"][4]} (".number_format($cfsByAge["75+"][4]/max(1, count($frailtyByAge['75+']))*100, 1).")</td>
                    </tr>
                    <tr>
                        <td>5-9</td>
                        <td>".($cfs[5] + $cfs[6] + $cfs[7] + $cfs[8] + $cfs[9])." (".number_format(($cfs[5] + $cfs[6] + $cfs[7] + $cfs[8] + $cfs[9])/max(1, $nIntake)*100, 1).")</td>
                        <td>".($cfsByAge["<60-64"][5] + $cfsByAge["<60-64"][6] + $cfsByAge["<60-64"][7] + $cfsByAge["<60-64"][8] + $cfsByAge["<60-64"][9])." (".number_format(($cfsByAge["<60-64"][5] + $cfsByAge["<60-64"][6] + $cfsByAge["<60-64"][7] + $cfsByAge["<60-64"][8] + $cfsByAge["<60-64"][9])/max(1, count($frailtyByAge['<60-64']))*100, 1).")</td>
                        <td>".($cfsByAge["65-74"][5] + $cfsByAge["65-74"][6] + $cfsByAge["65-74"][7] + $cfsByAge["65-74"][8] + $cfsByAge["65-74"][9])." (".number_format(($cfsByAge["65-74"][5] + $cfsByAge["65-74"][6] + $cfsByAge["65-74"][7] + $cfsByAge["65-74"][8] + $cfsByAge["65-74"][9])/max(1, count($frailtyByAge['65-74']))*100, 1).")</td>
                        <td>".($cfsByAge["75+"][5] + $cfsByAge["75+"][6] + $cfsByAge["75+"][7] + $cfsByAge["75+"][8] + $cfsByAge["75+"][9])." (".number_format(($cfsByAge["75+"][5] + $cfsByAge["75+"][6] + $cfsByAge["75+"][7] + $cfsByAge["75+"][8] + $cfsByAge["75+"][9])/max(1, count($frailtyByAge['75+']))*100, 1).")</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
        
        <div class='modules'>
            <div class='module-3cols-outer'>
                <h2>Member Characteristics Distribution</h2>
                <table class='wikitable'>
                    <thead>
                        <tr>
                            <th>Dimension</th>
                            <th>n (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th colspan='2' style='text-align: left;'>Sub-Roles</th></tr>
                        <tr>
                            <td>Online Independant</td>
                            <td>{$subroles[0]} (".number_format($subroles[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Phone-Based</td>
                            <td>{$subroles[1]} (".number_format($subroles[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>In-Person with volunteer</td>
                            <td>{$subroles[2]} (".number_format($subroles[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Paper Copy</td>
                            <td>{$subroles[3]} (".number_format($subroles[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Age</th></tr>
                        <tr>
                            <td><60-64</td>
                            <td>{$ages[0]} (".number_format($ages[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>65-74</td>
                            <td>{$ages[1]} (".number_format($ages[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>75+</td>
                            <td>{$ages[2]} (".number_format($ages[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Gender</th></tr>
                        <tr>
                            <td>Man</td>
                            <td>{$genders[0]} (".number_format($genders[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Woman</td>
                            <td>{$genders[1]} (".number_format($genders[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer not to say</td>
                            <td>{$genders[2]} (".number_format($genders[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer to self describe myself</td>
                            <td>{$genders[3]} (".number_format($genders[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Ethnicity</th></tr>
                        <tr>
                            <td>Black</td>
                            <td>{$ethnicities[0]} (".number_format($ethnicities[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>East/Southeast Asian</td>
                            <td>{$ethnicities[1]} (".number_format($ethnicities[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Indigenous</td>
                            <td>{$ethnicities[2]} (".number_format($ethnicities[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Latino</td>
                            <td>{$ethnicities[3]} (".number_format($ethnicities[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Middle Eastern</td>
                            <td>{$ethnicities[4]} (".number_format($ethnicities[4]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>South Asian</td>
                            <td>{$ethnicities[5]} (".number_format($ethnicities[5]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>White</td>
                            <td>{$ethnicities[6]} (".number_format($ethnicities[6]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Other</td>
                            <td>{$ethnicities[7]} (".number_format($ethnicities[7]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer not to answer</td>
                            <td>{$ethnicities[8]} (".number_format($ethnicities[8]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Income</th></tr>
                        <tr>
                            <td>Under $10,000</td>
                            <td>{$incomes[0]} (".number_format($incomes[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>$10,000 to $24,999</td>
                            <td>{$incomes[1]} (".number_format($incomes[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>$25,000 to $49,999</td>
                            <td>{$incomes[2]} (".number_format($incomes[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>$50,000 to $74,999</td>
                            <td>{$incomes[3]} (".number_format($incomes[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>$75,000 to $99,999</td>
                            <td>{$incomes[4]} (".number_format($incomes[4]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>$100,000 or more</td>
                            <td>{$incomes[5]} (".number_format($incomes[5]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer not to say</td>
                            <td>{$incomes[6]} (".number_format($incomes[6]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Living Arrangement</th></tr>
                        <tr>
                            <td>Living alone</td>
                            <td>{$livings[0]} (".number_format($livings[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Living with partner</td>
                            <td>{$livings[1]} (".number_format($livings[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Living with relatives & non-relatives</td>
                            <td>{$livings[2]} (".number_format($livings[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer not to say</td>
                            <td>{$livings[3]} (".number_format($livings[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        
                        <tr><th colspan='2' style='text-align: left;'>Education Level</th></tr>
                        <tr>
                            <td>No certificate, diploma, or degree</td>
                            <td>{$educations[0]} (".number_format($educations[0]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Secondary (highschool) diploma or equivalency certificate</td>
                            <td>{$educations[1]} (".number_format($educations[1]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Apprenticeship or trades certificate or diploma</td>
                            <td>{$educations[2]} (".number_format($educations[2]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>College, CEGEP, or non-university certificate or diploma</td>
                            <td>{$educations[3]} (".number_format($educations[3]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>University certificate of diploma below bachelor level</td>
                            <td>{$educations[4]} (".number_format($educations[4]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>University certificate, diploma, or degree at bachelor level or above</td>
                            <td>{$educations[5]} (".number_format($educations[5]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Prefer not to say</td>
                            <td>{$educations[6]} (".number_format($educations[6]/max(1, $nIntake)*100, 1).")</td>
                        </tr>
                    </tbody>
                </table>
                
                <b>Baseline: Mean (SD) EQ-5D-5L Utilities and EQ-VAS by Age Group</b>
                <table class='wikitable' style='margin-top:0;'>
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Utilities</th>
                            <th>EQ Vas</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th>RCHA Total (N=".count($eqByAge["All"]).")</th>
                            <th>RCHA Total (N=".count($selfHealthByAge["All"]).")</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>All</td>
                            <td>".number_format(array_sum($eqByAge['All'])/max(1,count($eqByAge['All'])), 3)." (".number_format(stdev($eqByAge['All']), 3).")</td>
                            <td>".number_format(array_sum($selfHealthByAge['All'])/max(1,count($selfHealthByAge['All'])), 2)." (".number_format(stdev($selfHealthByAge['All']), 2).")</td>
                        </tr>
                        <tr>
                            <td><60-64</td>
                            <td>".number_format(array_sum($eqByAge['<60-64'])/max(1,count($eqByAge['<60-64'])), 3)." (".number_format(stdev($eqByAge['<60-64']), 3).")</td>
                            <td>".number_format(array_sum($selfHealthByAge['<60-64'])/max(1,count($selfHealthByAge['<60-64'])), 2)." (".number_format(stdev($selfHealthByAge['<60-64']), 2).")</td>
                        </tr>
                        <tr>
                            <td>65-74</td>
                            <td>".number_format(array_sum($eqByAge['65-74'])/max(1,count($eqByAge['65-74'])), 3)." (".number_format(stdev($eqByAge['65-74']), 3).")</td>
                            <td>".number_format(array_sum($selfHealthByAge['65-74'])/max(1,count($selfHealthByAge['65-74'])), 2)." (".number_format(stdev($selfHealthByAge['65-74']), 2).")</td>
                        </tr>
                        <tr>
                            <td>75+</td>
                            <td>".number_format(array_sum($eqByAge['75+'])/max(1,count($eqByAge['75+'])), 3)." (".number_format(stdev($eqByAge['75+']), 3).")</td>
                            <td>".number_format(array_sum($selfHealthByAge['75+'])/max(1,count($selfHealthByAge['75+'])), 2)." (".number_format(stdev($selfHealthByAge['75+']), 2).")</td>
                        </tr>
                    </tbody>
                </table>
                
                <b>Baseline: Loneliness by Age Group (N={$nIntake})</b>
                <table class='wikitable' style='margin-top:0;'>
                    <thead>
                        <tr>
                            <th></th>
                            <th>All</th>
                            <th><60-64</th>
                            <th>65-74</th>
                            <th>75+</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>3-5 (Not Lonely) Total (%)</td>
                            <td>".count($lonelinessByAge['All']["3-5"])." (".number_format(count($lonelinessByAge['All']["3-5"])/max(1,count(array_flatten($lonelinessByAge['All'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['<60-64']["3-5"])." (".number_format(count($lonelinessByAge['<60-64']["3-5"])/max(1,count(array_flatten($lonelinessByAge['<60-64'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['65-74']["3-5"])." (".number_format(count($lonelinessByAge['65-74']["3-5"])/max(1,count(array_flatten($lonelinessByAge['65-74'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['75+']["3-5"])." (".number_format(count($lonelinessByAge['75+']["3-5"])/max(1,count(array_flatten($lonelinessByAge['75+'])))*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>6-9 (Lonely) Total (%)</td>
                            <td>".count($lonelinessByAge['All']["6-9"])." (".number_format(count($lonelinessByAge['All']["6-9"])/max(1,count(array_flatten($lonelinessByAge['All'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['<60-64']["6-9"])." (".number_format(count($lonelinessByAge['<60-64']["6-9"])/max(1,count(array_flatten($lonelinessByAge['<60-64'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['65-74']["6-9"])." (".number_format(count($lonelinessByAge['65-74']["6-9"])/max(1,count(array_flatten($lonelinessByAge['65-74'])))*100, 1).")</td>
                            <td>".count($lonelinessByAge['75+']["6-9"])." (".number_format(count($lonelinessByAge['75+']["6-9"])/max(1,count(array_flatten($lonelinessByAge['75+'])))*100, 1).")</td>
                        </tr>
                        <tr>
                            <td>Mean (SD)</td>
                            <td>".number_format(array_sum(array_flatten($lonelinessByAge['All']))/max(1,count(array_flatten($lonelinessByAge['All']))), 2)." (".number_format(stdev(array_flatten($lonelinessByAge['All'])), 2).")</td>
                            <td>".number_format(array_sum(array_flatten($lonelinessByAge['<60-64']))/max(1,count(array_flatten($lonelinessByAge['<60-64']))), 2)." (".number_format(stdev(array_flatten($lonelinessByAge['<60-64'])), 2).")</td>
                            <td>".number_format(array_sum(array_flatten($lonelinessByAge['65-74']))/max(1,count(array_flatten($lonelinessByAge['65-74']))), 2)." (".number_format(stdev(array_flatten($lonelinessByAge['65-74'])), 2).")</td>
                            <td>".number_format(array_sum(array_flatten($lonelinessByAge['75+']))/max(1,count(array_flatten($lonelinessByAge['75+']))), 2)." (".number_format(stdev(array_flatten($lonelinessByAge['75+'])), 2).")</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h2>Progress Aggregate Report</h2>
                <table class='wikitable' style='width:100%;'>
                    <tr>
                        <th colspan='2'></th>
                        <th>3 Month</th>
                        <th>6 Month</th>
                        <th>9 Month</th>
                        <th>12 Month</th>
                    </tr>
                    <tr>
                        <th rowspan='3'>A</th>
                        <td>Time spent sitting</td>
                        <td>".number_format(array_sum($aggregates[0][0])/max(1, count($aggregates[0][0]))*100, 1)."% (N=".count($aggregates[0][0]).")</td>
                        <td>".number_format(array_sum($aggregates[0][1])/max(1, count($aggregates[0][1]))*100, 1)."% (N=".count($aggregates[0][1]).")</td>
                        <td>".number_format(array_sum($aggregates[0][2])/max(1, count($aggregates[0][2]))*100, 1)."% (N=".count($aggregates[0][2]).")</td>
                        <td>".number_format(array_sum($aggregates[0][3])/max(1, count($aggregates[0][3]))*100, 1)."% (N=".count($aggregates[0][3]).")</td>
                    </tr>
                    <tr>
                        <td>Walking 10 min at a time</td>
                        <td>".number_format(array_sum($aggregates[1][0])/max(1, count($aggregates[1][0]))*100, 1)."% (N=".count($aggregates[1][0]).")</td>
                        <td>".number_format(array_sum($aggregates[1][1])/max(1, count($aggregates[1][1]))*100, 1)."% (N=".count($aggregates[1][1]).")</td>
                        <td>".number_format(array_sum($aggregates[1][2])/max(1, count($aggregates[1][2]))*100, 1)."% (N=".count($aggregates[1][2]).")</td>
                        <td>".number_format(array_sum($aggregates[1][3])/max(1, count($aggregates[1][3]))*100, 1)."% (N=".count($aggregates[1][3]).")</td>
                    </tr>
                    <tr style='border-bottom: 2px solid #ddd;'>
                        <td>Moderate activity</td>
                        <td>".number_format(array_sum($aggregates[2][0])/max(1, count($aggregates[2][0]))*100, 1)."% (N=".count($aggregates[2][0]).")</td>
                        <td>".number_format(array_sum($aggregates[2][1])/max(1, count($aggregates[2][1]))*100, 1)."% (N=".count($aggregates[2][1]).")</td>
                        <td>".number_format(array_sum($aggregates[2][2])/max(1, count($aggregates[2][2]))*100, 1)."% (N=".count($aggregates[2][2]).")</td>
                        <td>".number_format(array_sum($aggregates[2][3])/max(1, count($aggregates[2][3]))*100, 1)."% (N=".count($aggregates[2][3]).")</td>
                    </tr>
                    <tr style='border-bottom: 2px solid #ddd;'>
                        <th rowspan='1'>V</th>
                        <td>Missing vaccinations</td>
                        <td>".number_format(array_sum($aggregates[3][0])/max(1, count($aggregates[3][0]))*100, 1)."% (N=".count($aggregates[3][0]).")</td>
                        <td>".number_format(array_sum($aggregates[3][1])/max(1, count($aggregates[3][1]))*100, 1)."% (N=".count($aggregates[3][1]).")</td>
                        <td>".number_format(array_sum($aggregates[3][2])/max(1, count($aggregates[3][2]))*100, 1)."% (N=".count($aggregates[3][2]).")</td>
                        <td>".number_format(array_sum($aggregates[3][3])/max(1, count($aggregates[3][3]))*100, 1)."% (N=".count($aggregates[3][3]).")</td>
                    </tr>
                    <tr style='border-bottom: 2px solid #ddd;'>
                        <th rowspan='1'>O</th>
                        <td>Medication review</td>
                        <td>".number_format(array_sum($aggregates[4][0])/max(1, count($aggregates[4][0]))*100, 1)."% (N=".count($aggregates[4][0]).")</td>
                        <td>".number_format(array_sum($aggregates[4][1])/max(1, count($aggregates[4][1]))*100, 1)."% (N=".count($aggregates[4][1]).")</td>
                        <td>".number_format(array_sum($aggregates[4][2])/max(1, count($aggregates[4][2]))*100, 1)."% (N=".count($aggregates[4][2]).")</td>
                        <td>".number_format(array_sum($aggregates[4][3])/max(1, count($aggregates[4][3]))*100, 1)."% (N=".count($aggregates[4][3]).")</td>
                    </tr>
                    <tr>
                        <th rowspan='2'>I</th>
                        <td>Seeing family and friends improvement</td>
                        <td>".number_format(array_sum($aggregates[5][0])/max(1, count($aggregates[5][0]))*100, 1)."% (N=".count($aggregates[5][0]).")</td>
                        <td>".number_format(array_sum($aggregates[5][1])/max(1, count($aggregates[5][1]))*100, 1)."% (N=".count($aggregates[5][1]).")</td>
                        <td>".number_format(array_sum($aggregates[5][2])/max(1, count($aggregates[5][2]))*100, 1)."% (N=".count($aggregates[5][2]).")</td>
                        <td>".number_format(array_sum($aggregates[5][3])/max(1, count($aggregates[5][3]))*100, 1)."% (N=".count($aggregates[5][3]).")</td>
                    </tr>
                    <tr style='border-bottom: 2px solid #ddd;'>
                        <td>Loneliness score improvement</td>
                        <td>".number_format(array_sum($aggregates[6][0])/max(1, count($aggregates[6][0]))*100, 1)."% (N=".count($aggregates[6][0]).")</td>
                        <td>".number_format(array_sum($aggregates[6][1])/max(1, count($aggregates[6][1]))*100, 1)."% (N=".count($aggregates[6][1]).")</td>
                        <td>".number_format(array_sum($aggregates[6][2])/max(1, count($aggregates[6][2]))*100, 1)."% (N=".count($aggregates[6][2]).")</td>
                        <td>".number_format(array_sum($aggregates[6][3])/max(1, count($aggregates[6][3]))*100, 1)."% (N=".count($aggregates[6][3]).")</td>
                    </tr>
                    <tr>
                        <th rowspan='1'>D</th>
                        <td>Nutrition deficit</td>
                        <td>".number_format(array_sum($aggregates[7][0])/max(1, count($aggregates[7][0]))*100, 1)."% (N=".count($aggregates[7][0]).")</td>
                        <td>".number_format(array_sum($aggregates[7][1])/max(1, count($aggregates[7][1]))*100, 1)."% (N=".count($aggregates[7][1]).")</td>
                        <td>".number_format(array_sum($aggregates[7][2])/max(1, count($aggregates[7][2]))*100, 1)."% (N=".count($aggregates[7][2]).")</td>
                        <td>".number_format(array_sum($aggregates[7][3])/max(1, count($aggregates[7][3]))*100, 1)."% (N=".count($aggregates[7][3]).")</td>
                    </tr>
                </table>
            </div>
        </div>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Descriptors") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Descriptives", "{$wgServer}{$wgScriptPath}/index.php/Special:Descriptors", $selected);
        }
        return true;
    }
    
    static function getBlobData($blobSection, $blobItem, $person, $year, $rpType="RP_AVOID"){
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
}

?>
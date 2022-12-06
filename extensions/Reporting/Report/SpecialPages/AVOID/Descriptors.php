<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Descriptors'] = 'Descriptors'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Descriptors'] = $dir . 'Descriptors.i18n.php';
$wgSpecialPageGroups['Descriptors'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'Descriptors::createSubTabs';

function runDescriptors($par) {
    Descriptors::execute($par);
}

class Descriptors extends SpecialPage {
    
    function __construct() {
        SpecialPage::__construct("Descriptors", null, true, 'runDescriptors');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
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
        
        $mobility6 = array(0,0,0,0,0);
        $selfcare6 = array(0,0,0,0,0);
        $activities6 = array(0,0,0,0,0);
        $pain6 = array(0,0,0,0,0);
        $anxiety6 = array(0,0,0,0,0);
        
        $frailty = array(0,0,0,0);
        $frailty6 = array(0,0,0,0);
        
        $cfs = array(0,0,0,0,0,0,0,0,0,0);
        $cfs6 = array(0,0,0,0,0,0,0,0,0,0);
        
        $ages = array(0,0,0,0,0,0,0);
        $genders = array(0,0,0,0);
        $ethnicities = array(0,0,0,0,0,0,0,0,0);
        
        foreach($people as $person){
            if(!$person->isRoleAtMost(CI)){
                continue;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId()) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $fScores = $api->getFrailtyScore($person->getId(), "RP_AVOID");
                $scores = $fScores["Health"];
                $age = $this->getBlobData("AVOID_Questions_tab0", "avoid_age", $person, YEAR);
                $gender = $this->getBlobData("AVOID_Questions_tab0", "avoid_gender", $person, YEAR);
                $ethnicity = $this->getBlobData("AVOID_Questions_tab0", "ethnicity_avoid", $person, YEAR)["ethnicity_avoid"];
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
                
                if($age == "less than 60" || $age <= 60){
                    $ages[0]++;
                }
                else if($age > 60 && $age <= 65){
                    $ages[1]++;
                }
                else if($age > 65 && $age <= 70){
                    $ages[2]++;
                }
                else if($age > 70 && $age <= 75){
                    $ages[3]++;
                }
                else if($age > 75 && $age <= 80){
                    $ages[4]++;
                }
                else if($age > 80 && $age <= 85){
                    $ages[5]++;
                }
                else if($age > 85){
                    $ages[6]++;
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
                
                @$cfs[$fScores["CFS"]]++;
                
                @$mobility[$scores[0]]++;
                @$selfcare[$scores[1]]++;
                @$activities[$scores[2]]++;
                @$pain[$scores[3]]++;
                @$anxiety[$scores[4]]++;
                
                $nIntake++;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO") && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $fScores = $api->getFrailtyScore($person->getId(), "RP_AVOID_SIXMO");
                $scores = $fScores["Health"];
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
	                    <td>{$frailty6[3]} (".number_format($frailty6[3]/max(1, $n6month)*100, 1).")</td>
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
            </div>
        </div>
        
        <h2>Member Characteristics Distribution</h2>
        <table class='wikitable'>
            <thead>
                <tr>
                    <th>Dimension</th>
                    <th>n (%)</th>
                </tr>
            </thead>
            <tbody>
                <tr><th colspan='2' style='text-align: left;'>Age</th></tr>
                <tr>
                    <td>Less or equal to 60</td>
                    <td>{$ages[0]} (".number_format($ages[0]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 60-65</td>
                    <td>{$ages[1]} (".number_format($ages[1]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 65-70</td>
                    <td>{$ages[2]} (".number_format($ages[2]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 70-75</td>
                    <td>{$ages[3]} (".number_format($ages[3]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 75-80</td>
                    <td>{$ages[4]} (".number_format($ages[4]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 80-85</td>
                    <td>{$ages[5]} (".number_format($ages[5]/max(1, $nIntake)*100, 1).")</td>
                </tr>
                <tr>
                    <td>over 85</td>
                    <td>{$ages[6]} (".number_format($ages[6]/max(1, $nIntake)*100, 1).")</td>
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
                
                <tr><th colspan='2' style='text-align: left;'>Living Arrangement</th></tr>
                
                <tr><th colspan='2' style='text-align: left;'>Education Level</th></tr>
            </tbody>
        </table>");
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
    
    function getBlobData($blobSection, $blobItem, $person, $year, $rpType=null){
        $rpType = ($rpType == null) ? "RP_AVOID" : $rpType;
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
}

?>

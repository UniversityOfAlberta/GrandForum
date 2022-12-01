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
        $wgOut->setPageTitle("Descriptors");
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
        
        $frailty = array("very low risk" => 0,
                         "low risk" => 0,
                         "medium risk" => 0,
                         "high risk" => 0);
        $frailty6 = array("very low risk" => 0,
                         "low risk" => 0,
                         "medium risk" => 0,
                         "high risk" => 0);
        foreach($people as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId()) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $fScores = $api->getFrailtyScore($person->getId(), "RP_AVOID");
                $scores = $fScores["Health"];
                
                $frailty[$fScores["Label"]]++;
                
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
                
                $frailty6[$fScores["Label"]]++;
                
                @$mobility6[$scores[0]]++;
                @$selfcare6[$scores[1]]++;
                @$activities6[$scores[2]]++;
                @$pain6[$scores[3]]++;
                @$anxiety6[$scores[4]]++;
                
                $n6Month++;
            }
        }
        
        @$wgOut->addHTML("<h2>Distribution of EQ-5D-5L dimension responses at baseline and at follow-up</h2>
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
            </table>");
        
        @$wgOut->addHTML("<h2>Frailty Status</h2>
            <table class='wikitable'>
                <thead>
                    <tr>
                        <th>Score</th>
                        <th>Frailty Status (%Deficits)</th>
                        <th>Baseline<br />n (%)</th>
                        <th>Follow-up<br />n (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0 - 3</td>
	                    <td>Non-Frail (0 to 10%)</td>
	                    <td>{$frailty["very low risk"]} (".number_format($frailty["very low risk"]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6["very low risk"]} (".number_format($frailty6["very low risk"]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>3.25 - 8</td>
	                    <td>Vulnerable (>10 to 21%)</td>
	                    <td>{$frailty["low risk"]} (".number_format($frailty["low risk"]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6["low risk"]} (".number_format($frailty6["low risk"]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>8.25 - 16</td>
	                    <td>Frail (>21 to <45%)</td>
	                    <td>{$frailty["medium risk"]} (".number_format($frailty["medium risk"]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6["medium risk"]} (".number_format($frailty6["medium risk"]/max(1, $n6Month)*100, 1).")</td>
	                </tr>
	                <tr>
	                    <td>16+</td>
	                    <td>Severely Frail (≥45%)</td>
	                    <td>{$frailty["high risk"]} (".number_format($frailty["high risk"]/max(1, $nIntake)*100, 1).")</td>
	                    <td>{$frailty6["high risk"]} (".number_format($frailty6["high risk"]/max(1, $n6month)*100, 1).")</td>
                    </tr>
                </tbody>
            </table>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Descriptors") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Descriptors", "{$wgServer}{$wgScriptPath}/index.php/Special:Descriptors", $selected);
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

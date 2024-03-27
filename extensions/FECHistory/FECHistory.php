<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FECHistory'] = 'FECHistory'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FECHistory'] = $dir . 'FECHistory.i18n.php';
$wgSpecialPageGroups['FECHistory'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'FECHistory::createSubTabs';

class FECHistory extends SpecialPage{

    function FECHistory() {
        parent::__construct("FECHistory", null, true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $data = DBFunctions::execSQL("SELECT DISTINCT user_id FROM grand_personal_fec_info");
        $wgOut->addHTML("<table id='fechistory' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Going to FEC?</th>
                                    <th>PhD</th>
                                    <th>Appointment</th>
                                    <th>Assistant</th>
                                    <th>Associate</th>
                                    <th>Professor</th>
                                    <th>FSO II</th>
                                    <th>FSO III</th>
                                    <th>FSO IV</th>
                                    <th>ATS I</th>
                                    <th>ATS II</th>
                                    <th>ATS III</th>
                                    <th>Probation1</th>
                                    <th>Probation2</th>
                                    <th>Tenure</th>
                                    <th>Sabbaticals</th>
                                    <th>Retirement</th>
                                    <th>Date of Last Degree</th>
                                    <th>Last Degree</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($data as $row){
            $person = Person::newFromId($row['user_id']);
            if($person->getId() != 0){
                $fec = $person->getFecPersonalInfo();
                $goingToFec = ($person->getCaseNumber() == "") ? "No" : "Yes";
                
                $sabbs = array();
                if(!empty($person->sabbatical)){
                    foreach($person->sabbatical as $sabbatical){
                        $end = date('Y-m-d', strtotime("+{$sabbatical['duration']} month -1 day", strtotime($sabbatical['start'])));
                        $sabbs[] = "{$sabbatical['start']} - {$end}";
                    }
                }
                
                $wgOut->addHTML("<tr>
                                     <td><a href='{$person->getUrl()}'>{$person->getNameForForms()}</a></td>
                                     <td>{$person->getDepartment()}</td>
                                     <td>{$person->getPosition()}</td>
                                     <td align='center'>{$goingToFec}</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfPhd)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfAppointment)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfAssistant)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfAssociate)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfProfessor)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateFso2)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateFso3)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateFso4)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateAtsec1)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateAtsec2)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateAtsec3)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfProbation1)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfProbation2)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfTenure)."</td>
                                     <td style='white-space:nowrap;'>".implode("<br />", $sabbs)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfRetirement)."</td>
                                     <td>".str_replace("00:00:00", "", $fec->dateOfLastDegree)."</td>
                                     <td>{$fec->lastDegree}</td>
                                </tr>");
            }
        }
        $wgOut->addHTML("   </tbody>
                         </table>
                         <script type='text/javascript'>
                            $('#fechistory').DataTable({
                                'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'All']],
                                'autoWidth': false,
                                'iDisplayLength': -1,
                                'dom': 'Blfrtip',
                                'buttons': [
                                    'excel'
                                ]
                            });
                         </script>");
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(DEAN) || 
                $me->isRole(DEANEA) || 
                $me->isRole(VDEAN) || 
                $me->isRoleAtLeast(STAFF));
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if(self::userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "FECHistory")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("FEC History", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:FECHistory", 
                                                                   "$selected");
        }
    }
    
}

?>

<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['IndexComponents'] = 'IndexComponents'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['IndexComponents'] = $dir . 'IndexComponents.i18n.php';
$wgSpecialPageGroups['IndexComponents'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'IndexComponents::createSubTabs';

function runIndexComponents($par) {
    IndexComponents::execute($par);
}

class IndexComponents extends SpecialPage {
    
    function __construct() {
        global $config;
        SpecialPage::__construct("IndexComponents", null, true, 'runIndexComponents');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    static function getRow($person){
        global $wgServer, $wgScriptPath, $config, $EQ5D5L;
        $me = Person::newFromWgUser();

        $api1 = new UserFrailtyIndexAPI();
        $api2 = new UserInPersonFrailtyIndexAPI();
        $scores1 = $api1->getFrailtyScore($person->getId(), "RP_AVOID");
        $scores2 = $api2->getFrailtyScore($person->getId());
        if($scores1['Total'] == 0 || $scores2['Total'] == 0){
            return "";
        }
        $html = "";
        $html .= "<tr data-id='{$person->getId()}'>
                    <td>{$person->getId()}</td>";
        foreach(UserFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $html .= "<td>".((isset($scores1["$key#$bId"])) ? $scores1["$key#$bId"] : 0)."</td>";
            }
        }
        foreach(UserInPersonFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $html .= "<td>".((isset($scores2["$key#$bId"])) ? $scores2["$key#$bId"] : 0)."</td>";
            }
        }

        return $html;
    }

    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = Person::newFromWgUser();
        $people = Person::getAllPeople(CI);
        
        $fiColspan = 0;
        foreach(UserFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $fiColspan++;
            }
        }
        
        $ipColspan = 0;
        foreach(UserInPersonFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $ipColspan++;
            }
        }
        
        $wgOut->addHTML("<table id='summary' class='wikitable'>
                            <thead>
                                <tr>
                                    <th rowspan='3'>User Id</th>
                                    <th colspan='{$fiColspan}'>Frailty Index</th>
                                    <th colspan='{$ipColspan}'>In Person</th>
                                </tr>
                                <tr>");
        foreach(UserFrailtyIndexAPI::$checkanswers as $key => $questions){
            $wgOut->addHTML("       <th colspan='".count($questions)."'>$key</th>");
        }
        foreach(UserInPersonFrailtyIndexAPI::$checkanswers as $key => $questions){
            $wgOut->addHTML("       <th colspan='".count($questions)."'>$key</th>");
        }
        $wgOut->addHTML("
                                </tr>
                                <tr>");
        foreach(UserFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $wgOut->addHTML("   <th>".IntakeSummary::$map[$bId]."</th>");
            }
        }
        foreach(UserInPersonFrailtyIndexAPI::$checkanswers as $key => $questions){
            foreach($questions as $bId => $question){
                $wgOut->addHTML("   <th>".IntakeSummary::$map[$bId]."</th>");
            }
        }
        $wgOut->addHTML("       </tr>
                            </thead>");
        $wgOut->addHTML("<tbody>");
        
        foreach($people as $person){
            if(!$person->isRoleAtMost(CI)){
                continue;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID") && IntakeSummary::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID") != "CFN"){
                $wgOut->addHTML(self::getRow($person));
            }
        }
        $wgOut->addHTML("</tbody>
                        </table>");
        $wgOut->addHTML("
        <style>
            .downloadExcel {
                float: left;
            }
        </style>                
        <script type='text/javascript'>
            var tableHTML = '<table>' + 
                                $('#summary thead')[0].innerHTML.replaceAll('\\n', '').replaceAll('#', '') + 
                                $('#summary tbody')[0].innerHTML.replaceAll('\\n', '').replaceAll('#', '') +
                            '</table>';
        
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'scrollX': true,
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [ ],
                scrollX: true,
                scrollY: $('#bodyContent').height() - 400
            });
            
            $('.dt-buttons').append(\"<button id='downloadExcel' class='downloadExcel'>Excel</button>\");
            
            $('.dt-buttons').click(function(){
                window.open('data:application/vnd.ms-excel,' + tableHTML);
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "IndexComponents") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Index Components", "{$wgServer}{$wgScriptPath}/index.php/Special:IndexComponents", $selected);
        }
        return true;
    }
    
}

?>

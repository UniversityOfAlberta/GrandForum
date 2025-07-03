<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReviewerAssignments'] = 'ReviewerAssignments'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReviewerAssignments'] = $dir . 'ReviewerAssignments.i18n.php';
$wgSpecialPageGroups['ReviewerAssignments'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'ReviewerAssignments::createSubTabs';

function runReviewerAssignments($par) {
    ReviewerAssignments::execute($par);
}

class ReviewerAssignments extends SpecialPage {
    
    function __construct(){
        SpecialPage::__construct("ReviewerAssignments", null, false, 'runReviewerAssignments');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }
    
    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        
        $data = DBFunctions::select(array('grand_eval'),
                                    array('*'),
                                    array('type' => NEQ('Project')));
        
        $wgOut->addHTML("<table id='reviewerAssignments' class='wikitable'>
                            <thead>
                                <tr>
                                    <th>Reviewer</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($data as $row){
            $reviewer = Person::newFromId($row['user_id']);
            $user = Person::newFromId($row['sub_id']);
            if($reviewer != null && $user != null && $reviewer->getId() != 0 && $user->getId() != 0){
                $wgOut->addHTML("<tr>
                                    <td>{$reviewer->getNameForForms()}</td>
                                    <td>{$user->getNameForForms()}</td>
                                    <td>{$row['type']}</td>
                                    <td>{$row['year']}</td>
                                 </tr>");
            }
        }
        $wgOut->addHTML("   </tbody>
                        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#reviewerAssignments').DataTable({
                'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': -1,
                'order': [[ 3, 'desc' ], [ 2, 'asc' ]]
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "ReviewerAssignments") ? "selected" : false;
            array_splice($tabs["Manager"]['subtabs'], 0, 0, array(TabUtils::createSubTab("Reviewer Assignments", "$wgServer$wgScriptPath/index.php/Special:ReviewerAssignments", $selected)));
        }
        return true;
    }
    
}

?>

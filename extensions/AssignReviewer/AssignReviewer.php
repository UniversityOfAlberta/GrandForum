<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AssignReviewer'] = 'AssignReviewer'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AssignReviewer'] = $dir . 'AssignReviewer.i18n.php';
$wgSpecialPageGroups['AssignReviewer'] = 'network-tools';

function runAssignReviewer($par) {
    AssignReviewer::execute($par);
}

class AssignReviewer extends SpecialPage{

    function AssignReviewer() {
        SpecialPage::__construct("AssignReviewer", null, false, 'runAssignReviewer');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(MANAGER);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(!isset($_POST['submit'])){
            AssignReviewer::generateFormHTML($wgOut);
        }
        else{
            AssignReviewer::handleSubmit($wgOut);
            return;
        }
    }
    
    function createForm(){
        $formContainer = new FormContainer("form_container");
        
        $formTable = new FormTable("form_table");
        
        $submitCell = new EmptyElement();
        $submitField = new SubmitButton("submit", "Submit Request", "Submit Request", VALIDATE_NOTHING);
        $submitRow = new FormTableRow("submit_row");
        $submitRow->append($submitCell)->append($submitField);
        
        $formTable->append($submitRow);
        
        $formContainer->append($formTable);
        return $formContainer;
    }
    
     function generateFormHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $user = Person::newFromId($wgUser->getId());
        $student_id = $_GET['student'];
        $student_text = "?student=$student_id";
        $wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AssignReviewer$student_text' method='post'>\n");
        $wgOut->addHTML('
<div id="productAuthors">

</div>');
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
        $personNames = array();
        $student_id = $_GET['student'];
        $wgOut->addHTML("<input type='hidden' name='student_id' value='$student_id'>");
        $student = Person::newFromId($student_id);
        $evaluators = $student->getEvaluators(YEAR,'sop');
        foreach($evaluators as $evaluator){
            $personNames[] = $evaluator->getNameForForms();
        }
      
        $list = array();
        $allPeople = Person::getAllPeople('all');
        foreach($allPeople as $person){
            if($person->isRole(EVALUATOR) && array_search($person->getNameForForms(), $personNames) === false){
                $list[] = $person->getNameForForms();
            }
        }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='Person' id='people'>
                            <div class='left'><span>".implode("</span>\n<span>", $personNames)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");


        $form = self::createForm();
        $wgOut->addHTML($form->render());
        $wgOut->addHTML("</form>");                        
        $wgOut->addHTML("<script type='text/javascript'>
            $(document).ready(function(){
                $('#rightpeoples').height('300px');
                $('#leftpeoples').height('300px');
            });
        </script>");
    }
    
    function handleSubmit($wgOut){
        global $wgServer, $wgScriptPath, $wgMessage, $wgGroupPermissions, $wgUser;
        $form = self::createForm();
        $me = Person::newFromId($wgUser->getId());
        $status = $form->validate();
        if($status){
            $_POST['users'] = @$_POST['people'];
            $_POST['student_id'] = @$_POST['student_id']; 
                $result = APIRequest::doAction('AssignReviewers', false);
                if($result){
                    $form->reset();
                    $wgMessage->addSuccess("Reviewer Assigned.");
                    redirect("$wgServer$wgScriptPath/index.php/Special:Sops");
                }
        }
        AssignReviewer::generateFormHTML($wgOut);
    }

}

?>

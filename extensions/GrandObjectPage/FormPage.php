<?php
$formPage = new FormPage();

$wgHooks['ArticleViewHeader'][] = array($formPage, 'processPage');

class FormPage {

    function processPage($article, $outputDone, $pcache){
        global $wgTitle, $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgFileExtensions, $wgMessage;
        $me = Person::newFromWgUser();
        if($wgTitle->getNSText() == "Form" && $me->isRoleAtLeast(MANAGER)){
            $id = $wgTitle->getText();
            $form = Form::newFromId($id);
            $name = $wgTitle->getNSText();
            $create = (isset($_GET['create']) && $me->isRoleAtLeast(HQP));
            $edit = ((isset($_GET['edit']) || $create) && $me->isRoleAtLeast(HQP));
            $post = ((isset($_POST['submit']) && ($_POST['submit'] == "Save Form" || $_POST['submit'] == "Create Form")) && $me->isRoleAtLeast(HQP));
            if($me->isRoleAtLeast(HQP) && (($form->getId() != null) || $create)){
                if($post){
                    // Handle POST request
                    if(!$create){
                        $_POST['id'] = $form->getId();
                    }
                    if($_POST['firstName'] != "" || $_POST['lastName'] != ""){
                        $_POST['users'][] = $_POST['firstName'].".".$_POST['lastName'];
                    }
                    $_POST['title'] = str_replace("'", "&#39;", $_POST['title']);
                    $_POST['media'] = str_replace("'", "&#39;", $_POST['media']);
                    $_POST['url'] = "";
                    $_POST['description'] = "";
                    $_POST['date'] = str_replace("'", "&#39;", $_POST['date']);
                    $_POST['type'] = "form";
                    $api = new AddMaterialAPI();
                    $api->doAction(true);
                    $errors = $api->errors;
                    $form = Form::newFromTitle($_POST['title']);
                    $wgOut->redirect('');
                    if($errors == ""){
                        redirect("$wgServer$wgScriptPath/index.php/{$name}:{$form->getId()}");
                    }
                    else{
                        $wgOut->clearHTML();
                        $wgMessage->addError("$errors");
                    }
                }
            }
            else{
                $wgOut->clearHTML();
            }
            
            if(!$create && !$edit && ($form == null || $form->getTitle() == "")){
                // Material does not exist
                $wgOut->clearHTML();
                $wgOut->setPageTitle("Material Does Not Exist");
                $wgOut->addHTML("There is no Form with the id '{$wgTitle->getText()}'");
                $wgOut->output();
                $wgOut->disable();
            }
            if(!$create){
                $wgOut->setPageTitle(str_replace("&#39;", "'", $form->getTitle()));
            }
            else{
                $title = $wgTitle->getText();
                $wgOut->setPageTitle($title);
            }
            if($edit){
                $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
	        }
            if($create){
                $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/{$name}:".str_replace("?", "%3F", str_replace("'", "&#39;", $title))."?create' method='post'>
                                <b>Title:</b> <input size='35' type='text' name='title' value='".str_replace("'", "&#39;", $title)."' />");
            }
            else if($edit){
                $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/{$name}:{$form->getId()}?edit' method='post'>
                                    <b>Title:</b> <input size='35' type='text' name='title' value='{$form->getTitle()}' />");
            }
            
            $firstName = $form->getFirstName();
            $lastName = $form->getLastName();
            $wgOut->addWikiText("== Person ==
                                 __NOEDITSECTION__\n");
            if($edit){
                $wgOut->addHTML("<table>
                    <tr><td align='right'>First Name:</td><td align='left'><input type='text' name='firstName' value='{$firstName}' /></td></tr>
                    <tr><td align='right'>Last Name:</td><td align='left'><input type='text' name='lastName' value='{$lastName}' /></td></tr>
                </table>");
            }
            else{
                if($lastName != "" && $firstName != ""){
                    $person = Person::newFromNameLike($firstName.".".$lastName);
                    $wgOut->addHTML("<a href='{$person->getUrl()}'>{$lastName}, {$firstName}</a>");
                }
            }
            if($form->getUniversity() != ""){
                $wgOut->addWikiText("== University ==
                                         __NOEDITSECTION__\n");
                $wgOut->addHTML("{$form->getUniversity()}");
            }
            if($edit || (!$edit && $form->getMedia() != "")){
                $wgOut->addWikiText("== Media ==
                                        __NOEDITSECTION__\n");
            }
            if($edit){
                $wgOut->addHTML("Enter in the URL of the media source.  <br />Permitted file types: ".implode(", ", $wgFileExtensions).", as well as youtube, and vimeo videos.<br />");
                $wgOut->addHTML("<input type='text' name='media' size='50' value='{$form->getMedia()}' />");
            }
            else{
                $wgOut->addHTML($form->getMediaLink());
            }
            
            $wgOut->addWikiText("== Date ==
                                 __NOEDITSECTION__\n");
            if($edit){
                $date = $form->getDate();
                if($form->getDate() == "0000-00-00" || $form->getDate() == ""){
                    $date = date("Y-m-d");
                }
                $wgOut->addHTML("<input name='date' type='text' value='{$date}' />");
                $wgOut->addHTML("<script type='text/javascript'>
                    $('input[name=date]').datepicker();
                    $('input[name=date]').datepicker('option', 'dateFormat', 'yy-mm-dd');
                    $('input[name=date]').keydown(function(){
                        return false;
                    });
                    $('input[name=date]').attr('value', '$date');
                </script>");
            }
            else{
                $wgOut->addHTML("{$form->getDate()}");
            }
            
            if($edit || !$edit && count($form->getProjects()) > 0){
                $wgOut->addWikiText("== Project ==
                                     __NOEDITSECTION__\n");
            }
            $p = $form->getProject();
            $projectName = ($p != null && $p->getName() != "") ? $p->getName() : "";
            if($edit){
                $projs = Project::getAllProjects();
                        
                $projList = new ProjectList("projects", "Projects", $pProjects, $projs);
                $wgOut->addHTML($projList->render());
                if(count($projs) > 0){
                    foreach($projs as $project){
                        // Add any deleted projects so that they remain as part of this project
                        if($project->deleted){
                            $wgOut->addHTML("<input style='display:none;' type='checkbox' name='projects[]' value='{$project->getName()}' checked='checked' />");
                        }
                    }
                }
            }
            else{
                $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/{$projectName}:Main'>{$projectName}</a>");
            }
            $wgOut->addHTML("<br />");
            if($wgUser->isLoggedIn()){
                if($create){
                    $wgOut->addHTML("<input type='submit' name='submit' value='Create Form' />");
                    $wgOut->addHTML("</form>");
                }
                else if($edit){
                    $wgOut->addHTML("<input type='submit' name='submit' value='Save Form' />");
                    $wgOut->addHTML("</form>");
                }
                else {
                    $wgOut->addHTML("<input type='button' name='edit' value='Edit Form' onClick='document.location=\"$wgServer$wgScriptPath/index.php/{$name}:{$form->getId()}?edit\";' />");
                }
            }
            $wgOut->output();
            $wgOut->disable();
            return true;
        }
        return true;
    }
}
?>

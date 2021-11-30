<?php
$wgHooks['ArticleViewHeader'][] = 'MaterialPage::processPage';

class MaterialPage {

    static function processPage($article, $outputDone, $pcache){
        global $wgTitle, $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgFileExtensions, $wgMessage;
        $me = Person::newFromId($wgUser->getId());
        if($wgTitle->getNSText() == "Multimedia"){
            $id = $wgTitle->getText();
            $material = Material::newFromId($id);
            $name = $wgTitle->getNSText();
            $create = (isset($_GET['create']) && $me->isRoleAtLeast(HQP));
            $edit = ((isset($_GET['edit']) || $create) && $me->isRoleAtLeast(HQP));
            $post = ((isset($_POST['submit']) && ($_POST['submit'] == "Save Material" || $_POST['submit'] == "Create Material")) && $me->isRoleAtLeast(HQP));
            if($me->isRoleAtLeast(HQP) && (($material->getId() != null) || $create)){
                TabUtils::clearActions();
                if($post){
                    // Handle POST request
                    if(!$create){
                        $_POST['id'] = $material->getId();
                    }
                    $_POST['title'] = str_replace("'", "&#39;", $_POST['title']);
                    $_POST['media'] = str_replace("'", "&#39;", $_POST['media']);
                    $_POST['url'] = str_replace("'", "&#39;", $_POST['url']);
                    $_POST['date'] = str_replace("'", "&#39;", $_POST['date']);
                    $_POST['users'] = @$_POST['people'];
                    if(!isset($_POST['keywords'])){
                        $_POST['keywords'] = array();
                    }
                    foreach($_POST['keywords'] as $key => $keyword){
                        $_POST['keywords'][$key] = str_replace("'", "&#39;", $keyword);
                    }
                    $api = new AddMaterialAPI();
                    $api->doAction(true);
                    $errors = $api->errors;
                    $material = Material::newFromTitle($_POST['title']);
                    $wgOut->redirect('');
                    if($errors == ""){
                        redirect("$wgServer$wgScriptPath/index.php/{$name}:{$material->getId()}");
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
            
            if(!$create && !$edit && ($material == null || $material->getTitle() == "")){
                // Material does not exist
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle("Material Does Not Exist");
                $wgOut->addHTML("There is no Material with the id '{$wgTitle->getText()}'");
                $wgOut->output();
                $wgOut->disable();
            }
            if(!$create){
                $wgOut->setPageTitle(str_replace("&#39;", "'", $material->getTitle()));
            }
            else{
                $title = $_GET['name'];
                $wgOut->setPageTitle($title);
            }
            if($edit){
                $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
	        }
            if($create){
                $wgOut->addHTML("<form name='material' action='$wgServer$wgScriptPath/index.php/{$name}:New?name=".urlencode($title)."&create' method='post'>
                                <b>Title:</b> <input size='35' type='text' name='title' value='".str_replace("'", "&#39;", $title)."' />");
            }
            else if($edit){
                $wgOut->addHTML("<form name='material' action='$wgServer$wgScriptPath/index.php/{$name}:{$material->getId()}?edit' method='post'>
                                    <b>Title:</b> <input size='35' type='text' name='title' value='{$material->getTitle()}' />");
            }
            
            $people = $material->getPeople();
            if($edit || !$edit && count($people) > 0){
                $wgOut->addWikiText("== People ==
                                     __NOEDITSECTION__\n");
                $i = 1;
                $nPeople = count($people);
                $personNames = array();
                if(!$create){
                    foreach($people as $person){
                        if($person instanceof Person){
                            $personNames[] = $person->getNameForForms();
                        }
                        else{
                            $personNames[] = $person;
                        }
                        $i++;
                    }
                }
                if($edit){
                    $allPeople = Person::getAllPeople('all');
                    foreach($allPeople as $person){
                        if(array_search($person->getNameForForms(), $personNames) === false &&
                           $person->getNameForForms() != "WikiSysop" &&
                           $person->isRoleAtLeast(HQP)){
                            $list[] = $person->getNameForForms();
                        }
                    }
                    $wgOut->addHTML("<div class='switcheroo noCustom' name='Person' id='people'>
                                        <div class='left'><span>".implode("</span>\n<span>", $personNames)."</span></div>
                                        <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                                    </div>");
                }
                else{
                    $texts = array();
                    foreach($people as $person){
                        if($person instanceof Person){
                            if($person->getRoles() != null){
                                $texts[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                            }
                            else{
                                $texts[] = $person->getNameForForms();
                            }
                        }
                        else{
                            $texts[] = $person;
                        }
                    }
                    $wgOut->addHTML(implode(", ", $texts));
                }
            }
            if($edit || (!$edit && $material->getMedia() != "")){
                $wgOut->addWikiText("== Media ==
                                        __NOEDITSECTION__\n");
            }
            if($edit){
                $wgOut->addHTML("Enter in the URL of the media source.  <br />Permitted file types: ".implode(", ", $wgFileExtensions).", as well as youtube, and vimeo videos.<br />");
                $wgOut->addHTML("<input type='text' name='media' size='50' value='{$material->getMedia()}' />");
            }
            else{
                $wgOut->addHTML($material->getMediaLink());
            }
            
            if($edit || !$edit && $material->getMaterialUrl() != ""){
                $wgOut->addWikiText("== URL ==
                                        __NOEDITSECTION__\n");
            }
            if($edit){
                $wgOut->addHTML("Enter in a relevant page to the media source if there is one:<br />");
                $wgOut->addHTML("<input type='text' name='url' size='50' value='{$material->getMaterialUrl()}' />");
            }
            else{
                $wgOut->addWikiText("{$material->getMaterialUrl()}");
            }
            
            if($edit || !$edit && $material->getDescription() != ""){
                $wgOut->addWikiText("== Description ==
                                        __NOEDITSECTION__\n");
            }
            if($edit){
                $wgOut->addHTML("<textarea style='height:175px; width:650px;' name='description'>{$material->getDescription()}</textarea>");
            }
            else{
                $wgOut->addWikiText($material->getDescription());
            }
            
            if($edit || !$edit && count($material->getKeywords()) > 0){
                $wgOut->addWikiText("== Keywords ==
                                        __NOEDITSECTION__\n");
            }
            if($edit){
                $wgOut->addHTML("<div id='keywords'>");
                foreach($material->getKeywords() as $keyword){
                    $wgOut->addHTML("<input type='text' name='keywords[]' value='{$keyword}' /><input type='button' class='removeKeyword' value='-' /><br />");
                }
                if(count($material->getKeywords()) == 0){
                    $wgOut->addHTML("<input type='text' name='keywords[]' value='' /><input type='button' class='removeKeyword' value='-' /><br />");
                }
                $wgOut->addHTML("</div>");
                $wgOut->addHTML("<input type='button' id='addKeyword' value='Add Keyword' />");
                $allKeywords = Material::getAllKeywords();
                $newAllKeywords = array();
                foreach($allKeywords as $keyword){
                    $newAllKeywords[] = str_replace("\"", "\\\"", str_replace("&#39;", "'", $keyword));
                }
                $wgOut->addScript("<script type='text/javascript'>
                    var keywords = [\"".implode("\",\"", $newAllKeywords)."\"];
                    $(document).ready(function(){
                        $('form[name=material]').submit(function(){
                            var title = $('form[name=material] input[name=title]').val();
                            if(title == ''){
                                clearError();
                                addError('The Multimedia must not have an empty title');
                                $('html, body').animate({ scrollTop: 0 });
                                return false;
                            }
                        });
                    
                        $('#addKeyword').click(function(){
                            $('#keywords').append(\"<input type='text' name='keywords[]' value='' /><input type='button' class='removeKeyword' value='-' /><br />\");
                            
                            $('.removeKeyword').click(function(){
                                $(this).prev('input').remove();
                                $(this).next('br').remove();
                                $(this).remove();
                            });
                            
                            $('#keywords input[type=text]').autocomplete({
                                source: keywords
                            });
                        });
                        
                        $('.removeKeyword').click(function(){
                            $(this).prev('input').remove();
                            $(this).next('br').remove();
                            $(this).remove();
                        });
                        
                        $('#keywords input[type=text]').autocomplete({
                            source: keywords
                        });
                    });
                </script>");
            }
            else{
                $wgOut->addHTML(implode(", ", $material->getKeywords()));
            }
            
            $wgOut->addWikiText("== Date ==
                                 __NOEDITSECTION__\n");
            if($edit){
                $date = $material->getDate();
                if($material->getDate() == "0000-00-00" || $material->getDate() == ""){
                    $date = date("Y-m-d");
                }
                $wgOut->addHTML("<input name='date' type='text' value='{$date}' />");
                $wgOut->addHTML("<script type='text/javascript'>
                    $('input[name=date]').datepicker();
                    $('input[name=date]').datepicker('option', 'dateFormat', 'yy-mm-dd');
                    $('input[name=date]').datepicker('setDate', '$date');
                    $('input[name=date]').keydown(function(){
                        return false;
                    });
                    $('input[name=date]').attr('value', '$date');
                </script>");
            }
            else{
                $wgOut->addHTML("{$material->getDate()}");
            }
            
            if($edit || !$edit && count($material->getProjects()) > 0){
                $wgOut->addWikiText("== Projects ==
                                     __NOEDITSECTION__\n");
            }
            $projects = $material->getProjects();
            $pProjects = array();
            if(!$create){
                foreach($projects as $project){
                    $pProjects[] = $project->getName();
                }
            }
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
                $projectList = array();
                foreach($projects as $project){
                    if(!$project->deleted){
                        $projectList[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
                    }
                }
                $wgOut->addHTML(implode(", ", $projectList));
            }
            $wgOut->addHTML("<br />");
            if($wgUser->isLoggedIn()){
                if($create){
                    $wgOut->addHTML("<input type='submit' name='submit' value='Create Material' />");
                    $wgOut->addHTML("</form>");
                }
                else if($edit){
                    $wgOut->addHTML("<input type='submit' name='submit' value='Save Material' />");
                    $wgOut->addHTML("</form>");
                }
                else {
                    $wgOut->addHTML("<input type='button' name='edit' value='Edit Material' onClick='document.location=\"$wgServer$wgScriptPath/index.php/{$name}:{$material->getId()}?edit\";' />");
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

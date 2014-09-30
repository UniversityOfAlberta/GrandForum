<?php

class ProjectMainTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectMainTab($project, $visibility){
        parent::AbstractTab("Main");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        if(!$project->isSubProject() && $wgUser->isLoggedIn() && MailingList::isSubscribed($project, $me)){
            $this->html .="<h3><a href='$wgServer$wgScriptPath/index.php/Mail:{$project->getName()}'>{$project->getName()} Mailing List</a></h3>";
        }
        $bigbet = ($this->project->isBigBet()) ? "Yes" : "No";
        $title = "";
        if($edit){
            if($project->isSubProject()){
                $acronymField = new TextField("acronym", "New Acronym", $this->project->getName());
                $title .= "<tr><td><b>New Acronym:</b></td><td>{$acronymField->render()}</td></tr>";
            }
            $fullNameField = new TextField("fullName", "New Title", $this->project->getFullName());
            $title .= "<tr><td><b>New Title:</b></td><td>{$fullNameField->render()}</td></tr>";
        }
        $this->html .= "<table>
                            $title";
        if($config->getValue("projectTypes")){
            $this->html .= "<tr><td><b>Type:</b></td><td>{$this->project->getType()}</td></tr>";
        }
        if($config->getValue("bigBetProjects") && !$this->project->isSubProject()){
            $this->html .= "<tr><td><b>Big-Bet:</b></td><td>{$bigbet}</td></tr>";
        }
        if($config->getValue("projectStatus")){
            $this->html .= "<tr><td><b>Status:</b></td><td>{$this->project->getStatus()}</td></tr>";
        }
        $this->html .= "</table>";
        $this->showChallenge();
        $this->showChampions();
        $this->showPeople();
        $this->showDescription();

        if(!$project->isSubProject()){
            $this->showProblem();
            $this->showSolution();
        }
        
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        $_POST['project'] = $this->project->getName();
        $_POST['fullName'] = @str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['fullName']));
        $_POST['description'] = @str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description']));
        $_POST['problem'] = @str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['problem']));
        $_POST['solution'] = @str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['solution']));
        if($_POST['description'] != $this->project->getDescription() ||
           $_POST['problem'] != $this->project->getProblem() ||
           $_POST['solution'] != $this->project->getSolution() ||
           $_POST['fullName'] != $this->project->getFullName()){
            APIRequest::doAction('ProjectDescription', true);
            Project::$cache = array();
            $this->project = Project::newFromId($this->project->getId());
            $wgOut->setPageTitle($this->project->getFullName()." (Phase ".$this->project->getPhase().")");
        }

        if(isset($_POST['challenge_id'])){
            APIRequest::doAction('ProjectChallenge', true);
        }
        
        // Deleting Champions
        if(isset($_POST['champ_del'])){
            foreach($_POST['champ_del'] as $key => $id){
                $champ = Person::newFromId($id);
                $_POST['role'] = $this->project->getName();
                $_POST['user'] = $champ->getName();
                $_POST['comment'] = "Automatic Removal";
                APIRequest::doAction('DeleteProjectMember', true);
            }
        }
        
        // Adding New Champions
        if(isset($_POST['champ_name'])){
            foreach($_POST['champ_name'] as $key => $name){
                if($name != ""){
                    $_POST['role'] = $this->project->getName();
                    $_POST['user'] = $name;
                    APIRequest::doAction('AddProjectMember', true);
                }
            }
        }
        
        if(isset($_POST['pl'])){
            $leaderName = ($this->project->getLeader() != null) ? $this->project->getLeader()->getName() : "";
            if($_POST['pl'] != $leaderName){
                $_POST['role'] = $this->project->getName();
                $_POST['user'] = $leaderName;
                $_POST['comment'] = "Automatic Removal";
                APIRequest::doAction('DeleteProjectLeader', true);
                
                $_POST['user'] = $_POST['pl'];
                $_POST['manager'] = 'False';
                $_POST['co_lead'] = 'False';
                APIRequest::doAction('AddProjectLeader', true);
            }
        }
        
        if(isset($_POST['acronym'])){
            $_POST['new_acronym'] = str_replace(" ", "-", $_POST['acronym']);
            $_POST['old_acronym'] = $this->project->getName();
            $result = APIRequest::doAction('UpdateProjectAcronym', true);
            if($result){
                $this->project->name = $_POST['new_acronym'];
                redirect($this->project->getUrl());
                exit;
            }
        }
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return $this->visibility['isLead'];
    }
    
    function showThemes(){
        global $wgServer, $wgScriptPath;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        $this->html .= "<h2><span class='mw-headline'>Theme Distribution</span></h2>";
        $themes = $this->project->getThemes();
        $i = 1;
        
        $this->html .= "<table><tr>";
        foreach($themes['values'] as $theme){
            if($i > 1){
                $this->html .= "<td><ul><li></li></ul></td>";
            }
            $this->html .= "<td align='right'>";
            if($edit){
                $this->html .= "{$themes['names'][$i]}";
            }
            else{
                $this->html .= "<a href='{$wgServer}{$wgScriptPath}/index.php/Grand:Theme{$i}_-_".IndexTable::getThemeFullName($i).
                               "'>" . $themes['names'][$i] . "</a>";
            }
            $this->html .= "</td><td>";
            
            if($edit){
                $this->html .= "<input id='t{$i}' onKeyUp='stripAlphaChars(this.id)' type='text' size='2' name='t$i' value='{$themes['values'][$i]}' /> %";
            }
            else{
                $this->html .= "{$themes['values'][$i]}%";
            }
            $this->html .= "</td>";
            
            $i++;
        }
        $this->html .= "</tr></table>";
    }

    function showChallenge(){
        global $wgServer, $wgScriptPath;
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        
        $this->html .= "<h2><span class='mw-headline'>Primary Challenge</span></h2>";
        $challenge = $this->project->getChallenge();
        
        $challenges = Theme::getAllThemes($this->project->getPhase());
        $chlg_opts = "<option value='0'>Not Specified</option>";
        foreach ($challenges as $chlg){
            $cid = $chlg->getId();
            $cname = $chlg->getAcronym();
            $selected = ($cname == $challenge->getAcronym())? "selected='selected'" : "";
            $chlg_opts .= "<option value='{$cid}' {$selected}>{$cname}</option>";
        }
        if($edit){
            $this->html .=<<<EOF
            <select name="challenge_id">{$chlg_opts}</select>
EOF;
        }
        else{
            $this->html .= "<h4>{$challenge->getAcronym()}</h4>";
        }   
    }
    
    function showChampions(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;

        $champions = array();
        $derivedChamps = array();
        foreach($project->getChampions() as $champ){
            $champions[$champ['user']->getId()] = $champ;
        }
        if(!$project->isSubProject()){
            foreach($project->getSubProjects() as $sub){
                foreach($sub->getChampions() as $champ){
                    if(!isset($derivedChamps[$champ['user']->getId()])){
                        $derivedChamps[$champ['user']->getId()] = $champ;
                    }
                    $derivedChamps[$champ['user']->getId()]['subs'][] = "<a href='{$sub->getUrl()}' target='_blank'>{$sub->getName()}</a>";
                    unset($champions[$champ['user']->getId()]);
                }
            }
        }

        $this->html .= "<h2><span class='mw-headline'>Champions</span></h2>";
        if($edit){
            $this->showEditChampions($champions);
        }
        else{
            if(!count($champions) == 0){
                foreach($champions as $champion){
                    $subs = "";
                    if(isset($champion['subs'])){
                        $subs = " (".implode(", ", $champion['subs']).")";
                    }
                    $this->html .= "
                    <h3><a href='{$champion['user']->getUrl()}'>{$champion['user']->getNameForForms()}</a>$subs</h3>
                    <table cellspacing='0' cellpadding='2' style='margin-left:15px;'>";
                    if($wgUser->isLoggedIn()){
                        $this->html .= "<tr><td><strong>Email:</strong></td><td>{$champion['user']->getEmail()}</td></tr>";
                    }
                    $this->html .= "<tr><td><strong>Title:</strong></td><td>{$champion['title']}</td></tr>
                        <tr><td><strong>Organization:</strong></td><td>{$champion['org']}</td></tr>
                    </table>";
                }
            }
        }
        
        if(!$project->isSubProject()){
            if(count($derivedChamps) > 0){
                $this->html .= "<p>The following are Champions of {$project->getName()}'s sub-projects</p><ul>";
                foreach($derivedChamps as $champion){
                    $this->html .= "<li><a href='{$champion['user']->getUrl()}'>{$champion['user']->getNameForForms()}</a> (".implode(", ", $champion['subs']).")</li>";
                }
                $this->html .= "</ul>";
            }
        }
        if(!$edit){
            if(count($champions) == 0 && count($derivedChamps) == 0){
                $this->html .= "<strong>N/A</strong>";
            }
        }
    }
    
    function showEditChampions($champions){
        $this->html .= "<table>";
        if(count($champions) > 0){
            $this->html .= "<tr><th></th><th>Delete?</th></tr>";
        }
        foreach($champions as $champ){
            $user = $champ['user'];
            $this->html .= "<tr>
                    <td style='padding-left:6px;'><b>{$user->getNameForForms()}</b></td>
                    <td align='center'><input type='checkbox' name='champ_del[]' value='{$user->getId()}' /></td>
                </tr>";
        }
        $names = array("");
        $people = Person::getAllPeople(CHAMP);
        foreach($people as $person){
            $names[$person->getName()] = $person->getNameForForms();
        }
        asort($names);

        $plusMinus = new PlusMinus("champ_plusminus");
        $table = new FormTable("champ_table");
        
        $combo = new ComboBox("champ_name[]", "Name", "", $names, VALIDATE_CHAMPION);
        
        $table->append($combo);
        $plusMinus->append($table);
        $this->html .= "<tr><td>";
        $this->html .= $plusMinus->render();
        $this->html .= "</td><td></td></tr></table>";
    }

    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $me = Person::newFromWgUser();
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $leaders = $project->getLeaders(true); //only get id's
        $coleaders = $project->getCoLeaders(true);
        $managers = $project->getManagers(true);
        $pnis = $project->getAllPeople(PNI);
        $cnis = $project->getAllPeople(CNI);
        $ars = $project->getAllPeople(AR);
        $hqps = $project->getAllPeople(HQP);
        
        $names = array("");
        if($project->isSubProject()){
            $people = array_merge($project->getParent()->getAllPeople(), $project->getAllPeople());
            foreach($people as $person){
                if($person->isRoleAtLeast(CNI)){
                    $names[$person->getName()] = $person->getNameForForms();
                }
            }
            if($project->getLeader() != null && !isset($names[$project->getLeader()->getName()])){
                $names[$project->getLeader()->getName()] = $project->getLeader()->getNameForForms();
            }
            
            asort($names);
        }
        
        $this->html .= "<h2><span class='mw-headline'>Leaders</span></h2>";
        $this->html .= "<table>";
        if(!empty($leaders)){
            foreach($leaders as $leader_id){
                $leader = Person::newFromId($leader_id);
                $this->html .= "<tr>";
                
                if(!$edit || !$me->leadershipOf($project->getParent())){
                    $this->html .= "<td align='right'><b>Leader:</b></td><td><a href='{$leader->getUrl()}'>{$leader->getReversedName()}</a></td></tr>";
                }
                else if($me->leadershipOf($project->getParent())){
                    $plRow = new FormTableRow("pl_row");
                    $plRow->append(new Label("pl_label", "Project Leader", "The leader of this Project.  The person should be a valid person on this project.", VALIDATE_NOTHING));
                    $plRow->append(new ComboBox("pl", "Project Leader", $leader->getName(), $names, VALIDATE_NI));
                    $this->html .= $plRow->render();
                }
            }    
        }
        else if($edit && $me->leadershipOf($project->getParent())){
            $plRow = new FormTableRow("pl_row");
            $plRow->append(new Label("pl_label", "Project Leader", "The leader of this Project.  The person should be a valid person on this project.", VALIDATE_NOTHING));
            $plRow->append(new ComboBox("pl", "Project Leader", "", $names, VALIDATE_NI));
            $this->html .= $plRow->render();
        }
        if(!empty($coleaders)){
            foreach($coleaders as $leader_id){
                $leader = Person::newFromId($leader_id);
                $this->html .= "<tr><td align='right'><b>co-Leader:</b></td><td><a href='{$leader->getUrl()}'>{$leader->getReversedName()}</a></td></tr>";
            }    
        }
        if(!empty($managers)){
            foreach($managers as $leader_id){
                $leader = Person::newFromId($leader_id);
                $this->html .= "<tr><td align='right'><b>Manager:</b></td><td><a href='{$leader->getUrl()}'>{$leader->getReversedName()}</a></td></tr>";
            }    
        }
        $this->html .= "</table>";
        if(!$edit){
            $this->html .= "<table width='100%'><tr><td valign='top' width='50%'>";
            if($edit || !$edit && count($pnis) > 0){
                $this->html .= "<h2><span class='mw-headline'>PNIs</span></h2>";
            }
            $this->html .= "<ul>";
            foreach($pnis as $pni){
                if((!empty($leaders) && in_array($pni->getId(), $leaders)) || 
                   (!empty($coleaders) && in_array($pni->getId(), $coleaders)) ||
                   (!empty($managers) && in_array($pni->getId(), $managers))){
                    continue;
                }
                $target = "";
                if($edit){
                    $target = " target='_blank'";
                }
                $this->html .= "<li><a href='{$pni->getUrl()}'$target>{$pni->getReversedName()}</a></li>";
            }
            
            $this->html .= "</ul>";
            if($edit || !$edit && count($cnis) > 0){
                $this->html .= "<h2><span class='mw-headline'>CNIs</span></h2>";
            }
            $this->html .= "<ul>";
            foreach($cnis as $cni){
                if((!empty($leaders) && in_array($cni->getId(), $leaders)) || 
                   (!empty($coleaders) && in_array($cni->getId(), $coleaders)) ||
                   (!empty($managers) && in_array($cni->getId(), $managers))){
                    continue;
                }
                $target = "";
                if($edit){
                    $target = " target='_blank'";
                }
                $this->html .= "<li><a href='{$cni->getUrl()}'$target>{$cni->getReversedName()}</a></li>";
            }
            $this->html .= "</ul>";
            if($edit || !$edit && count($ars) > 0){
                $this->html .= "<h2><span class='mw-headline'>Associated Researchers</span></h2>";
            }
            $this->html .= "<ul>";
            foreach($ars as $ar){
                if((!empty($leaders) && in_array($ar->getId(), $leaders)) || 
                   (!empty($coleaders) && in_array($ar->getId(), $coleaders)) ||
                   (!empty($managers) && in_array($ar->getId(), $managers))){
                    continue;
                }
                $target = "";
                if($edit){
                    $target = " target='_blank'";
                }
                $this->html .= "<li><a href='{$ar->getUrl()}'$target>{$ar->getReversedName()}</a></li>";
            }
            $this->html .= "</ul></td>";
            if($wgUser->isLoggedIn()){
                $this->html .= "<td width='50%' valign='top'>";
                if($edit || !$edit && count($hqps) > 0){
                    $this->html .= "<h2><span class='mw-headline'>HQP</span></h2>";
                }
                $this->html .= "<ul>";
                foreach($hqps as $hqp){
                    $target = ""; 
                    if($edit){
                        $target = " target='_blank'";
                    }
                    $this->html .= "<li><a href='{$hqp->getUrl()}'$target>{$hqp->getReversedName()}</a></li>";
                }
                $this->html .= "</ul></td>";
            }
            $this->html .= "</tr></table>";
        }
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if($edit || !$edit && $project->getDescription() != ""){
            $this->html .= "<h2><span class='mw-headline'>Description</span></h2>";
        }
        if(!$edit){
            $this->html .= "<p>" . $this->sandboxParse($project->getDescription()) . "</p>";
        }
        else{
            $this->html .= "<textarea name='description' style='height:500px;'>{$project->getDescription()}</textarea>";
        }
    }

    function showProblem(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if($edit || !$edit && $project->getProblem() != ""){
            $this->html .= "<h2><span class='mw-headline'>Problem Summary</span></h2>";
        }
        if(!$edit){
            $this->html .= "<p>" . $this->sandboxParse($project->getProblem()) . "</p>";
        }
        else{
            $this->html .= "<textarea name='problem' style='height:500px;'>{$project->getProblem()}</textarea>";
        }
    }

    function showSolution(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if($edit || !$edit && $project->getSolution() != ""){
            $this->html .= "<h2><span class='mw-headline'>Proposed Solution Summary</span></h2>";
        }
        if(!$edit){
            $this->html .= "<p>" . $this->sandboxParse($project->getSolution()) . "</p>";
        }
        else{
            $this->html .= "<textarea name='solution' style='height:500px;'>{$project->getSolution()}</textarea>";
        }
    }

    function sandboxParse($wikiText) {
        global $wgTitle, $wgUser;
        $myParser = new Parser();
        $myParserOptions = ParserOptions::newFromUser($wgUser);
        $result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
        return $result->getText();
    }

}    
    
?>

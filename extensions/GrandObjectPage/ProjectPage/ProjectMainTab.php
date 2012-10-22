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
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        $edit = $this->visibility['edit'];
        
        if($wgUser->isLoggedIn() && $me->isMemberOf($project)){
            $this->html .="<h3><a href='$wgServer$wgScriptPath/index.php/{$project->getName()}:Mail_Index'>{$project->getName()} Mailing List</a></h3>";
        }
        
        $this->showThemes();
        $this->showPeople();
        $this->showDescription();
        
        return $this->html;
    }
    
    function handleEdit(){
        $_POST['project'] = $this->project->getName();
        $_POST['description'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description'])));
        $_POST['themes'] = $_POST['t1'].",".$_POST['t2'].",".$_POST['t3'].",".$_POST['t4'].",".$_POST['t5'];
        if(stripslashes($_POST['description']) != $this->project->getDescription() ||
           stripslashes($_POST['t1']) != $this->project->getTheme(1) ||
           stripslashes($_POST['t2']) != $this->project->getTheme(2) ||
           stripslashes($_POST['t3']) != $this->project->getTheme(3) ||
           stripslashes($_POST['t4']) != $this->project->getTheme(4) ||
           stripslashes($_POST['t5']) != $this->project->getTheme(5)){
            APIRequest::doAction('ProjectDescription', true);
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
        $edit = $this->visibility['edit'];
        
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
    
    function showPeople(){
        global $wgUser, $wgServer, $wgScriptPath;
        
        $edit = $this->visibility['edit'];
        $project = $this->project;
        
        $leaders = $project->getLeaders(true); //only get id's
        $coleaders = $project->getCoLeaders(true);
        $pnis = $project->getAllPeople(PNI);
        $cnis = $project->getAllPeople(CNI);
        $hqps = $project->getAllPeople(HQP);
        
        if(!$edit){
            $this->html .= "<h2><span class='mw-headline'>Project Leaders</span></h2>";
            $this->html .= "<table>";
            if(!empty($leaders)){
                foreach($leaders as $leader_id){
                    $leader = Person::newFromId($leader_id);
                    $leaderRoles = $leader->getRoles();
                    $this->html .= "<tr>";
                    $leaderType = "Leader";
                    if($leader->managementOf($project->getName())){
                        $leaderType = "Manager";
                    }
                    if(count($leaderRoles) > 0){
                        $this->html .= "<td align='right'><b>{$leaderType}:</b></td><td><a href='$wgServer$wgScriptPath/index.php/{$leaderRoles[0]->getRole()}:{$leader->getName()}'>{$leader->getReversedName()}</a></td></tr>";
                    }
                    else{
                        $this->html .= "<td align='right'><b>{$leaderType}:</b></td><td>{$leader->getReversedName()}</td></td></tr>";
                    }
                }    
            }
            if(!empty($coleaders)){
                foreach($coleaders as $leader_id){
                    $leader = Person::newFromId($leader_id);
                    $leaderRoles = $leader->getRoles();
                    $this->html .= "<tr>";
                    $leaderType = "Co-Leader";
                    if($leader->managementOf($project->getName())){
                        $leaderType = "Manager";
                    }
                    if(count($leaderRoles) > 0){
                        $this->html .= "<td align='right'><b>{$leaderType}:</b></td><td><a href='$wgServer$wgScriptPath/index.php/{$leaderRoles[0]->getRole()}:{$leader->getName()}'>{$leader->getReversedName()}</a></td></tr>";
                    }
                    else{
                        $this->html .= "<td align='right'><b>{$leaderType}:</b></td><td>{$leader->getReversedName()}</td></td></tr>";
                    }
                }    
            }
            $this->html .= "</table>";
        }
        
        $this->html .= "<table width='100%'><tr><td valign='top' width='50%'>";
        if($edit || !$edit && count($pnis) > 0){
            $this->html .= "<h2><span class='mw-headline'>PNIs</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($pnis as $pni){
            if((!empty($leaders) && in_array($pni->getId(), $leaders)) || (!empty($coleaders) && in_array($pni->getId(), $coleaders))){
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
            if((!empty($leaders) && in_array($cni->getId(), $leaders)) || (!empty($leaders) && in_array($cni->getId(), $leaders))){
                continue;
            }
            $target = "";
            if($edit){
                $target = " target='_blank'";
            }
            $this->html .= "<li><a href='{$cni->getUrl()}'$target>{$cni->getReversedName()}</a></li>";
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
    
    function showDescription(){
        global $wgServer, $wgScriptPath;
        
        $edit = $this->visibility['edit'];
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

    function sandboxParse($wikiText) {
        global $wgTitle, $wgUser;
        $myParser = new Parser();
        $myParserOptions = ParserOptions::newFromUser($wgUser);
        $result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
        return $result->getText();
    }

}    
    
?>

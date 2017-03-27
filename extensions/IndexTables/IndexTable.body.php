<?php

require_once("InactiveUsers.php");

$indexTable = new IndexTable();

$wgHooks['OutputPageParserOutput'][] = array($indexTable, 'generateTable');
$wgHooks['userCan'][] = array($indexTable, 'userCanExecute');

$wgHooks['SubLevelTabs'][] = 'IndexTable::createSubTabs';

class IndexTable {

	var $text = "";
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $config, $wgTitle, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        if($config->getValue('projectsEnabled')){
            $project = Project::newFromHistoricName($wgTitle->getNSText());
            $selected = ((($project != null && $project->getType() != "Administrative") || $wgTitle->getText() == "Projects") && 
                         !($me->isMemberOf($project) || ($project != null && $me->isMemberOf($project->getParent())))) ? "selected" : "";
            $projectTab = TabUtils::createSubTab("Projects", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", "$selected");
            if(Project::areThereDeletedProjects()){
                $projectTab['dropdown'][] = TabUtils::createSubTab("Current", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", $selected);
                $projectTab['dropdown'][] = TabUtils::createSubTab("Completed", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:CompletedProjects", $selected);
            }
            $tabs['Main']['subtabs'][] = $projectTab;
        }
        
        $lastRole = "";
        if($wgTitle->getNSText() == INACTIVE && !($me->isRole(INACTIVE) && $wgTitle->getText() == $me->getName())){
            $person = Person::newFromName($wgTitle->getText());
            if($person != null & $person->getName() != null && $person->isRole(INACTIVE)){
                $roles = $person->getRoles(true);
                $lastRole = "";
                for($i = count($roles) - 1; $i >= 0; $i--){
                    $role = $roles[$i];
                    if($role->getRole() != INACTIVE){
                        $lastRole = $role->getRole();
                        break;
                    }
                }
            }
        }
        $roles = array_values($wgAllRoles);
        if(count($roles) == 1){
              $role = $roles[0];
              if(($role != HQP || $me->isLoggedIn()) && count(Person::getAllPeople($role, true))){
                    $selected = ($lastRole == NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role &&
                    !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                    $peopleSubTab = TabUtils::createSubTab('People', "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
              }
        }
        elseif(count($roles)>1){
            sort($roles);
            $peopleSubTab = TabUtils::createSubTab('People', "", "");
            foreach($roles as $role){
                if(($role != HQP || $me->isLoggedIn()) && count(Person::getAllPeople($role, true))){
                    $selected = ($lastRole == NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role &&
                    !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                    $peopleSubTab['dropdown'][] = TabUtils::createSubTab($role, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
                }
            }
        }
            $tabs['Main']['subtabs'][] = $peopleSubTab;
        $selected = ($wgTitle->getText() == "Products" || 
                     $wgTitle->getText() == "Multimedia" ||
                     $wgTitle->getNsText() == "Multimedia") ? "selected" : "";
        $productsSubTab = TabUtils::createSubTab(Inflect::pluralize($config->getValue("productsTerm")));
        $structure = Product::structure();
        $categories = array_keys($structure['categories']);
        foreach($categories as $category){
            if(Product::countByCategory($category) > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab(Inflect::pluralize($category), "$wgServer$wgScriptPath/index.php/Special:Products#/{$category}", "$selected");
            }
        }
        $selected = ($wgTitle->getText() == "ALL Grants" && str_replace('_',' ',$wgTitle->getNSText()) == $config->getValue('networkName')) ? "selected" : "";
        $grantSubTab = TabUtils::createSubTab("Grants", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Grants", "$selected");
        if($wgUser->isLoggedIn()){
            //$tabs['Main']['subtabs'][] = $grantSubTab;
        }
        $selected = ($wgTitle->getText() == "ALL Courses" && str_replace('_',' ',$wgTitle->getNSText()) == $config->getValue('networkName')) ? "selected" : "";
        $grantSubTab = TabUtils::createSubTab("Courses", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Courses", "$selected");
        if($wgUser->isLoggedIn()){
            //$tabs['Main']['subtabs'][] = $grantSubTab;
        }
	    if(Material::countByCategory() > 0){
            $productsSubTab['dropdown'][] = TabUtils::createSubTab("Multimedia", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Multimedia", "$selected");
        }
        if($wgUser->isLoggedIn()){
            //$tabs['Main']['subtabs'][] = $productsSubTab;
        }
        $themesColl = new Collection(Theme::getAllThemes());
        $themeAcronyms = $themesColl->pluck('getAcronym()');
        $themeNames = $themesColl->pluck('getName()');
        $themes = array();
        foreach($themeAcronyms as $id => $acronym){
            $themes[] = $themeAcronyms[$id].' - '.$themeNames[$id];
        }
        
        if(Project::areThereAdminProjects()){
            $project = Project::newFromHistoricName($wgTitle->getNSText());
            $selected = ((($project != null && $project->getType() == 'Administrative') || $wgTitle->getText() == "AdminProjects")) ? "selected" : "";
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab(Inflect::pluralize($config->getValue('adminProjects')), 
                                                                "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:AdminProjects", 
                                                                "$selected");
        }
        
        if(count($themes) > 0){
            $selected = ($wgTitle->getNSText() == $config->getValue('networkName') && 
                         ($wgTitle->getText() == Inflect::pluralize($config->getValue('projectThemes')) || 
                         array_search($wgTitle->getText(), $themes) !== false)) ? "selected" : "";
            
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab(Inflect::pluralize($config->getValue('projectThemes')), 
                                                                "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:".Inflect::pluralize($config->getValue('projectThemes')), 
                                                                "$selected");
        }
        
        /*if(Wiki::newFromTitle("{$config->getValue('networkName')}_Conferences")->exists()){
            $selected = ($wgTitle->getNSText() == "Conference" || $wgTitle->getText() == "{$config->getValue('networkName')} Conferences") ? "selected" : "";
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab("Conferences", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}_Conferences", "$selected");
        }*/
        return true;
    }
	
	function userCanExecute(&$title, &$user, $action, &$result){
	    global $wgOut, $wgServer, $wgScriptPath, $config;
	    if($title->getNSText() == "{$config->getValue('networkName')}"){
	        $me = Person::newFromUser($user);
	        $text = $title->getText();
	        switch ($title->getText()) {
	            case 'ALL '.HQP:
				case 'Multimedia':
				    $result = $me->isLoggedIn();
	                break;
				case 'Forms':
				    $result = $me->isRoleAtLeast(MANAGER);
				    break;
	        }
	    }
	    return true;
	}

	function generateTable($out, $parseroutput){
		global $wgTitle, $wgOut, $wgUser, $config, $wgRoles, $wgAllRoles;
		$me = Person::newFromWgUser();
		if($wgTitle != null && str_replace("_", " ", $wgTitle->getNsText()) == "{$config->getValue('networkName')}" && !$wgOut->isDisabled()){
		    $result = true;
		    $this->userCanExecute($wgTitle, $wgUser, "read", $result);
		    if(!$result){
	            $wgOut->loginToUse();
		        $wgOut->output();
		        $wgOut->disable();
			    return true;
	        }
		    $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('.indexTable').css('display', 'table');
                    $('.dataTables_filter').css('float', 'none');
                    $('.dataTables_filter').css('text-align', 'left');
                    $('.dataTables_filter input').css('width', 250);
                });
            </script>");
			switch ($wgTitle->getText()) {
				case 'Multimedia':
				    $wgOut->setPageTitle("Multimedia");
				    $this->generateMaterialsTable();
				    break;
				case 'Forms':
				    if($me->isRoleAtLeast(MANAGER)){
				        $wgOut->setPageTitle("Forms");
				        $this->generateFormsTable();
				    }
				    break;
			    case 'Projects':
			        $wgOut->setPageTitle("Current Projects");
				    $this->generateProjectsTable('Active', 'Research');
				    break;
				case 'CompletedProjects':
			        $wgOut->setPageTitle("Completed Projects");
				    $this->generateProjectsTable('Ended', 'Research');
				    break;
				case 'AdminProjects':
			        $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
				    $this->generateAdminTable();
				    break;
			    case Inflect::pluralize($config->getValue('projectThemes')):
			        $wgOut->setPageTitle(Inflect::pluralize($config->getValue('projectThemes')));
				    $this->generateThemesTable();
				    break;
                            case 'ALL Grants':
                                $wgOut->setPageTitle("Grants");
                                $this->generateGrantsTable();
                                break;
                            case 'ALL Courses':
                                $wgOut->setPageTitle("Courses");
                                $this->generateCoursesTable();
			    default:
			        foreach($wgAllRoles as $role){
                        if(($role != HQP || $me->isLoggedIn()) && $wgTitle->getText() == "ALL {$role}"){
                            $wgOut->setPageTitle($config->getValue('roleDefs', $role));
				            $this->generatePersonTable($role);
                        }
                    }
				    break;
			}
			TabUtils::clearActions();
			$wgOut->addHTML($this->text);
			$wgOut->output();
			$wgOut->disable();
		}
		return true;
	}
	
	/**
	 * Generates the Table for the projects
	 * Consists of the following columns
	 * Acronym | Name 
	 */
	private function generateProjectsTable($status, $type="Research"){
		global $wgScriptPath, $wgServer, $wgOut, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $themesHeader = "";
        $idHeader = "";
        if($type != "Administrative"){
            $themesHeader = "<th>{$config->getValue('projectThemes')}</th>";
        }
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th>Project Id</th>";
        }
        $data = Project::getAllProjectsEver();
	    $this->text .= "
            <table class='indexTable' style='display:none;' frame='box' rules='all'>
            <thead>
            <tr><th>Acronym</th><th>Name</th>{$themesHeader}{$idHeader}</tr></thead><tbody>";
	    foreach($data as $proj){
	        if($proj->getStatus() == $status && ($proj->getType() == $type || $type == 'all')){
	            $this->text .= "
                    <tr>
                    <td align='left'><a href='{$proj->getUrl()}'>{$proj->getName()}</a></td>
                    <td align='left'>{$proj->getFullName()}</td>";
                if($type != "Administrative"){
                    $this->text .= "<td align='center'>{$proj->getChallenge()->getAcronym()}</td>";
                }
                if($idHeader){
                    $this->text .= "<td>{$proj->getId()}</td>\n";
                }
                $this->text .= "</tr>\n";
            }
	    }
	    $this->text .= "</tbody></table>";
		$this->text .= "</div><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";

		return true;
	}
	
	/**
	 * Generates the Table for the themes
	 * Consists of the following columns
	 * Theme | Name 
	 */
	private function generateThemesTable(){
		global $wgScriptPath, $wgServer, $config;
		$this->text .=
"<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>{$config->getValue('projectThemes')}</th><th>Name</th><th>Leaders</th><th>Coordinators</th></tr></thead><tbody>
";
        $themes = Theme::getAllThemes(PROJECT_PHASE);
		foreach($themes as $theme){
		    $leaders = array();
		    $coordinators = array();
		    $leads = $theme->getLeaders();
		    $coords = $theme->getCoordinators();
            foreach($leads as $lead){
                $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
            }
            foreach($coords as $coord){
                $coordinators[] = "<a href='{$coord->getUrl()}'>{$coord->getNameForForms()}</a>";
            }
		    $leadersString = implode(", ", $leaders);
		    $coordsString = implode(", ", $coordinators);
			$this->text .= <<<EOF
<tr>
<td align='left'>
<a href='{$theme->getUrl()}'>{$theme->getAcronym()}</a>
</td><td align='left'>
{$theme->getName()}
</td><td>{$leadersString}</td><td>{$coordsString}</td></tr>
EOF;
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";

		return true;
	}
	
	/**
	 * Generates the Table of Admin Projects
	 */
	private function generateAdminTable(){
	    global $wgScriptPath, $wgServer, $config;
	    $me = Person::newFromWgUser();
	    $activityPlans = "";
	    if($config->getValue('networkName') == 'AGE-WELL' && ($me->isProjectLeader() || $me->isRoleAtLeast(STAFF))){
	        $activityPlans = "<th>Activity Plans</th>";
	    }
		$this->text .=
"<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>{$config->getValue('adminProjects')}</th><th>Name</th><th>Leaders</th>{$activityPlans}</tr></thead><tbody>
";
        $adminProjects = Project::getAllProjects();
        foreach($adminProjects as $project){
            if($project->getType() == 'Administrative'){
                $leaders = array();
                foreach($project->getLeaders() as $lead){
                    $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
                }
                $leaderString = implode(", ", $leaders);
                $this->text .= "<tr>";
                $this->text .= "<td><a href='$wgServer$wgScriptPath/index.php/{$project->getName()}:Information'>{$project->getName()}<a></td>";
                $this->text .= "<td>{$project->getFullName()}</td>";
                $this->text .= "<td>{$leaderString}</td>";
                if($config->getValue('networkName') == 'AGE-WELL' && ($me->isProjectLeader() || $me->isRoleAtLeast(STAFF))){
                    $this->text .= "<td>";
                    $projs = array();
                    $projects = array();
                    foreach($me->leadership() as $p){
                        $projects[$p->getName()] = $p;
                    }
                    foreach($me->getThemeProjects() as $p){
                        $projects[$p->getName()] = $p;
                    }
                    foreach($projects as $proj){
                        if($proj->getType() != 'Administrative'){
                            $projs[] = "<a href='$wgServer$wgScriptPath/index.php/Special:Report?report=CCPlanning&project={$proj->getName()}&section={$project->getName()}'>{$proj->getName()}</a>";
                        }
                    }
                    if($me->leadershipOf($project) || $me->isRoleAtLeast(STAFF)){
                        $report = "";
                        switch($project->getName()){
                            case "CC1 K-MOB":
                                $report = "CC1Leader";
                                break;
                            case "CC2 TECH-TRANS":
                                $report = "CC2Leader";
                                break;
                            case "CC3 T-WORK":
                                $report = "CC3Leader";
                                break;
                            case "CC4 TRAIN":
                                $report = "CC4Leader";
                                break;
                        }
                        $projs[] = "<a href='$wgServer$wgScriptPath/index.php/Special:Report?report={$report}&project={$project->getName()}'>Feedback</a>";
                    }
                    $this->text .= implode(", ", $projs);
                    $this->text .= "</td>";
                }
                $this->text .= "</tr>";
            }
        }
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";

		return true;
	}
	
	/**
	 * Generates the Table for the Network Investigators, Collaborating
	 * Researchers, or Highly-Qualified People, depending on parameter
	 * #table.
	 * Consists of the following columns
	 * User Page | Projects | Twitter
	 */
	private function generatePersonTable($table){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
		$me = Person::newFromId($wgUser->getId());
		$data = Person::getAllPeople($table);
		$idHeader = "";
		$contactHeader = "";
		$subRoleHeader = "";
		$projectsHeader = "";
		$universityHeader = "";
		$ldapHeader = "";
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }
        if($me->isLoggedIn() && 
           ($table == TL || $table == TC || $wgRoleValues[$table] >= $wgRoleValues[SD])){
            $contactHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Phone</th>";
        }
        if($table == HQP){
            $subRoleHeader = "<th style='white-space: nowrap;'>Sub Roles</th>";
        }
        if($config->getValue('projectsEnabled') && $table != BOD && $table != ISAC && $table != CAC && $table != IAC && $table != RMC){
            $projectsHeader = "<th style='white-space: nowrap;'>Projects</th>";
        }
	if(!$config->getValue('singleUniversity')){
	   $universityHeader = "<th style='white-space: nowrap;'>University</th>";
	}
	else{
	   $ldapHeader = "<th style='white-space: nowrap; '>LDAP</th>";
	}
        $this->text .= "Below are all the current $table in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
		$this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap;'>Name</th>
                                    {$subRoleHeader}
                                    {$projectsHeader}
				    {$universityHeader}
                                    <th style='white-space: nowrap;'>Department</th>
                                    <th style='white-space: nowrap;'>Title</th>
				    {$ldapHeader}
                                    {$contactHeader}
                                    {$idHeader}</tr>
                                </thead>
                                <tbody>
";
		foreach($data as $person){
		    
			$this->text .= "
<tr>
<td align='left' style='white-space: nowrap;'>
<a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
</td>
";
            if($subRoleHeader != ""){
                $subRoles = $person->getSubRoles();
                $this->text .= "<td style='white-space:nowrap;' align='left'>".implode("<br />", $subRoles)."</td>";
            }
            if($config->getValue('projectsEnabled') && $table != BOD && $table != ISAC && $table != CAC && $table != IAC && $table != RMC){
                $projects = $person->getProjects();
                $projs = array();
			    foreach($projects as $project){
			        if(!$project->isSubProject() && ($project->getPhase() == PROJECT_PHASE)){
				        $subprojs = array();
				        foreach($project->getSubProjects() as $subproject){
				            if($person->isMemberOf($subproject)){
				                $subprojs[] = "<a href='{$subproject->getUrl()}'>{$subproject->getName()}</a>";
				            }
				        }
				        $subprojects = "";
				        if(count($subprojs) > 0){
				            $subprojects = "(".implode(", ", $subprojs).")";
				        }
				        $projs[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a> $subprojects";
				    }
			    }
			    $this->text .= "<td align='left'>".implode("<br />", $projs)."</td>";
			}
			$university = $person->getUniversity();
	    if(!$config->getValue('singleUniversity')){
            	$this->text .= "<td align='left'>{$university['university']}</td>";
            }
	    $this->text .= "<td align='left'>{$university['department']}</td>";
            $this->text .= "<td align='left'>{$university['position']}</td>";
            if($config->getValue('singleUniversity')){
		$this->text .= "<td align='left'>";
		if($person->ldap != ""){
		    $this->text .="<a href='{$person->getLdap()}' target='_blank'>LDAP</a>";
		}
		$this->text .= "</td>";
	    }
	    if($contactHeader != ''){
                $this->text .= "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>";
                $this->text .= "<td align='left'>{$person->getPhoneNumber()}</td>";
            }
			if($idHeader != ''){
			    $this->text .= "<td>{$person->getId()}</td>";
			}
			$this->text .= "</tr>";
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth':false});</script>";

		return true;
	}
	
	function generateMaterialsTable(){
	    global $wgServer, $wgScriptPath;
	    $this->text = "<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Type</th><th>People</th><th>Projects</th></tr></thead><tbody>";
        $materials = Material::getAllMaterials();
        foreach($materials as $material){
            $this->text .= "<tr><td>{$material->getDate()}</td><td><a href='{$material->getUrl()}'>{$material->getTitle()}</a></td><td>{$material->getHumanReadableType()}</td>";
            $projs = array();
            foreach($material->getProjects() as $project){
                $projs[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
            }
            $personLinks = array();
            foreach($material->getPeople() as $person){
                if($person->getType() != ""){
                    $personLinks[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                }
                else{
                    $personLinks[] = "{$person->getName()}";
                }
            }
            $this->text .= "<td>".implode(", ", $personLinks)."</td>";
            $this->text .= "<td>".implode(", ", $projs)."</td>";
            $this->text .= "</tr>";
        }
        $this->text .= "</tbody></table>";
        $this->text .= "<script type='text/javascript'>
	        $(document).ready(function(){
	            $('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
	            $('.indexTable').dataTable().fnSort([[0,'desc']]);
	        });
	    </script>";
	    return true;
	}
	
	function generateFormsTable(){
	    global $wgServer, $wgScriptPath;
	    $this->text = "<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Person</th><th>University</th><th>Project</th></tr></thead><tbody>";
        $forms = Form::getAllForms();
        foreach($forms as $form){
            $personName = "";
            $person = $form->getPerson();
            if($person != null && $person->getName() != ""){
                $personName = "<a href='{$person->getType()}'>{$person->getReversedName()}</a>";
            }

            $university = $form->getUniversity();

            $projectName = "";
            $project = $form->getProject();
            if($project != null && $project->getName() != ""){
                $projectName = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
            }
            $this->text .= "<tr><td>{$form->getDate()}</td><td><a href='$wgServer$wgScriptPath/index.php/Form:{$form->getId()}'>{$form->getTitle()}</a></td><td>{$personName}</td><td>{$university}</td><td>{$projectName}</td>";
            
            $this->text .= "</tr>";
        }
        $this->text .= "</tbody></table>";
        $this->text .= "<script type='text/javascript'>
	        $(document).ready(function(){
	            $('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
	            $('.indexTable').dataTable().fnSort([[0,'desc']]);
	        });
	    </script>";
	    return true;
	}
        /*
         * Generates the Table for the Grants
         * Consists of the following columns
         * Title | Co-grantees | Cash | In Kind | Total 
         */
        private function generateGrantsTable(){
           global $wgUser,$wgOut;
           if(!$wgUser->isLoggedIn()){
                permissionError();
           }
           $contributions = Contribution::getAllContributions();
           $this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style ='white-space:nowrap;'>Year</th>
			<th style='white-space:nowrap;'>Sponsors</th>
			<th style='white-space:nowrap;'>PI</th>
                        <th style='white-space:nowrap;'>Co-grantees</th>
                        <th style='white-space:nowrap;'>Cash</th>
                        <th style='white-space:nowrap;'>In Kind</th>
			<th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";

        foreach($contributions as $contribution){
            $partners = $contribution->getPartners();
            $names = array();
            $pis = array();
	    foreach($contribution->getPeople() as $author){
		if($author instanceof Person){
                    $url = "<a href='{$author->getUrl()}'>{$author->getNameForForms()}</a>";
                    if(!in_array($url,$names)){
                    $names[] = $url;}
               }
               else{
                    if(!in_array($author,$names)){
                        $names[] = $author;
		    }
               }
            }
            foreach($contribution->getPIs() as $pi){
                if($pi instanceof Person){
                    $url = "<a href='{$pi->getUrl()}'>{$pi->getNameForForms()}</a>";
                    if(!in_array($url,$pis)){
                    $pis[] = $url;}
               }
               else{
                    if(!in_array($pi,$pis)){
                        $pis[] = $pi;
                    }
               }
            }


            $this->text .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
				<td align=center>{$contribution->getStartYear()}</td>
                                <td align=center>{$partners[0]->getOrganization()}</td>
				<td>".implode(", ", $pis)."</td>
                                <td>".implode(", ", $names)."</td>
                                <td align=right>$".number_format($contribution->getCash())."</td>
                                <td align=right>$".number_format($contribution->getKind())."</td>
                                 <td align=right>$".number_format($contribution->getTotal())."</td></tr>";
          }
          $this->text .= "</table></tbody><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength':100, 'aaSorting':[[0,'asc'],[1,'desc']]});</script>";

        return true;


        }
        private function generateCoursesTable(){
	   global $wgUser,$wgOut;
	   if(!$wgUser->isLoggedIn()){
		permissionError();
	   }
           $this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style='white-space:nowrap;'>Number</th>
                        <th style='white-space:nowrap;'>Catalog Description</th>
                        </tr></thead><tbody>";

           $courses = Course::getAllCourses();
           foreach($courses as $course){
                $this->text .= "<tr><td>$course->subject</td>
                                <td>$course->catalog</td>
                                <td>$course->courseDescr</td></tr>";



           }
           $this->text .= "</table></tbody><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength':100});</script>";

        return true;
        }
}

?>

<?php

require_once("InactiveUsers.php");

$indexTable = new IndexTable();

$wgHooks['OutputPageParserOutput'][] = array($indexTable, 'generateTable');
$wgHooks['userCan'][] = array($indexTable, 'userCanExecute');

$wgHooks['SubLevelTabs'][] = 'IndexTable::createSubTabs';

class IndexTable {

	var $text = "";
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $config, $wgTitle;
        $me = Person::newFromWgUser();
        $project = Project::newFromHistoricName($wgTitle->getNSText());
        $selected = ((($project != null && $project->getType() != "Administrative") || $wgTitle->getText() == "Projects") &&
                     $wgTitle->getNSText() != "Reboot" &&
                     !($me->isMemberOf($project) || ($project != null && $me->isMemberOf($project->getParent())))) ? "selected" : "";
        $projectTab = TabUtils::createSubTab("Projects", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", "$selected");
        if(Project::areThereDeletedProjects()){
            $projectTab['dropdown'][] = TabUtils::createSubTab("Current", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", $selected);
            $projectTab['dropdown'][] = TabUtils::createSubTab("Completed", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:CompletedProjects", $selected);
        }
        
        $tabs['Main']['subtabs'][] = $projectTab;
        
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
        $peopleSubTab = TabUtils::createSubTab("People");
        if($me->isLoggedIn()){
            $selected = ($lastRole == HQP || $wgTitle->getText() == "ALL HQP" || ($wgTitle->getNSText() == HQP && !($me->isRole(HQP) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(HQP, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_HQP", "$selected");
        }
        
        if(count(Person::getAllPeople(NI)) > 0){
            $selected = ($lastRole == NI || $wgTitle->getText() == "ALL NI" || ($wgTitle->getNSText() == NI && !($me->isRole(NI) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(NI, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_NI", "$selected");
        }
        
        if(count(Person::getAllPeople(ISAC)) > 0){
            $selected = ($lastRole == ISAC || $wgTitle->getText() == "ALL ".ISAC || ($wgTitle->getNSText() == ISAC && !($me->isRole(ISAC) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(ISAC, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_".ISAC, "$selected");
        }
        
        if(count(Person::getAllPeople(EXTERNAL)) > 0){
            $selected = ($lastRole == EXTERNAL || $wgTitle->getText() == "ALL External" || ($wgTitle->getNSText() == EXTERNAL && !($me->isRole(EXTERNAL) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(EXTERNAL, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_External", "$selected");
        }
        
        if(count(Person::getAllPeople(NCE)) > 0){
            $selected = ($lastRole == NCE || $wgTitle->getText() == "ALL NCE Rep" || ($wgTitle->getNSText() == NCE && !($me->isRole(NCE) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(NCE, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_NCE_Rep", "$selected");
        }
        
        if(count(Person::getAllPeople(RMC)) > 0){
            $selected = ($lastRole == RMC || $wgTitle->getText() == "ALL RMC" || ($wgTitle->getNSText() == RMC && !($me->isRole(RMC) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
            $peopleSubTab['dropdown'][] = TabUtils::createSubTab(RMC, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_RMC", "$selected");
        }
        
        $tabs['Main']['subtabs'][] = $peopleSubTab;
        
        if($wgUser->isLoggedIn()){
            $selected = ($wgTitle->getText() == "Products" || 
                         $wgTitle->getText() == "Multimedia Stories" ||
                         $wgTitle->getNsText() == "Publication" ||
                         $wgTitle->getNsText() == "Artifact" ||
                         $wgTitle->getNsText() == "Presentation" ||
                         $wgTitle->getNsText() == "Activity" ||
                         $wgTitle->getNsText() == "Press" ||
                         $wgTitle->getNsText() == "Award" ||
                         $wgTitle->getNsText() == "Multimedia_Story") ? "selected" : "";
            $productsSubTab = TabUtils::createSubTab("Products");
            if(Product::countByCategory('Publication') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Publications", "$wgServer$wgScriptPath/index.php/Special:Products#/Publication", "$selected");
            }
            if(Product::countByCategory('Artifact') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Artifacts", "$wgServer$wgScriptPath/index.php/Special:Products#/Artifact", "$selected");
            }
            if(Product::countByCategory('Presentation') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Presentations", "$wgServer$wgScriptPath/index.php/Special:Products#/Presentation", "$selected");
            }
            if(Product::countByCategory('Activity') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Activities", "$wgServer$wgScriptPath/index.php/Special:Products#/Activity", "$selected");
            }
            if(Product::countByCategory('Press') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Press", "$wgServer$wgScriptPath/index.php/Special:Products#/Press", "$selected");
            }
            if(Product::countByCategory('Award') > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Awards", "$wgServer$wgScriptPath/index.php/Special:Products#/Award", "$selected");
            }
            if(Material::countByCategory() > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab("Multimedia", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Multimedia_Stories", "$selected");
            }
            $tabs['Main']['subtabs'][] = $productsSubTab;
        }
        
        $adminProjects = array();
        $projects = Project::getAllProjects();
        foreach($projects as $project){
            if($project->getType() == 'Administrative'){
                $adminProjects[$project->getName()] = $project;
            }
        }
        
        $themesColl = new Collection(Theme::getAllThemes());
        $themeAcronyms = $themesColl->pluck('getAcronym()');
        $themeNames = $themesColl->pluck('getName()');
        $themes = array();
        foreach($themeAcronyms as $id => $acronym){
            $themes[] = $themeAcronyms[$id].' - '.$themeNames[$id];
        }
        
        if(count($adminProjects) > 0){
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
        
        if(Wiki::newFromTitle("{$config->getValue('networkName')}:ALL_Conferences")->exists()){
            $selected = ($wgTitle->getNSText() == "Conference" || $wgTitle->getText() == "ALL Conferences") ? "selected" : "";
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab("Conferences", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Conferences", "$selected");
        }
        return true;
    }
	
	function userCanExecute(&$title, &$user, $action, &$result){
	    global $wgOut, $wgServer, $wgScriptPath, $config;
	    if($title->getNSText() == "{$config->getValue('networkName')}"){
	        $me = Person::newFromUser($user);
	        $text = $title->getText();
	        switch ($title->getText()) {
	            case 'ALL '.HQP:
				case 'Multimedia Stories':
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
		global $wgTitle, $wgOut, $wgUser, $config;
		$me = Person::newFromId($wgUser->getId());
		if($wgTitle != null && $wgTitle->getNsText() == "{$config->getValue('networkName')}" && !$wgOut->isDisabled()){
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
			    case 'ALL '.HQP:
			        $wgOut->setPageTitle("Highly Qualified Personnel");
				    $this->generatePersonTable(HQP);
				    break;
			    case 'ALL '.NI:
			        $wgOut->setPageTitle($config->getValue('roleDefs', NI));
				    $this->generatePersonTable(NI, 1);
				    break;
				case 'ALL '.ISAC:
			        $wgOut->setPageTitle(ISAC." Members");
				    $this->generatePersonTable(ISAC);
				    break;
				case 'ALL '.EXTERNAL:
			        $wgOut->setPageTitle("External Members");
				    $this->generatePersonTable(EXTERNAL);
				    break;
				case 'ALL '.NCE:
			        $wgOut->setPageTitle("NCE Reps");
				    $this->generatePersonTable(NCE);
				    break;
				case 'ALL '.RMC:
			        $wgOut->setPageTitle("Research Management Committee");
				    $this->generateRMCTable();
				    break;
				case 'Multimedia Stories':
				    $wgOut->setPageTitle("Multimedia Stories");
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
				    $this->generateProjectsTable('Active');
				    break;
				case 'CompletedProjects':
			        $wgOut->setPageTitle("Completed Projects");
				    $this->generateProjectsTable('Ended');
				    break;
				case 'AdminProjects':
			        $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
				    $this->generateProjectsTable('Active', 'Administrative');
				    break;
			    case Inflect::pluralize($config->getValue('projectThemes')):
			        $wgOut->setPageTitle(Inflect::pluralize($config->getValue('projectThemes')));
				    $this->generateThemesTable();
				    break;
			    default:
				    return true;
			}
			TabUtils::clearActions();
			$wgOut->addHTML($this->text);
			$wgOut->output();
			$wgOut->disable();
		}
		return true;
	}
	
	/*
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
        if($me->isRoleAtLeast(MANAGER)){
            $idHeader = "<th>Project Id</th>";
        }
        $data = Project::getAllProjectsEver();
	    $this->text .= "
            <table class='indexTable' style='display:none;' frame='box' rules='all'>
            <thead>
            <tr><th>Acronym</th><th>Name</th>{$themesHeader}{$idHeader}</tr></thead><tbody>";
	    foreach($data as $proj){
	        if($proj->getStatus() == $status && $proj->getType() == $type){
	            $this->text .= "
                    <tr>
                    <td align='left'><a href='{$proj->getUrl()}'>{$proj->getName()}</a></td>
                    <td align='left'>{$proj->getFullName()}</td>";
                if($type != "Administrative"){
                    $this->text .= "<td align='center'>{$proj->getChallenge()->getAcronym()}</td>";
                }
                if($me->isRoleAtLeast(MANAGER)){
                    $this->text .= "<td>{$proj->getId()}</td>\n";
                }
                $this->text .= "</tr>\n";
            }
	    }
	    $this->text .= "</tbody></table>";
		$this->text .= "</div><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100});</script>";

		return true;
	}
	
	/*
	 * Generates the Table for the themes
	 * Consists of the following columns
	 * Theme | Name 
	 */
	private function generateThemesTable(){
		global $wgScriptPath, $wgServer, $config;
		$this->text .=
"<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>{$config->getValue('projectThemes')}</th><th>Name</th><th>Leaders</th></tr></thead><tbody>
";
        $themes = Theme::getAllThemes(PROJECT_PHASE);
		foreach($themes as $theme){
		    $leaders = array();
		    $leads = $theme->getLeaders();
            foreach($leads as $lead){
                $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
            }
		    $leadersString = implode(", ", $leaders);
			$this->text .= <<<EOF
<tr>
<td align='left'>
<a href='{$wgServer}{$wgScriptPath}/index.php/{$config->getValue('networkName')}:{$theme->getAcronym()} - {$theme->getName()}'>{$theme->getAcronym()}</a>
</td><td align='left'>
{$theme->getName()}
</td><td>{$leadersString}</td></tr>
EOF;
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100});</script>";

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
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
		$me = Person::newFromId($wgUser->getId());
		$data = Person::getAllPeople($table);
		$idHeader = "";
        if($me->isRoleAtLeast(MANAGER)){
            $idHeader = "<th width='0%' style='white-space: nowrap;'>User Id</th>";
        }
        $this->text .= "Below are all the current $table in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
		$this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th width='15%' style='white-space: nowrap;'>Name</th><th width='65%' style='white-space: nowrap;'>Projects</th><th width='20%' style='white-space: nowrap;'>University</th>$idHeader</tr></thead><tbody>
";
		foreach($data as $person){
		    $projects = $person->getProjects();
			$this->text .= "
<tr>
<td align='left' style='white-space: nowrap;'>
<a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
</td>
<td align='left'>
";
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
			$this->text .= implode("<br />", $projs);
            $this->text .= "</td><td align='left'>";
            $university = $person->getUniversity();
            $this->text .= $university['university'];
			$this->text .= "</td>";
			if($me->isRoleAtLeast(MANAGER)){
			    $this->text .= "<td>{$person->getId()}</td>";
			}
			$this->text .= "</tr>";
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'bAutoWidth': false});</script>";

		return true;
	}
	
	private function generateRMCTable(){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
		$data = Person::getAllPeople(RMC);

        $this->text .= "Below are all the current ".RMC." in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
		$this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Name</th><th>Roles</th></tr></thead><tbody>";
		foreach($data as $person){
		    $roles = $person->getRoles();
			$this->text .= "
<tr>
<td align='left'>
<a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
</td>
<td align='left'>
";
            foreach($roles as $role){
				$this->text .= "{$role->getRole()}, ";
			}
			if(count($person->getRoles()) > 0){
				$pos = strrpos($this->text, ", ");
				$this->text = substr($this->text, 0, $pos);
			}
			$this->text .= "</tr>";
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100});</script>";
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
	            $('.indexTable').dataTable({'iDisplayLength': 100});
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
	            $('.indexTable').dataTable({'iDisplayLength': 100});
	            $('.indexTable').dataTable().fnSort([[0,'desc']]);
	        });
	    </script>";
	    return true;
	}
}

?>

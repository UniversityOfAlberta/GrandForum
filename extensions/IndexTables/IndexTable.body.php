<?php

//require_once("$IP/extensions/PageRank/PageRank.php");
require_once("InactiveUsers.php");

$indexTable = new IndexTable();

$wgHooks['ArticlePageDataBefore'][] = array($indexTable, 'generateTable');

class IndexTable{

	var $text = "";

	function generateTable($article, $fields){
		global $wgTitle, $wgOut, $wgUser;
		$me = Person::newFromId($wgUser->getId());
		if($wgTitle != null && $wgTitle->getNsText() == "GRAND" && !$wgOut->isDisabled()){
		    $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('.indexTable').css('display', 'table');
                    $('.dataTables_filter').css('float', 'none');
                    $('.dataTables_filter').css('text-align', 'left');
                    $('.dataTables_filter input').css('width', 250);
                });
            </script>");
			switch ($wgTitle->getText()) {
			    case 'ALL '.CNI:
			        $wgOut->setPageTitle("Collaborating Network Investigators");
				    $this->generatePersonTable(CNI);
				    break;
			    case 'ALL '.HQP:
			        $wgOut->setPageTitle("Highly Qualified People");
				    $this->generatePersonTable(HQP);
				    break;
			    case 'ALL '.PNI:
			        $wgOut->setPageTitle("Principal Network Investigators");
				    $this->generatePersonTable(PNI);
				    break;
				case 'ALL '.RMC:
			        $wgOut->setPageTitle("Research Management Committee");
				    $this->generateRMCTable();
				    break;
				case 'Publications':
				    $wgOut->setPageTitle("Publications");
				    $this->generatePublicationsTable("Publication");
				    break;
				case 'Presentations':
				    $wgOut->setPageTitle("Presentations");
				    $this->generatePublicationsTable("Presentation");
				    break;
				case 'Artifacts':
				    $wgOut->setPageTitle("Artifacts");
				    $this->generatePublicationsTable("Artifact");
				    break;
				case 'Multimedia Stories':
				    $wgOut->setPageTitle("Multimedia Stories");
				    $this->generateMaterialsTable();
				    break;
				case 'Forms':
				    if($me->getName() == "Adrian.Sheppard" || $me->getName() == "Admin"){
				        $wgOut->setPageTitle("Forms");
				        $this->generateFormsTable();
				    }
				    break;
			    case 'Projects':
			        $wgOut->setPageTitle("Projects");
				    $this->generateProjectsTable();
				    break;
			    case 'Themes':
			        $wgOut->setPageTitle("Themes");
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
	 * Generates the Table for the themes
	 * Consists of the following columns
	 * Acronym | Name 
	 */
	private function generateProjectsTable(){
		global $wgScriptPath, $wgServer, $wgOut, $wgUser;
        $me = Person::newFromId($wgUser->getId());
        $idHeader = "";
        if($me->isRoleAtLeast(MANAGER)){
            $idHeader = "<th>Project Id</th>";
        }
		$this->text .= "
<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead>
<tr bgcolor='#F2F2F2'><th>Acronym</th><th>Name</th>$idHeader</tr></thead><tbody>
";

		$data = Project::getAllProjects();
		foreach($data as $proj){
			$this->text .= "
<tr>
<td align='left'><a href='{$proj->getUrl()}'>{$proj->getName()}</a></td>
<td align='left'>{$proj->getFullName()}</td>";
            if($me->isRoleAtLeast(MANAGER)){
                $this->text .= "<td>{$proj->getId()}</td>\n";
            }
            $this->text .= "</tr>\n";
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'bPaginate': false});</script>";

		return true;
	}
	
	/*
	 * Generates the Table for the themes
	 * Consists of the following columns
	 * Theme | Name 
	 */
	private function generateThemesTable(){
		global $wgScriptPath, $wgServer;
		$this->text .=
"<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Themes</th><th>Name</th></tr></thead><tbody>
";
		$data = Project::getDefaultThemeNames();
		foreach($data as $key => $name){
			$fn = $this->getThemeFullName($key);
			$this->text .= <<<EOF
<tr>
<td align='left'>
<a href='{$wgServer}{$wgScriptPath}/index.php/GRAND:Theme{$key} - {$fn}'>{$name}</a>
</td><td align='left'>
{$fn}
</td></tr>
EOF;
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'bPaginate': false});</script>";

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
		global $wgServer, $wgScriptPath, $wgUser, $wgOut;
		$me = Person::newFromId($wgUser->getId());
		if ($table == HQP && ! $wgUser->isLoggedIn()){
		    $wgOut->loginToUse();
		    $wgOut->output();
		    $wgOut->disable();
			return;
	    }
		$data = Person::getAllPeople($table);
		$idHeader = "";
        if($me->isRoleAtLeast(MANAGER)){
            $idHeader = "<th>User Id</th>";
        }
        $this->text .= "Below are all the current $table in GRAND.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
		$this->text .= "<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Name</th><th>Projects</th><th>University</th>$idHeader</tr></thead><tbody>
";
		foreach($data as $person){
		    $projects = $person->getProjects();
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            $university = $person->getUniversity();
            if(isset($university['university'])){
                $projs[] = $university['university'];
            }
			$this->text .= "
<tr>
<td align='left'>
<a href='{$wgServer}{$wgScriptPath}/index.php/{$table}:{$person->getName()}'>{$person->getReversedName()}</a>
</td>
<td align='left'>
";
			foreach($projects as $project){
				$this->text .= "<a href='{$project->getUrl()}'>{$project->getName()}</a>, ";
			}
			if(count($person->getProjects()) > 0){
				$pos = strrpos($this->text, ", ");
				$this->text = substr($this->text, 0, $pos);
			}
            $this->text .= "<td align='left'>";
            $university = $person->getUniversity();
            $this->text .= $university['university'];
			$this->text .= "</td>";
			if($me->isRoleAtLeast(MANAGER)){
			    $this->text .= "<td>{$person->getId()}</td>";
			}
			$this->text .= "</tr>";
		}
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'bPaginate': false});</script>";

		return true;
	}
	
	private function generateRMCTable(){
		global $wgServer, $wgScriptPath, $wgUser, $wgOut;
		$data = Person::getAllPeople(RMC);

        $this->text .= "Below are all the current ".RMC." in GRAND.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
		$this->text .= "<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Name</th><th>Roles</th></tr></thead><tbody>
";
		foreach($data as $person){
		    $projects = $person->getProjects();
		    $roles = $person->getRoles();
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            foreach($roles as $role){
                $projs[] = $role->getRole();
            }
            $university = $person->getUniversity();
            if(isset($university['university'])){
                $projs[] = $university['university'];
            }
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
		$this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'bPaginate': false});</script>";

		return true;
	}
	
	private function generatePublicationsTable($type){
	    global $wgScriptPath, $wgServer, $wgUser, $wgOut;
	    if(!$wgUser->isLoggedIn()){
	        $wgOut->loginToUse();
		    $wgOut->output();
		    $wgOut->disable();
			return;
	    }
	    $nonGrand = (isset($_GET['nonGrand']) && strtolower($_GET['nonGrand']) == "true");
	    $papers = array();
	    if($nonGrand){
	        $param = 'nonGrand';
	    }
	    else{
	        $param = 'grand';
	    }
	    if($type == "Publication"){
	        $papers = Paper::getAllPapers('all', 'Publication', $param);
	    }
	    else if($type == "Presentation"){
	        $papers = Paper::getAllPapers('all', 'Presentation', $param);
	    }
	    else if($type == "Artifact"){
	        $papers = array_merge(Paper::getAllPapers('all',"Artifact", $param), 
	                              Paper::getAllPapers('all',"Press", $param),
	                              Paper::getAllPapers('all',"Activity", $param),
	                              Paper::getAllPapers('all',"Award", $param));
	    }
	    if(!$nonGrand){
	        $this->text .= "<a href='$wgServer$wgScriptPath/index.php/GRAND:{$type}s?nonGrand=true'><b>[View Non-GRAND {$type}s]</b></a><br />Below are all the {$type}s in GRAND.  To search for a $type, use the search box below.<br /><br />";
	    }
	    else{
	        $this->text .= "<a href='$wgServer$wgScriptPath/index.php/GRAND:{$type}s'><b>[View GRAND {$type}s]</b></a><br />Below are all the {$type}s which are not associated with GRAND.  To search for a $type, use the search box below.<br /><br />";
	    }
		$this->text .= "<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Date</th><th>Category/Type</th><th style='min-width:300px;'>Title</th><th>Authors</th><th>Projects</th></tr></thead><tbody>";
	    foreach($papers as $paper){
	        $auths = array();
	        foreach($paper->getAuthors() as $author){
	            $auths[] = $author->getName();
	        }
	        $projects = $paper->getProjects();
            $projs = array();
            foreach($projects as $project){
                $projs[] = $project->getName();
            }
            $date = $paper->getDate();
            $title = $paper->getTitle();
            $this->text .= "
                    <tr>
                    <td style='white-space:nowrap;'>{$date}</td>
                    <td style='white-space:nowrap;'>{$paper->getCategory()} / {$paper->getType()}</td>
                    <td align='left'>
                        <a href='{$paper->getUrl()}'>{$title}</a>
                    </td>
                    <td align='left'>";
            $authorLinks = array();
            foreach($paper->getAuthors() as $author){
                if($author->getType() != ""){
                    $authorLinks[] = "<a href='{$author->getUrl()}'>{$author->getNameForForms()}</a>";
                }
                else{
                    $authorLinks[] = "{$author->getName()}";
                }
            }
            $this->text .= implode(", ", $authorLinks)."</td>";
            $this->text .= "<td>". implode(", ", $projs)."</td>";
            $this->text .= "</tr>";
	    }
	    $this->text .= "</tbody></table>";
	    $this->text .= "<script type='text/javascript'>
	        $(document).ready(function(){
	            $('.indexTable').dataTable({'iDisplayLength': 100,
	                                        'aaSorting': [ [0,'desc'], [1,'asc'], [4, 'asc'] ],
	                                        'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
	        });
	    </script>";
	    return true;
	}
	
	function generateMaterialsTable(){
	    global $wgServer, $wgScriptPath;
	    $this->text = "<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Date</th><th style='min-width:300px;'>Title</th><th>Type</th><th>People</th><th>Projects</th></tr></thead><tbody>";
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
	    $this->text = "<table class='indexTable' style='background:#ffffff;display:none;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Date</th><th style='min-width:300px;'>Title</th><th>Person</th><th>University</th><th>Project</th></tr></thead><tbody>";
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
	
	function getThemeFullName($theme){
		if($theme == 1){
			$fullName = "New Media Challenges and Opportunities";
		}
		else if($theme == 2){
			$fullName = "Games and Interactive Simulation";
		}
		else if($theme == 3){
			$fullName = "Animation, Graphics, and Imaging";
		}
		else if($theme == 4){
			$fullName = "Social, Legal, Economic, and Cultural Perspectives";
		}
		else if($theme == 5){
			$fullName = "Enabling Technologies and Methodologies";
		}
		return $fullName;
	}
	
	private function getThemeName($theme){
		if($theme == 1){
			$name = "nMEDIA";
		}
		else if($theme == 2){
			$name = "GamSim";
		}
		else if($theme == 3){
			$name = "AnImage";
		}
		else if($theme == 4){
			$name = "SocLeg";
		}
		else if($theme == 5){
			$name = "TechMeth";
		}
		return $name;
	}
}

?>

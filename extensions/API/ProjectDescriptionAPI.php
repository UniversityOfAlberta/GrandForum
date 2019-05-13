<?php

class ProjectDescriptionAPI extends API{

    function ProjectDescriptionAPI(){
        $this->addPOST("project",true,"The name of the project","MEOW");
	    $this->addPOST("description",true,"The short overview for this project","MEOW is great");
	    $this->addPOST("long_description",true,"The long description for this project","MEOW is great");
	    $this->addPOST("member_status",true,"The member status of this project","Member");
	    $this->addPOST("website",true,"The website url","http://agewell-nce.ca/");
	    $this->addPOST("dept_website",true,"The department website url","http://agewell-nce.ca/");
	    $this->addPOST("email",true,"The email address of the department chair","");
	    $this->addPOST("use_generic",true,"Whether to use the generic chair email or not","1");
	    $this->addPOST("admin_email",true,"The email address of the department admin","");
	    $this->addPOST("admin_use_generic",true,"Whether to use the generic admin email or not","1");
	    $this->addPOST("tech_email",true,"The email address of the department tech","");
	    $this->addPOST("tech_use_generic",true,"Whether to use the generic tech email or not","1");
	    $this->addPOST("fullName",false,"The full name of the project", "Media Enabled Organizational Workflow");
	    $this->addPOST("shortName",false,"The short name of the project", "MEOW");
    }

    function processParams($params){
        if(isset($_POST['description']) && $_POST['description'] != ""){
            $_POST['description'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['description']));
        }
        if(isset($_POST['long_description']) && $_POST['long_description'] != ""){
            $_POST['long_description'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['long_description']));
        }
        if(isset($_POST['website']) && $_POST['website'] != ""){
            $_POST['website'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['website']));
        }
        if(isset($_POST['dept_website']) && $_POST['dept_website'] != ""){
            $_POST['dept_website'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['dept_website']));
        }
        if(isset($_POST['email']) && $_POST['email'] != ""){
            $_POST['email'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['email']));
        }
        if(isset($_POST['use_generic']) && $_POST['use_generic'] != ""){
            $_POST['use_generic'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['use_generic']));
        }
        if(isset($_POST['admin_email']) && $_POST['admin_email'] != ""){
            $_POST['admin_email'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['admin_email']));
        }
        if(isset($_POST['admin_use_generic']) && $_POST['admin_use_generic'] != ""){
            $_POST['admin_use_generic'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['admin_use_generic']));
        }
        if(isset($_POST['tech_email']) && $_POST['tech_email'] != ""){
            $_POST['tech_email'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['tech_email']));
        }
        if(isset($_POST['tech_use_generic']) && $_POST['tech_use_generic'] != ""){
            $_POST['tech_use_generic'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['tech_use_generic']));
        }
        if(isset($_POST['project']) && $_POST['project'] != ""){
            $_POST['project'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['project']));
        }
        if(isset($_POST['fullName']) && $_POST['fullName'] != ""){
            $_POST['fullName'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['fullName']));
        }
        if(isset($_POST['shortName']) && $_POST['shortName'] != ""){
            $_POST['shortName'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['shortName']));
        }
    }

	function doAction($noEcho=false){
	    $me = Person::newFromWgUser();
		$project = Project::newFromName($_POST['project']);
		$error = "";
		if($project == null || $project->getName() == null){
	        $error = "A valid project must be provided";
	    }
        if(!$project->userCanEdit()){
            $error = "You must be logged in as a project leader";
        }
		if(!$noEcho && $error != ""){
		    echo "$error\n";
		    exit;
		}
		if($error != ""){
		    return $error;
		}
		
		if(isset($_POST['fullName'])){
		    $fullName = $_POST['fullName'];
		}
		else{
		    $fullName = $project->getFullName();
		}
		if(isset($_POST['shortName'])){
		    $shortName = $_POST['shortName'];
		}
		else{
		    $shortName = $project->getShortName();
		}
        DBFunctions::begin();
        DBFunctions::update('grand_project_descriptions',
                            array('end_date' => EQ(COL('CURRENT_TIMESTAMP'))),
                            array('project_id' => EQ($project->getId()),
                                  'id' => EQ($project->getLastHistoryId())),
                            array(),
                            true);
        DBFunctions::insert('grand_project_descriptions',
                            array('project_id' => $project->getId(),
                                  'evolution_id' => $project->evolutionId,
                                  'full_name' => $fullName,
                                  'short_name' => $shortName,
                                  'description' => $_POST['description'],
                                  'long_description' => @$_POST['long_description'],
                                  'member_status' => @$_POST['memberStatus'],
                                  'website' => @$_POST['website'],
                                  'dept_website' => @$_POST['dept_website'],
                                  'email' => @$_POST['email'],
                                  'use_generic' => @$_POST['use_generic'],
                                  'admin_email' => @$_POST['admin_email'],
                                  'admin_use_generic' => @$_POST['admin_use_generic'],
                                  'tech_email' => @$_POST['tech_email'],
                                  'tech_use_generic' => @$_POST['tech_use_generic'],
                                  'start_date' => 'CURRENT_TIMESTAMP'),
                            true);
        DBFunctions::commit();
        if(!$noEcho){
            echo "Project description updated\n";
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>

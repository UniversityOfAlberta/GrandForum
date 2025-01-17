<?php

/**
 * @package GrandObjects
 */

class LIMSContactPmm extends BackboneModel {

    static $cache = array();

    var $id;
    var $title;
    var $owner;
    var $projectId;
    var $details = array();
    var $projects = null;
	
	static function newFromId($id){
	    if(!isset(self::$cache[$id])){
	        $data = DBFunctions::select(array('grand_lims_contact'),
	                                    array('*'),
	                                    array('id' => $id));
	        self::$cache[$id] = new LIMSContactPmm($data);
	    }
	    return self::$cache[$id];
	}
	
	static function getAllContacts($project=null){
	    if($project == null){
	        // Get All
	        $data = DBFunctions::select(array('grand_lims_contact'),
	                                    array('id'),
	                                    array());
	    }
	    else{
	        // Get only the contacts which belong to $project
	        $data = DBFunctions::select(array('grand_lims_contact' => 'c', 
	                                          'grand_lims_projects' => 'p'),
	                                    array('c.id'),
	                                    array('c.id' => EQ(COL('p.contact_id')),
	                                          'p.project_id' => $project->getId()));
	    }
	    $contacts = array();
	    foreach($data as $row){
	        $contact = LIMSContactPmm::newFromId($row['id']);
	        if($contact->isAllowedToView()){
	            $contacts[] = $contact;
	        }
	    }
	    return $contacts;
	}
	
	function __construct($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->title = $data[0]['title'];
		    $this->owner = $data[0]['owner'];
                $this->projectId= $data[0]['project_id'];
		    $this->details = json_decode($data[0]['details']);
		    if($this->details == null){
		        $this->details = array();
		    }
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getTitle(){
	    return $this->title;
	}
	
	function getPerson(){
	    return Person::newFromId($this->owner);
	}
	
	function getOwner(){
	    return $this->owner;
	}

	function getProjectId(){
	    return $this->projectId;
	}
	
	function getDetails(){
	    return $this->details;
	}
	
	function getProjects(){
	    if($this->projects === null){
	        $this->projects = array();
	        $data = DBFunctions::select(array('grand_lims_projects'),
	                                    array('project_id'),
	                                    array('contact_id' => $this->getId()));
	        foreach($data as $row){
	            $this->projects[] = Project::newFromId($row['project_id']);
	        }
	    }
	    return $this->projects;
	}
	
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/Special:LIMSPmm#/{$this->getId()}";
	}
	
	function getOpportunities(){
	    return LIMSOpportunityPmm::getOpportunities($this->getId());
	}
	
	function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        return ($this->getPerson()->isMe() || $me->isRoleAtLeast(STAFF));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(STAFF);
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(STAFF);
    }
    
    /**
     * Verifies that the Contact is unique
     */
    function validate(){
        $details = $this->getDetails();
        $data = DBFunctions::select(array('grand_lims_contact'),
                                    array('id'),
                                    array('details' => LIKE('%"firstName":"'.DBFunctions::like($details->firstName).'"%'),
                                          WHERE_AND('details') => LIKE('%"lastName":"'.DBFunctions::like($details->lastName).'"%'),
                                          WHERE_AND('id') => NEQ($this->id)));
        if($details->firstName == "" || $details->lastName == ""){
            return "The first name and last name cannot be empty";
        }
        else if(count($data) > 0){
            return "A contact with the name '{$details->firstName} {$details->lastName}' already exists";
        }
        return true;
    }
	
	function toArray(){
	    if($this->isAllowedToView()){
	        $person = $this->getPerson();
	        $owner = array('id' => $person->getId(),
	                       'name' => $person->getNameForForms(),
	                       'url' => $person->getUrl());
	        $opportunities = array();
	        $projects = array();
            $project_id = array();
	        if(is_array($this->getProjects())){
                foreach($this->getProjects() as $project){
                    $url = "";
                    if($project->getId() != -1){
                        $url = $project->getUrl();
                    }
                    $projects[] = array('id' => $project->getId(),
                                        'name' => $project->getName(),
                                        'url' => $url);
                }
            }
	        foreach($this->getOpportunities() as $opportunity){
	            $opp = $opportunity->toArray();
	            $tasks = array();
	            foreach($opportunity->getTasks() as $task){
	                $tasks[] = $task->toArray();
	            }
	            $opp['tasks'] = $tasks;
	            $opportunities[] = $opp;
	        }
	        
	        $json = array('id' => $this->getId(),
	                      'title' => $this->getTitle(),
	                      'owner' => $owner,
                          'projectId' => $this->getProjectId(),
	                      'details' => $this->getDetails(),
	                      'url' => $this->getUrl(),
	                      'projects' => $projects,
	                      'isAllowedToEdit' => $this->isAllowedToEdit(),
	                      'opportunities' => $opportunities);
	        return $json;
	    }
	    return null;
	}
	
	function create(){
	    if(self::isAllowedToCreate()){
	        $me = Person::newFromWgUser();
	        $this->owner = $me->getId();
	        DBFunctions::insert('grand_lims_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
                                      'project_id' => $this->projectId,
	                                  'details' => json_encode($this->details)));
	        $this->id = DBFunctions::insertId();
	        // Now add projects
	        foreach($this->projects as $project){
                DBFunctions::insert("grand_lims_projects", 
                                    array('contact_id' => $this->id,
                                          'project_id' => $project->id),
                                    true);
            }
            $this->projects = null;
            $this->getProjects();
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        $me = Person::newFromWgUser();
	        $this->owner = $me->getId();
	        DBFunctions::update('grand_lims_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
                                      'project_id' => $this->projectId,
	                                  'details' => json_encode($this->details)),
	                            array('id' => $this->id));
	        // Now add projects
	        $this->getProjects(); // Just incase projects not provided
	        foreach($this->projects as $project){
                if(!isset($project->id) || $project->id == 0){
                    $p = Project::newFromName($project->name);
                    $project->id = $p->getId();
                }
            }
            
	        DBFunctions::delete('grand_lims_projects',
	                            array('contact_id' => $this->id));
	        foreach($this->projects as $project){
                DBFunctions::insert("grand_lims_projects", 
                                    array('contact_id' => $this->id,
                                          'project_id' => $project->id),
                                    true);
            }
            $this->projects = null;
            $this->getProjects();
	    }
	}
	
	function delete(){
	    if($this->isAllowedToEdit()){
	        foreach($this->getOpportunities() as $opportunity){
	            $opportunity->delete();
	        }
	        DBFunctions::delete('grand_lims_projects',
	                            array('contact_id' => $this->id));
	        DBFunctions::delete('grand_lims_contact',
	                            array('id' => $this->id));
	        $this->id = "";
	    }
	}
	
	function exists(){
        return ($this->getId() > 0);
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>

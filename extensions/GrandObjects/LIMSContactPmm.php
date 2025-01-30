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
	
	static function newFromId($id){
	    if(!isset(self::$cache[$id])){
	        $data = DBFunctions::select(array('grand_pmm_contact'),
	                                    array('*'),
	                                    array('id' => $id));
	        self::$cache[$id] = new LIMSContactPmm($data);
	    }
	    return self::$cache[$id];
	}
	
	static function getAllContacts($project_id=null){
	    if($project_id == null){
	        // Get All
	        $data = DBFunctions::select(array('grand_pmm_contact'),
	                                    array('id'),
	                                    array());
	    }
	    else{
	        // Get only the contacts which belong to $project using project_id column directly in the where clause
	        $data = DBFunctions::select(array('grand_pmm_contact'),
	                                    array('id'),
	                                     array('project_id' => $project_id));
              
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

    function getProject(){
	    return Project::newFromId($this->projectId);
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
        $data = DBFunctions::select(array('grand_pmm_contact'),
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
            // Fetches the single project using projectId
            $project = $this->getProject(); 
            if ($project != null) {
                $projectData = array(
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                    'url' => $project->getUrl()
                );
            } else {
                $projectData = array(
                    'id'=> 0,
                    'name'=> '',
                    'url'=> ''
                );
            }

	        $opportunities = array();
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
	                      'project' => $projectData,
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
	        DBFunctions::insert('grand_pmm_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
	                                  'project_id' => $this->projectId,
	                                  'details' => json_encode($this->details)));
	        $this->id = DBFunctions::insertId();
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        $me = Person::newFromWgUser();
	        $this->owner = $me->getId();
	        DBFunctions::update('grand_pmm_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
	                                  'project_id' => $this->projectId,
	                                  'details' => json_encode($this->details)),
	                            array('id' => $this->id));
	    }
	}
	
	function delete(){
	    if($this->isAllowedToEdit()){
	        foreach($this->getOpportunities() as $opportunity){
	            $opportunity->delete();
	        }
	        DBFunctions::delete('grand_pmm_contact',
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

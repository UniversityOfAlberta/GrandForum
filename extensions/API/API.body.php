<?php

autoload_register('API');
autoload_register('API/Publications');
autoload_register('API/Publications/Activities');
autoload_register('API/Publications/Artifacts');
autoload_register('API/Publications/Awards');
autoload_register('API/Publications/Press');
autoload_register('API/Publications/Publications');
autoload_register('API/Publications/Presentations');
global $apiRequest;
$apiRequest = new APIRequest();

$wgHooks['UnknownAction'][] = array($apiRequest, 'processRequest');

/**
 * @package API
 */
class APIRequest{

    static $action;
    var $actions = array();

	function processRequest($action, $article){
		global $wgServer, $wgScriptPath;
		$actions = explode(".", $action, 2);
		if($actions[0] == "api"){
			if(isset($actions[1])){
				self::$action = $actions[1];
				$params = explode("/", self::$action);
				$this->createActions();
				$apiCategories = $this->actions;
				if($params[0] == "index"){
					// This is a special action, which lists all the actions and their help pages
					echo "<h1>API Actions</h1>\n";
					echo "<ul>\n";
					foreach($apiCategories as $key => $apiActions){
					    if($key == "Hidden"){
					        continue;
					    }
						echo "<h2>$key</h2>";
						foreach($apiActions as $key2 => $apiAction){
						    echo "\t<li><a href='$wgServer$wgScriptPath/index.php?action=api.$key2&getHelp'>$key2</a></li>\n";
						}
					}
					echo "</ul>\n";
					echo "<a href='$wgServer$wgScriptPath'>Return to Grand Forum</a>";
				}
				else {
				    $api = null;
				    foreach($params as $key => $param){
		                if($param == ""){
		                    unset($params[$key]);
		                }
		            }
				    foreach($apiCategories as $apiActions){
				        foreach($apiActions as $route => $a){
				            $routeParams = explode("/", $route);
				            
				            $match = true;
				            foreach($routeParams as $key => $param){
				                $match = $match && (isset($params[$key]) && ($param == $params[$key] || 
				                                    strstr($param, ":") !== false));
				                if($match && strstr($param, ":") !== false){
				                    $a->params[str_replace(":", "", $param)] = $params[$key];
				                }
				            }
				            foreach($params as $key => $param){
				                $match = $match && (isset($routeParams[$key]) && ($param == $routeParams[$key] || 
				                                    strstr($routeParams[$key], ":") !== false));
				                if($route == "person/:id"){
				                    //echo $param."<br />";
				                    //var_dump($match);
				                }
				            }
				            if($match){
				                $api = $a;
				                break;
				            }
				        }
				        if($api == null){
					        if(isset($apiActions[$params[0]])){
						        $api = $apiActions[$params[0]];
						        break;
					        }
					    }
					}
					if($api != null){
					    $api->processRequest($params);
					    
				    }
				    else {
					    echo "There is no such API action\n";
				    }
				}
			}
			else {
				echo "No API request was provided.  Exiting!\n";
			}
			exit;
		}
		return true;
	}
	
	function doAction($api, $param=false){
	    global $apiPaths;
	    $api = $api."API";
	    return $api::doAction($param);
	}
	
	function addAction($category, $action, $apiObj){
	    $this->actions[$category][$action] = $apiObj;
	}
	
	function createActions(){
	    global $apiPaths;
		// All API actions should be put into this array
		$actions = array();
		
		//POST
		$this->addAction('Publications', 'addBibtexArticleRef', new BibtexArticleAPI());
		$this->addAction('Publications', 'addBibtexBookRef', new BibtexBookAPI());
		$this->addAction('Publications', 'addBibtexCollectionRef', new BibtexCollectionAPI());
		$this->addAction('Publications', 'addProceedingsPaperRef', new ProceedingsPaperAPI());
		$this->addAction('Publications', 'addCollectionRef', new CollectionAPI());
		$this->addAction('Publications', 'addJournalPaperRef', new JournalPaperAPI());
		$this->addAction('Publications', 'addJournalAbstractRef', new JournalAbstractAPI());
		$this->addAction('Publications', 'addBookRef', new BookAPI());
		$this->addAction('Publications', 'addEditedBookRef', new EditedBookAPI());
		$this->addAction('Publications', 'addBookChapterRef', new BookChapterAPI());
		$this->addAction('Publications', 'addBookReviewRef', new BookReviewAPI());
		$this->addAction('Publications', 'addReviewArticleRef', new ReviewArticleAPI());
		$this->addAction('Publications', 'addWhitePaperRef', new WhitePaperAPI());
		$this->addAction('Publications', 'addMagazineRef', new MagazineAPI());
		$this->addAction('Publications', 'addPHDThesisRef', new PHDThesisAPI());
		$this->addAction('Publications', 'addMastersThesisRef', new MastersThesisAPI());
		$this->addAction('Publications', 'addBachelorsThesisRef', new BachelorsThesisAPI());
		$this->addAction('Publications', 'addTechReportRef', new TechReportAPI());
		$this->addAction('Publications', 'addPosterRef', new PosterAPI());
		$this->addAction('Publications', 'addManualRef', new ManualAPI());
		$this->addAction('Publications', 'addMiscRef', new MiscAPI());
		$this->addAction('Publications', 'updateBibtexArticleRef', new BibtexArticleAPI(true));
		$this->addAction('Publications', 'updateBibtexBookRef', new BibtexBookAPI(true));
		$this->addAction('Publications', 'updateBibtexCollectionRef', new BibtexCollectionAPI(true));
		$this->addAction('Publications', 'updateProceedingsPaperRef', new ProceedingsPaperAPI(true));
		$this->addAction('Publications', 'updateCollectionRef', new CollectionAPI(true));
		$this->addAction('Publications', 'updateJournalPaperRef', new JournalPaperAPI(true));
		$this->addAction('Publications', 'updateJournalAbstractRef', new JournalAbstractAPI(true));
		$this->addAction('Publications', 'updateBookRef', new BookAPI(true));
		$this->addAction('Publications', 'updateEditedBookRef', new EditedBookAPI(true));
		$this->addAction('Publications', 'updateBookChapterRef', new BookChapterAPI(true));
		$this->addAction('Publications', 'updateBookReviewRef', new BookReviewAPI(true));
		$this->addAction('Publications', 'updateReviewArticleRef', new ReviewArticleAPI(true));
		$this->addAction('Publications', 'updateWhitePaperRef', new WhitePaperAPI(true));
		$this->addAction('Publications', 'updateMagazineRef', new MagazineAPI(true));
		$this->addAction('Publications', 'updatePHDThesisRef', new PHDThesisAPI(true));
		$this->addAction('Publications', 'updateMastersThesisRef', new MastersThesisAPI(true));
		$this->addAction('Publications', 'updateBachelorsThesisRef', new BachelorsThesisAPI(true));
		$this->addAction('Publications', 'updateTechReportRef', new TechReportAPI(true));
		$this->addAction('Publications', 'updatePosterRef', new PosterAPI(true));
		$this->addAction('Publications', 'updateManualRef', new ManualAPI(true));
		$this->addAction('Publications', 'updateMiscRef', new MiscAPI(true));
		//GET
		$this->addAction('Publications', 'getPublicationInfo', new PublicationAPI());
		
		//POST
		$this->addAction('Artifacts', 'addRepositoryRef', new RepositoryAPI());
		$this->addAction('Artifacts', 'addOpenSoftwareRef', new SoftwareAPI());
		$this->addAction('Artifacts', 'addPatentRef', new PatentAPI());
		$this->addAction('Artifacts', 'addDeviceRef', new DeviceAPI());
		$this->addAction('Artifacts', 'addAestheticObjectRef', new AestheticObjectAPI());
		$this->addAction('Artifacts', 'addMiscArtifactRef', new ArtifactAPI());
		$this->addAction('Artifacts', 'updateRepositoryRef', new RepositoryAPI(true));
		$this->addAction('Artifacts', 'updateOpenSoftwareRef', new SoftwareAPI(true));
		$this->addAction('Artifacts', 'updatePatentRef', new PatentAPI(true));
		$this->addAction('Artifacts', 'updateDeviceRef', new DeviceAPI(true));
		$this->addAction('Artifacts', 'updateAestheticObjectRef', new AestheticObjectAPI(true));
		$this->addAction('Artifacts', 'updateMiscArtifactRef', new ArtifactAPI(true));
		
		//POST
		//$this->addAction('Activities', 'addInvitedPresentationRef', new InvitedPresentationAPI());
		//$this->addAction('Activities', 'addPresentationRef', new PresentationAPI());
		$this->addAction('Activities', 'addPanelRef', new PanelAPI());
		$this->addAction('Activities', 'addTutorialRef', new TutorialAPI());
		$this->addAction('Activities', 'addEventOrganizationRef', new EventOrganizationAPI());
		//$this->addAction('Activities', 'updateInvitedPresntaqtionRef', new InvitedPresentationAPI(true));
		//$this->addAction('Activities', 'updatePresentationRef', new PresentationAPI(true));
		$this->addAction('Activities', 'updatePanelRef', new PanelAPI(true));
		$this->addAction('Activities', 'updateTutorialRef', new TutorialAPI(true));
		$this->addAction('Activities', 'updateEventOrganizationRef', new EventOrganizationAPI(true));
		
		//POST
		$this->addAction('Press', 'addUniveristyPressRef', new UniversityPressAPI());
		$this->addAction('Press', 'addProvincialPressRef', new ProvincialPressAPI());
		$this->addAction('Press', 'addNationalPressRef', new NationalPressAPI());
		$this->addAction('Press', 'addInternationalPressRef', new InternationalPressAPI());
		$this->addAction('Press', 'addMiscPressRef', new PressAPI());
		$this->addAction('Press', 'updateUniveristyPressRef', new UniversityPressAPI(true));
		$this->addAction('Press', 'updateProvincialPressRef', new ProvincialPressAPI(true));
		$this->addAction('Press', 'updateNationalPressRef', new NationalPressAPI(true));
		$this->addAction('Press', 'updateInternationalPressRef', new InternationalPressAPI(true));
		$this->addAction('Press', 'updateMiscPressRef', new PressAPI(true));
		
		//POST
		$this->addAction('Awards', 'addAwardRef', new AwardsAPI());
		$this->addAction('Awards', 'updateAwardRef', new AwardsAPI(true));
		
		//POST
		$this->addAction('Products', 'deletePaperRef', new DeletePaperAPI());
		
		//POST
		$this->addAction('Materials', 'addMaterialRef', new AwardsAPI());
		$this->addAction('Materials', 'updateMaterialRef', new AwardsAPI(true));
		//GET
		$this->addAction('Materials', 'getMaterialList', new MaterialListAPI());
		$this->addAction('Materials', 'getMaterialInfo', new MaterialAPI());
		
		//POST
		$this->addAction('User Accounts', 'addUserAccount', new CreateUserAPI());
		$this->addAction('User Accounts', 'addUserRequest', new RequestUserAPI());
		$this->addAction('User Accounts', 'addUserRole', new AddRoleAPI());
		$this->addAction('User Accounts', 'addProjectLeader', new AddProjectLeaderAPI());
		$this->addAction('User Accounts', 'addThemeLeader', new AddThemeLeaderAPI());
		$this->addAction('User Accounts', 'addHQPThesis', new AddHQPThesisAPI());
		$this->addAction('User Accounts', 'addHQPMovedOn', new AddHQPMovedOnAPI());
		$this->addAction('User Accounts', 'addRelation', new AddRelationAPI());
		$this->addAction('User Accounts', 'addUserPartner', new UserPartnerAPI());
		$this->addAction('User Accounts', 'updateUserTwitterAccount', new UserTwitterAccountAPI());
		$this->addAction('User Accounts', 'updateUserNationality', new UserNationalityAPI());
        $this->addAction('User Accounts', 'updateUserEmail', new UserEmailAPI());
        $this->addAction('User Accounts', 'updateUserGender', new UserGenderAPI());
		$this->addAction('User Accounts', 'updateUserUniversity', new UserUniversityAPI());
		$this->addAction('User Accounts', 'updateUserProfile', new UserProfileAPI());
		$this->addAction('User Accounts', 'updateProjectRelation', new UpdateProjectRelationAPI());
		$this->addAction('User Accounts', 'updateUserPartner', new UserPartnerAPI());
		$this->addAction('User Accounts', 'deleteUserRole', new DeleteRoleAPI());
		$this->addAction('User Accounts', 'deleteProjectLeader', new DeleteProjectLeaderAPI());
		$this->addAction('User Accounts', 'deleteThemeLeader', new DeleteThemeLeaderAPI());
		//GET
		$this->addAction('User Accounts', 'getResearcherInfo', new ResearcherAPI());
		$this->addAction('User Accounts', 'getResearcherCompleteInfo', new ResearcherCompleteAPI());
		
		//POST
		$this->addAction('Contributions', 'addContribution', new AddContributionAPI());
		$this->addAction('Contributions', 'updateContribution', new AddContributionAPI());
		
		//POST
		$this->addAction('Projects', 'createProject', new CreateProjectAPI());
		$this->addAction('Projects', 'addProjectMember', new AddProjectMemberAPI());
		$this->addAction('Projects', 'addProjectMilestone', new ProjectMilestoneAPI());
		$this->addAction('Projects', 'updateProjectDescription', new ProjectDescriptionAPI());
		$this->addAction('Projects', 'updateProjectMilestone', new ProjectMilestoneAPI(true));
		$this->addAction('Projects', 'evolveProject', new EvolveProjectAPI());
		$this->addAction('Projects', 'deleteProjectMember', new DeleteProjectMemberAPI());
		$this->addAction('Projects', 'deleteProject', new DeleteProjectAPI());
		//GET
		$this->addAction('Projects', 'getProjectInfo', new ProjectInfoAPI());
		
		$this->addAction('Hidden', 'getWFInfo', new WFAPI());
		$this->addAction('Hidden', 'getProjectMilestoneHistory', new ProjectMilestoneHistoryAPI());
		
		return $actions;
	}
}

/**
 * @package API
 */
abstract class API {

    var $errors = array();
    var $posts = array();
    var $gets = array();
    var $messages = array();
    var $data = array();

	function processRequest($params=null){
		global $wgUser;
		session_write_close();
		if(isset($_GET['getHelp'])){
			$this->getHelp();
		}
		else{
			if($this->isLoginRequired()){
				$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : "";
				$password = isset($_POST['password']) ? $_POST['password'] : "";
				
				$user = User::newFromName($user_name);
				if($user != false && $user->checkPassword($password) || $wgUser->isLoggedIn()){ 
					// UserName and password are correct, or the user is in the browser and is already logged in
					if(!$wgUser->isLoggedIn()){
					    $wgUser = $user;
					}
					$this->processParams($params);
					if($this->checkRequiredParams()){
				        $this->doAction();
				    }
				}
				else { 
					// Authentication failed
					$this->addError("Authentication Failed\n");
				}
			}
			else{
                $this->processParams($params);
                if($this->checkRequiredParams()){
				    $this->doAction();
				}
			}
			header('Content-Type: application/json');
			echo json_encode(array('errors' => $this->errors,
			                       'messages' => $this->messages,
			                       'data' => $this->data));
			exit;
		}
	}
	
	function addPOST($name, $required, $description, $example){
	    $this->posts[$name] = array("name" => $name, 
	                                "required" => $required,
	                                "description" => $description,
	                                "example" => $example);
	}
	
	function addGET($name, $required, $description, $example){
	    $this->gets[$name] = array("name" => $name, 
	                               "required" => $required,
	                               "description" => $description,
	                               "example" => $example);
	}
	
	/**
	 * @param string $key The key of the POST parameter
	 * @return string Returns the value of the POST parameter
	 */
	function POST($key){
	    if(isset($_POST[$key])) return $_POST[$key];
	    $model = (isset($_POST['model'])) ? json_decode($_POST['model']) : array();
	    if(isset($model->$key)) return $model->$key;
	}
	
	/**
	 * @param string $key The key of the GET parameter
	 * @return string Returns the value of the GET parameter
	 */
	function GET($key){
	    return (isset($_GET[$key])) ? $_GET[$key] : "";
	}
	
	function addError($error){
	    $this->errors[] = $error;
	}
	
	function addMessage($message){
	    $this->messages[] = $message;
	}
	
	function addData($key, $value){
	    $this->data[$key] = $value;
	}
	
	function checkRequiredParams(){
	    $result = true;
	    foreach($this->posts as $post){
	        if($post['required']){
	            if(!isset($_POST[$post['name']])){
	                $this->addError("POST {$post['name']} must be provided\n");
	                $result = false;
	            }
	        }
	    }
	    foreach($this->gets as $get){
	        if($get['required']){
	            if(!isset($_GET[$get['name']])){
	                $this->addError("GET {$get['name']} must be provided\n");
	                $result = false;
	            }
	        }
	    }
	    return $result;
	}
	
	function getHelp(){
	    global $wgServer, $wgScriptPath;
	    $login = "NO";
	    $loginParams = "";
	    if($this->isLoginRequired()){
	        $login = "YES";
            $loginParams = "<ul>
					            <li>POST user_name: Your user name for the grand forum. <b>[Required]</b></li>
					            <li>POST password: Your password for the grand forum. <b>[Required]</b></li>
				            </ul>";
	    }
	    $postParams = "";
	    if(count($this->posts) > 0 || $this->isLoginRequired()){
	        $postArray = array();
	        if($this->isLoginRequired()){
	            $postArray[] = "user_name=User";
	            $postArray[] = "password=Password";
	        }
	        foreach($this->posts as $post){
	            $postArray[] = "{$post['name']}={$post['example']}";
	        }
	        $postParams = "wget -q -O - --post-data '".implode("&", $postArray)."' ";
	    }
	    $getString = "";
	    $getParams = "";
	    foreach($this->gets as $get){
	        $required = "";
	        if($get['required']){
	            $required = "<b>[Required]</b>";
	        }
	        $getParams .= "/{$get['example']}";
	        $getString .= "<li>GET {$get['name']}: {$get['description']}. $required</li>";
	    }
	    $postString = "";
	    foreach($this->posts as $post){
	        $required = "";
	        if($post['required']){
	            $required = "<b>[Required]</b>";
	        }
	        $postString .= "<li>POST {$post['name']}: {$post['description']}. $required</li>";
	    }
		echo "<h1>".APIRequest::$action."</h1>
			<b>URL:</b> $wgServer$wgScriptPath/index.php?action=api.".APIRequest::$action."<br />
			<br />
			<b>Authentication Required:</b> {$login}<br />
			<br />
			<b>Parameters:</b>
				$loginParams
				<ul>
					{$getString}
					{$postString}
				</ul>
			<br />
			<b>Example Usage:</b><br /><code>$postParams$wgServer$wgScriptPath/index.php?action=api.".APIRequest::$action."{$getParams}</code><br /><br />
			<a href='$wgServer$wgScriptPath/index.php?action=api.index'>Return to Index</a>";
		exit;
	}
	
	/**
	 * Does some pre-proccessing to the parameters
	 * @param array $params The array of GET parameters
	 */
	abstract function processParams($params);
	
	/**
	 * @return boolean Returns whether or not a login is required to execute this action
	 */
	abstract function isLoginRequired();
	
	/**
	 * Runs the API action
	 */
	abstract function doAction();
}

/**
 * @package API
 */
abstract class RESTAPI extends API {

    var $params = array();
    
    function processRequest($params=null){
		global $wgUser;
		if(isset($_GET['getHelp'])){
			$this->getHelp();
		}
		else{
			if($this->isLoginRequired() && !$wgUser->isLoggedIn()){
                header("HTTP/1.0: 403 Authentication Required");
                exit;
            }
			$this->processParams($params);
		    $this->doAction();
		}
	}
	
	/**
	 * Returns the value of the specified parameter if it exists ("" otherwise)
	 * @param string $id The id of the parameter
	 * @return string Returns the value of the parameter
	 */
	function getParam($id){
	    return (isset($this->params[$id])) ? $this->params[$id] : "";
	}
	
	/**
	 * In most cases for the RESTAPI login should not be required.
	 * This should instead be implemented for each of the doACTION functions.
	 */
	function isLoginRequired(){
        return false;
    }
	
	/**
	 * Generates a error message via the HTTP 400 return code, and exits execution
	 * @param string $message The message to display
	 * @param int $code the HTTP error code
	 */
	function throwError($message, $code=400){
	    header("HTTP/1.0: $code $message");
	    exit;
	}
    
    function doAction(){
        global $wgUser;
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "GET"){
            $json = $this->doGET();
        }
        else if($method == "PUT" || ($method == "POST" && @$_POST['_method'] == "PUT")){
            $json = $this->doPUT();
        }
        else if($method == "DELETE" || ($method == "POST" && @$_POST['_method'] == "DELETE")){
            $json = $this->doDELETE();
        }
        else if($method == "POST"){
            $json = $this->doPOST();
        }
        header('Content-Type: application/json');
        echo $json;
        exit;
    }
    
    function processParams($params){ }
    
    /**
     * CREATE/POST
     * @abstract
     */
    abstract function doPOST();
    
    /**
     * READ/GET
     * @abstract
     */
    abstract function doGET();

    /**
     * UPDATE/PUT
     * @abstract
     */
    abstract function doPUT();
    
    /**
     * DELETE/DELETE
     * @abstract
     */
    abstract function doDELETE();
    
}
?>

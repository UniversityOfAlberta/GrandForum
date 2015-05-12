<?php

autoload_register('API');
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
		    session_write_close();
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
				                else if($match){
				                    $a->params[$key] = $params[$key];
				                }
				            }
				            foreach($params as $key => $param){
				                $match = $match && (isset($routeParams[$key]) && ($param == $routeParams[$key] || 
				                                    strstr($routeParams[$key], ":") !== false));
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
	    $obj = new $api();
	    return $obj->doAction($param);
	}
	
	function addAction($category, $action, $apiObj){
	    $this->actions[$category][$action] = $apiObj;
	}
	
	function createActions(){
	    //POST
	    $this->addAction('Products', 'uploadCCV', new UploadCCVAPI());
	    $this->addAction('Products', 'importBibTeX', new ImportBibTeXAPI());
	    $this->addAction('Products', 'importDOI', new ImportDOIAPI());
	    $this->addAction('Products', 'getPublicationSearch', new PublicationSearchAPI());
	
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
		$this->addAction('User Accounts', 'updateUserWebsite', new UserWebsiteAPI());
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
		$this->addAction('User Accounts', 'updateUserEthics', new UserEthicsAPI());

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
		$this->addAction('Projects', 'addProjectJoinRequest', new ProjectJoinRequestAPI());
		$this->addAction('Projects', 'updateProjectAcronym', new UpdateProjectAcronymAPI());
		$this->addAction('Projects', 'updateProjectDescription', new ProjectDescriptionAPI());
		$this->addAction('Projects', 'updateProjectChallenge', new ProjectChallengeAPI());
		$this->addAction('Projects', 'updateProjectMilestone', new ProjectMilestoneAPI(true));
		$this->addAction('Projects', 'evolveProject', new EvolveProjectAPI());
		$this->addAction('Projects', 'deleteProjectMember', new DeleteProjectMemberAPI());
		$this->addAction('Projects', 'deleteProject', new DeleteProjectAPI());
		//GET
		$this->addAction('Projects', 'getProjectInfo', new ProjectInfoAPI());
		$this->addAction('Themes', 'getThemeInfo', new ThemeInfoAPI());
		
		//GET
		$this->addAction('University', 'getEthicsStats', new UniversityEthicsAPI());
		
		// HIDDENS
		$this->addAction('Hidden', 'getWFInfo', new WFAPI());
		$this->addAction('Hidden', 'getJung', new JungAPI());
		$this->addAction('Hidden', 'addRecordStory', new RecordStoryAPI());
		$this->addAction('Hidden', 'getProjectMilestoneHistory', new ProjectMilestoneHistoryAPI());
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
	    echo $message;
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
        ob_start("ob_gzhandler");
        echo $json;
        DBFunctions::close();
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

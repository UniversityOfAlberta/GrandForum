<?php

autoload_register('API');
autoload_register('API/Publications');
autoload_register('API/Publications/Activities');
autoload_register('API/Publications/Artifacts');
autoload_register('API/Publications/Awards');
autoload_register('API/Publications/Press');
autoload_register('API/Publications/Publications');
autoload_register('API/Publications/Presentations');

$apiRequest = new APIRequest();

$wgHooks['UnknownAction'][] = array($apiRequest, 'processRequest');

class APIRequest{

    static $action;

	function processRequest($action, $article){
		global $wgServer, $wgScriptPath;
		$actions = explode(".", $action, 2);
		if($actions[0] == "api"){
			if(isset($actions[1])){
				self::$action = $actions[1];
				$params = explode("/", self::$action);
				$apiCategories = $this->createActions();
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
				    foreach($apiCategories as $apiActions){
					    if(isset($apiActions[$params[0]])){
						    $api = $apiActions[$params[0]];
						    break;
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
	
	function createActions(){
	    global $apiPaths;
		// All API actions should be put into this array
		$actions = array();
		
		//POST
		$actions['Publications']['addBibtexArticleRef'] = new BibtexArticleAPI();
		$actions['Publications']['addBibtexBookRef'] = new BibtexBookAPI();
		$actions['Publications']['addBibtexCollectionRef'] = new BibtexCollectionAPI();
		$actions['Publications']['addProceedingsPaperRef'] = new ProceedingsPaperAPI();
		$actions['Publications']['addCollectionRef'] = new CollectionAPI();
		$actions['Publications']['addJournalPaperRef'] = new JournalPaperAPI();
		$actions['Publications']['addJournalAbstractRef'] = new JournalAbstractAPI();
		$actions['Publications']['addBookRef'] = new BookAPI();
		$actions['Publications']['addEditedBookRef'] = new EditedBookAPI();
		$actions['Publications']['addBookChapterRef'] = new BookChapterAPI();
		$actions['Publications']['addBookReviewRef'] = new BookReviewAPI();
		$actions['Publications']['addReviewArticleRef'] = new ReviewArticleAPI();
		$actions['Publications']['addWhitePaperRef'] = new WhitePaperAPI();
		$actions['Publications']['addMagazineRef'] = new MagazineAPI();
		$actions['Publications']['addPHDThesisRef'] = new PHDThesisAPI();
		$actions['Publications']['addMastersThesisRef'] = new MastersThesisAPI();
		$actions['Publications']['addBachelorsThesisRef'] = new BachelorsThesisAPI();
		$actions['Publications']['addTechReportRef'] = new TechReportAPI();
		$actions['Publications']['addPosterRef'] = new PosterAPI();
		$actions['Publications']['addManualRef'] = new ManualAPI();
		$actions['Publications']['addMiscRef'] = new MiscAPI();
		$actions['Publications']['updateBibtexArticleRef'] = new BibtexArticleAPI(true);
		$actions['Publications']['updateBibtexBookRef'] = new BibtexBookAPI(true);
		$actions['Publications']['updateBibtexCollectionRef'] = new BibtexCollectionAPI(true);
		$actions['Publications']['updateProceedingsPaperRef'] = new ProceedingsPaperAPI(true);
		$actions['Publications']['updateCollectionRef'] = new CollectionAPI(true);
		$actions['Publications']['updateJournalPaperRef'] = new JournalPaperAPI(true);
		$actions['Publications']['updateJournalAbstractRef'] = new JournalAbstractAPI(true);
		$actions['Publications']['updateBookRef'] = new BookAPI(true);
		$actions['Publications']['updateEditedBookRef'] = new EditedBookAPI(true);
		$actions['Publications']['updateBookChapterRef'] = new BookChapterAPI(true);
		$actions['Publications']['updateBookReviewRef'] = new BookReviewAPI(true);
		$actions['Publications']['updateReviewArticleRef'] = new ReviewArticleAPI(true);
		$actions['Publications']['updateWhitePaperRef'] = new WhitePaperAPI(true);
		$actions['Publications']['updateMagazineRef'] = new MagazineAPI(true);
		$actions['Publications']['updatePHDThesisRef'] = new PHDThesisAPI(true);
		$actions['Publications']['updateMastersThesisRef'] = new MastersThesisAPI(true);
		$actions['Publications']['updateBachelorsThesisRef'] = new BachelorsThesisAPI(true);
		$actions['Publications']['updateTechReportRef'] = new TechReportAPI(true);
		$actions['Publications']['updatePosterRef'] = new PosterAPI(true);
		$actions['Publications']['updateManualRef'] = new ManualAPI(true);
		$actions['Publications']['updateMiscRef'] = new MiscAPI(true);
		//GET
		$actions['Publications']['getPublicationInfo'] = new PublicationAPI();
		
		//POST
		$actions['Artifacts']['addRepositoryRef'] = new RepositoryAPI();
		$actions['Artifacts']['addOpenSoftwareRef'] = new SoftwareAPI();
		$actions['Artifacts']['addPatentRef'] = new PatentAPI();
		$actions['Artifacts']['addDeviceRef'] = new DeviceAPI();
		$actions['Artifacts']['addAestheticObjectRef'] = new AestheticObjectAPI();
		$actions['Artifacts']['addMiscArtifactRef'] = new ArtifactAPI();
		$actions['Artifacts']['updateRepositoryRef'] = new RepositoryAPI(true);
		$actions['Artifacts']['updateOpenSoftwareRef'] = new SoftwareAPI(true);
		$actions['Artifacts']['updatePatentRef'] = new PatentAPI(true);
		$actions['Artifacts']['updateDeviceRef'] = new DeviceAPI(true);
		$actions['Artifacts']['updateAestheticObjectRef'] = new AestheticObjectAPI(true);
		$actions['Artifacts']['updateMiscArtifactRef'] = new ArtifactAPI(true);
		
		//POST
		//$actions['Activities']['addInvitedPresentationRef'] = new InvitedPresentationAPI();
		//$actions['Activities']['addPresentationRef'] = new PresentationAPI();
		$actions['Activities']['addPanelRef'] = new PanelAPI();
		$actions['Activities']['addTutorialRef'] = new TutorialAPI();
		$actions['Activities']['addEventOrganizationRef'] = new EventOrganizationAPI();
		//$actions['Activities']['updateInvitedPresntaqtionRef'] = new InvitedPresentationAPI(true);
		//$actions['Activities']['updatePresentationRef'] = new PresentationAPI(true);
		$actions['Activities']['updatePanelRef'] = new PanelAPI(true);
		$actions['Activities']['updateTutorialRef'] = new TutorialAPI(true);
		$actions['Activities']['updateEventOrganizationRef'] = new EventOrganizationAPI(true);
		
		//POST
		$actions['Press']['addUniveristyPressRef'] = new UniversityPressAPI();
		$actions['Press']['addProvincialPressRef'] = new ProvincialPressAPI();
		$actions['Press']['addNationalPressRef'] = new NationalPressAPI();
		$actions['Press']['addInternationalPressRef'] = new InternationalPressAPI();
		$actions['Press']['addMiscPressRef'] = new PressAPI();
		$actions['Press']['updateUniveristyPressRef'] = new UniversityPressAPI(true);
		$actions['Press']['updateProvincialPressRef'] = new ProvincialPressAPI(true);
		$actions['Press']['updateNationalPressRef'] = new NationalPressAPI(true);
		$actions['Press']['updateInternationalPressRef'] = new InternationalPressAPI(true);
		$actions['Press']['updateMiscPressRef'] = new PressAPI(true);
		
		//POST
		$actions['Awards']['addAwardRef'] = new AwardsAPI();
		$actions['Awards']['updateAwardRef'] = new AwardsAPI(true);
		
		//POST
		$actions['Products']['deletePaperRef'] = new DeletePaperAPI();
		
		//POST
		$actions['Materials']['addMaterialRef'] = new AwardsAPI();
		$actions['Materials']['updateMaterialRef'] = new AwardsAPI(true);
		//GET
		$actions['Materials']['getMaterialList'] = new MaterialListAPI();
		$actions['Materials']['getMaterialInfo'] = new MaterialAPI();
		
		//POST
		$actions['User Accounts']['addUserAccount'] = new CreateUserAPI();
		$actions['User Accounts']['addUserRequest'] = new RequestUserAPI();
		$actions['User Accounts']['addUserRole'] = new AddRoleAPI();
		$actions['User Accounts']['addProjectLeader'] = new AddProjectLeaderAPI();
		$actions['User Accounts']['addThemeLeader'] = new AddThemeLeaderAPI();
		$actions['User Accounts']['addHQPThesis'] = new AddHQPThesisAPI();
		$actions['User Accounts']['addHQPMovedOn'] = new AddHQPMovedOnAPI();
		$actions['User Accounts']['addRelation'] = new AddRelationAPI();
		$actions['User Accounts']['addUserPartner'] = new UserPartnerAPI();
		$actions['User Accounts']['updateUserTwitterAccount'] = new UserTwitterAccountAPI();
		$actions['User Accounts']['updateUserNationality'] = new UserNationalityAPI();
        $actions['User Accounts']['updateUserEmail'] = new UserEmailAPI();
        $actions['User Accounts']['updateUserGender'] = new UserGenderAPI();
		$actions['User Accounts']['updateUserUniversity'] = new UserUniversityAPI();
		$actions['User Accounts']['updateUserProfile'] = new UserProfileAPI();
		$actions['User Accounts']['updateProjectRelation'] = new UpdateProjectRelationAPI();
		$actions['User Accounts']['updateUserPartner'] = new UserPartnerAPI();
		$actions['User Accounts']['deleteUserRole'] = new DeleteRoleAPI();
		$actions['User Accounts']['deleteProjectLeader'] = new DeleteProjectLeaderAPI();
		$actions['User Accounts']['deleteThemeLeader'] = new DeleteThemeLeaderAPI();
		//GET
		$actions['User Accounts']['getResearcherInfo'] = new ResearcherAPI();
		$actions['User Accounts']['getResearcherCompleteInfo'] = new ResearcherCompleteAPI();
		
		//POST
		$actions['Contributions']['addContribution'] = new AddContributionAPI();
		$actions['Contributions']['updateContribution'] = new AddContributionAPI();
		
		//POST
		$actions['Projects']['createProject'] = new CreateProjectAPI();
		$actions['Projects']['addProjectMember'] = new AddProjectMemberAPI();
		$actions['Projects']['addProjectMilestone'] = new ProjectMilestoneAPI();
		$actions['Projects']['updateProjectDescription'] = new ProjectDescriptionAPI();
		$actions['Projects']['updateProjectMilestone'] = new ProjectMilestoneAPI(true);
		$actions['Projects']['evolveProject'] = new EvolveProjectAPI();
		$actions['Projects']['deleteProjectMember'] = new DeleteProjectMemberAPI();
		$actions['Projects']['deleteProject'] = new DeleteProjectAPI();
		//GET
		$actions['Projects']['getProjectInfo'] = new ProjectAPI();
		
		$actions['Hidden']['getWFInfo'] = new WFAPI();
		$actions['Hidden']['getProjectMilestoneHistory'] = new ProjectMilestoneHistoryAPI();
		
		return $actions;
	}
}

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
	
	abstract function processParams($params);
	
	abstract function isLoginRequired();
	
	abstract function doAction();
}
?>

<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['Survey'] = 'Survey';
$wgExtensionMessagesFiles['Survey'] = $dir . 'Survey.i18n.php';
$wgSpecialPageGroups['Survey'] = 'report-reviewing';

autoload_register('Survey');
autoload_register('Survey/SectionTabs');

function runSurvey($par) {
	global $wgScriptPath, $wgOut, $wgUser, $wgTitle, $_tokusers;
	Survey::show();
	$wgOut->setPageTitle("NAVEL Survey");
}

class Survey extends SpecialPage {

	function __construct() {
		SpecialPage::__construct("Survey", HQP.'+', true, 'runSurvey');
	}
	
	static function show(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;

		$init_tab =	self::getLastTab();		
		
    	$tabbedPage = new SurveyPage("survey");

   		$consentTab = new ConsentTab();
   		$consent = $consentTab->getSavedData();
   		$consent = ($consent['consent'])? $consent['consent'] : (isset($_POST['consent']) && $_POST['consent']=="Agree")? 1 : 0;
   	
   		$tabbedPage->addTab($consentTab);
        if($consent){
	        $tabbedPage->addTab(new AboutTab());
	        $tabbedPage->addTab(new ProjectExperienceTab());
			$tabbedPage->addTab(new GrandExperienceTab());
	        $tabbedPage->addTab(new NetworkTab());
	        $tabbedPage->addTab(new ProfNetworkTab());
	        $tabbedPage->addTab(new YourCommunicationTab());
			$tabbedPage->addTab(new ReviewTab());
    	}

    	//Handle AJAX call to get the tab's content
        if(isset($_GET['get_tab_content']) && $_GET['get_tab_content'] != ""){
            $validate = (isset($_GET['validate']) && $_GET['validate'] == "1")? true : false;
            echo $tabbedPage->getTabContent($_GET['get_tab_content'], $validate);
            exit;
        }
        else{
        	$tabbedPage->showPage($init_tab);
		}
	}

	static function getLastTab(){
		global $wgUser;
        $my_id = $wgUser->getId();
        $last_tab = 0;

        $sql = "SELECT current_tab FROM survey_results WHERE user_id='{$my_id}'";
        $data = DBFunctions::execSQL($sql);

       	if(isset($data[0])){
       		$last_tab = $data[0]['current_tab'];
       	}

       	return $last_tab;
	}


}

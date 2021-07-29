<?php

$wgHooks['ToolboxLinks'][] = 'ManageProducts::createToolboxLinks';
BackbonePage::register('ManageProducts', 'Manage '.Inflect::pluralize($config->getValue("productsTerm")), 'network-tools', dirname(__FILE__));

class ManageProducts extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isLoggedIn();
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'Products/*',
                     'manage_products',
                     'manage_products_row',
                     'duplicates_dialog');
    }
    
    function getViews(){
        global $wgOut;
        $emptyProject = new Project(array());
        $publicationsFrozen = json_encode($emptyProject->isFeatureFrozen("Publications"));
        
        $wgOut->addScript("<script type='text/javascript'>
            var publicationsFrozen = $publicationsFrozen;
        </script>");
        
        return array('Backbone/*',
                     'Products/*',
                     'ManageProductsView',
                     'ManageProductsRowView',
                     'DuplicatesDialogView');
    }
    
    function getModels(){
        global $wgOut;
        $students = array();
        $studentNames = array();
        $studentFullNames = array();
        $person = Person::newFromWgUser();
        foreach($person->getHQP(true) as $hqp){
            $students[] = $hqp->getId();
            $studentNames[] = $hqp->getName();
            $studentFullNames[] = $hqp->getNameForForms();
        }
        $wgOut->addScript("<script type='text/javascript'>
            var students = ".json_encode($students).";
            var studentNames = ".json_encode($studentNames).";
            var studentFullNames = ".json_encode($studentFullNames).";
        </script>");
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    if(ManageProducts::userCanExecute($wgUser)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage ".Inflect::pluralize($config->getValue("productsTerm")), 
	                                                                      "$wgServer$wgScriptPath/index.php/Special:ManageProducts");
	    }
	    return true;
	}
	
	function execute($par){
	    self::importORCID();
	    parent::execute($par);
	}
	
	static function importORCID(){
	    global $wgMessage, $config, $wgServer, $wgScriptPath;
	    if(isset($_GET['error']) && $_GET['error'] == 'access_denied'){
	        echo "<script type='text/javascript'>window.close();</script>";
	        exit;
	    }
	    else if(isset($_GET['code'])){
            //open connection
            if(!isset($_COOKIE['access_token'])){
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, "https://orcid.org/oauth/token");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id={$config->getValue('orcidId')}&client_secret={$config->getValue('orcidSecret')}&grant_type=authorization_code&redirect_uri={$wgServer}{$wgScriptPath}/index.php/Special:ManageProducts&code={$_GET['code']}");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                    'Accept: application/json'
                ));
                
                //execute post
                $result = curl_exec($ch);

                //close connection
                curl_close($ch);
                
                $data = json_decode($result);
                setcookie('orcid', $data->orcid, time() + 3600);
                setcookie('access_token', $data->access_token, time() + 3600);
                $_COOKIE['orcid'] = $data->orcid;
                $_COOKIE['access_token'] = $data->access_token;
                echo "<script type='text/javascript'>window.close();</script>";
                exit;
            }
        }
	}

}

?>

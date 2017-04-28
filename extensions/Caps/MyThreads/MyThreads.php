<?php
$wgHooks['ToolboxLinks'][] = 'MyThreads::createToolboxLinks';
BackbonePage::register('MyThreads', 'MyThreads', 'network-tools', dirname(__FILE__));

class MyThreads extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(EXTERNAL);
    }
    
    function getTemplates(){
        return array('Backbone/*',
		     'my_threads',
                     'my_threads_row',
		     'thread',
		     'post',
		     'thread_edit'
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'MyThreadsView',
                     'MyThreadsRowView',
		     'ThreadView',
                     'PostView',
		     'ThreadEditView'
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser, $wgLang;
        $me = Person::newFromWgUser();
	    $title = "Ask an Expert";
	    if($wgLang->getCode() == "fr"){
	         $title = "Demandez à un Expert";
	    }

        if($me->isLoggedIn()){
            $toolbox['Other2']['links'][] = TabUtils::createToolboxLink($title, "$wgServer$wgScriptPath/index.php/Special:MyThreads");
        }
        return true;
    }
}

?>

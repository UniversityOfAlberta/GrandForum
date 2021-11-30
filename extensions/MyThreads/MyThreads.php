<?php
$wgHooks['ToolboxLinks'][] = 'MyThreads::createToolboxLinks';
BackbonePage::register('MyThreads', 'MyThreads', 'network-tools', dirname(__FILE__));

class MyThreads extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        global $config;
        $me = Person::newFromWgUser();
        if($config->getValue('networkName') == "GlycoNet"){
            return ($me->isRole(HQP) || $me->isRoleAtLeast(STAFF) || $me->isBoardMod());
        }
        return $me->isRoleAtLeast(HQP);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'my_threads',
                     'board',
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
                     'BoardView',
                     'ThreadView',
                     'PostView',
                     'ThreadEditView'
        );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        $boards = Board::getAllBoards();
        if((new self)->userCanExecute($wgUser) && count($boards) > 0){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Message Boards", "$wgServer$wgScriptPath/index.php/Special:MyThreads");
        }
        return true;
    }
}

?>

<?php

$wgHooks['SkinTemplateNavigation::Universal'][1000] = 'TabUtils::actionTabs';

class TabUtils {

    static $customActions = array();
    
    static function createTab($text, $href="", $selected=false){
        return array('id' => "lnk-".htmlspecialchars($text),
                     'text' => $text,
                     'href' => $href,
                     'selected' => $selected,
                     'subtabs' => array());
    }
    
    static function createSubTab($text, $href="", $selected=false){
        return array('text' => $text,
                     'href' => $href,
                     'selected' => $selected,
                     'dropdown' => array());
    }
    
    static function createToolboxHeader($text){
        return array('text' => $text, 
                     'links' => array());
    }
    
    static function createToolboxLink($text, $href){
        return array('text' => $text,
                     'href' => $href);
    }
    
    static function clearSubTabs($id){
        global $tabs;
        $tabs[$id]['subtabs'] = array();
    }

    static function actionTabs(&$skin, &$content_actions){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $dropdownScript;
        $new_actions = array();
        foreach($content_actions as $key1 => $actions){
            foreach($actions as $key => $action){
                if(strstr($action['class'], 'selected') !== false && !is_numeric($key)){
                    continue;
                }
                if(!is_numeric($key)){
                    $action['class'] = 'action';
                }
                $new_actions[$key1][$key] = $action;
            }
        }
        foreach(self::$customActions as $key => $action){
            $new_actions['actions'][$key] = $action;
        }
        $dropdownScript = "
            $('li.action').wrapAll('<ul class=\'actions\' />');                           
            $('div#submenu ul.actions').dropdown({title: 'Actions',
                                                  width: '125px' 
                                                 });
            $('div#submenu ul.actions').css('padding-right', 0);
            $('div#submenu ul.actions li.actions').css('float', 'right');
        ";
        foreach($content_actions as $key => $content){
            if(isset($content['dropdown'])){
                $dropdownScript .= "createDropDown('{$content['dropdown']['name']}', '{$content['dropdown']['title']}', {$content['dropdown']['width']});";
            }
        }
        
        $content_actions = $new_actions;
        return true;
    }
    
    static function clearTabs($skin, &$content_actions){
        foreach($content_actions as $key => $action){
            unset($action['protect']);
            unset($action['watch']);
            unset($action['unwatch']);
            unset($action['create']);
            unset($action['history']);
            unset($action['delete']);
            unset($action['talk']);
            unset($action['move']);
            unset($action['edit']);
            unset($action['addsection']);
            unset($action['editTemplate']);
            unset($action['Create from template']);
            unset($action['instance list']);
            $content_actions[$key] = $action;
        }
        return true;
    }
    
    /**
     * Adds an action to the sub-menu
     * @param string $text The visible text of the action
     * @param string $href The url of the action
     */
    static function addAction($text, $href){
        self::$customActions[str_replace(" ", "", $text)] = 
                                array('text' => $text,
                                      'href' => $href,
                                      'class' => 'action');
    }
    
    /**
     * Clears most of the built in wiki actions of the sub-menu
     */
    static function clearActions(){
        global $wgHooks;
        $wgHooks['SkinTemplateNavigation'][] = 'TabUtils::clearTabs';
    }

}

?>

<?php

$wgHooks['SkinTemplateNavigation::Universal'][1000] = 'TabUtils::actionTabs';

class TabUtils {

    static $customActions = array();
    
    /**
     * Creates and returns a top level tab that can be appended to the $tabs global
     * \code
     * $GLOBALS['tabs']['Profile'] = TabUtils::createTab("Top Tab");
     * \endcode
     * @param string $text The text that will show up on the tab
     * @param string $href The url that the go to 
     * @param string $selected Whether the tab is currently selected or not (should be "selected" if true)
     * @return array The array of tab information ['id', 'text', 'href', 'selected', 'subtabs']
     */
    static function createTab($text, $href="", $selected=""){
        return array('id' => "lnk-".htmlspecialchars($text),
                     'text' => $text,
                     'href' => $href,
                     'selected' => $selected,
                     'subtabs' => array());
    }
    
    /**
     * Creates and returns a sub-level tab that can be appended to a top level tab in the $tabs global
     * SubTabs can also be appended to the dropdowns of other SubTabs
     * \code
     * $GLOBALS['tabs']['Main']['subtabs'][] = TabUtils::createSubTab("Sub Tab", "{$url}", "$selected");
     * \endcode
     * @param string $text The text that will show up on the tab
     * @param string $href The url that the go to 
     * @param string $selected Whether the tab is currently selected or not (should be "selected" if true)
     * @return array The array of tab information ['text', 'href', 'selected', 'dropdown']
     */
    static function createSubTab($text, $href="", $selected=""){
        return array('text' => $text,
                     'href' => $href,
                     'selected' => $selected,
                     'dropdown' => array());
    }
    
    /**
     * Creates and returns a toolbox header that can be appended to the $toolbox global
     * \code
     * $GLOBALS['toolbox']['Other'] = TabUtils::createToolboxHeader("Header");
     * \endcode
     * @param string $text The text that will show up on the header
     * @return array The array of header information ['text', 'links']
     */
    static function createToolboxHeader($text){
        return array('text' => $text, 
                     'links' => array());
    }
    
    /**
     * Creates and returns a toolbox link that can be appended to a header in the $toolbox global
     * \code
     * $GLOBALS['toolbox']['Other'][] = TabUtils::createToolboxLink("Toolbox Link", "{$url}");
     * \endcode
     * @param string $text The text that will show up on the link
     * @param string $href $href The url that the go to
     * @return array The array of link information ['text', 'href']
     */
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
                if(isset($action['class']) && strstr($action['class'], 'selected') !== false && !is_numeric($key)){
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

<?php

$wgHooks['SkinTemplateContentActions'][1000] = 'TabUtils::actionTabs';

class TabUtils {

    static $customActions = array();
    
    static function createTab($text, $href="", $selected=false){
        return array('id' => "lnk-".htmlspecialchars($text),
                     'text' => $text,
                     'href' => $href,
                     'selected' => $selected,
                     'subtabs' => array());
    }
    
    static function createSubTab($text, $href, $selected=false){
        return array('text' => $text,
                     'href' => $href,
                     'selected' => $selected);
    }
    
    static function createToolboxHeader($text){
        return array('text' => $text, 
                     'links' => array());
    }
    
    static function createToolboxLink($text, $href){
        return array('text' => $text,
                     'href' => $href);
    }

    static function actionTabs(&$content_actions){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut, $dropdownScript;
        $new_actions = array();
        foreach($content_actions as $key => $action){
            if(strstr($action['class'], 'selected') !== false && !is_numeric($key)){
                continue;
            }
            if(!is_numeric($key)){
                $action['class'] = 'action';
            }
            $new_actions[$key] = $action;
        }
        foreach(self::$customActions as $key => $action){
            $new_actions[$key] = $action;
        }
        $dropdownScript = "<script type='text/javascript'>
            function createDropDown(name, title, width){
                $('li.' + name).wrapAll('<ul class=\'' + name + '\'>');
                $('ul.' + name).wrapAll('<li class=\'invisible\'>');
                var selected = false;
                if($('li.' + name).filter('.selected').length >= 1){
                    selected = true;
                }
                $('div#submenu ul.' + name).dropdown({title: title,
                                                       width: width + 'px' 
                                                      });
                if(selected){
                    $('ul.' + name + ' > li').addClass('selected');
                    $('ul.' + name).imgDown();
                }
            }
            
            $('li.action').wrapAll('<ul class=\'actions\' />');                           
            $('div#submenu ul.actions').dropdown({title: 'Actions',
                                                  width: '125px' 
                                                 });
            $('div#submenu ul.actions').css('padding-right', 0);
            $('div#submenu ul.actions li.actions').css('float', 'right');
            
            createDropDown('products', 'Products', 125);
            createDropDown('people', 'People', 75);
            createDropDown('phase2', 'Phase2', 125);";
        foreach($content_actions as $key => $content){
            if(isset($content['dropdown'])){
                $dropdownScript .= "createDropDown('{$content['dropdown']['name']}', '{$content['dropdown']['title']}', {$content['dropdown']['width']});";
            }
        }
        $dropdownScript .= "</script>";
        $content_actions = $new_actions;
        return true;
    }
    
    static function clearTabs($skin, &$content_actions){
        unset($content_actions['protect']);
        unset($content_actions['watch']);
        unset($content_actions['unwatch']);
        unset($content_actions['create']);
        unset($content_actions['history']);
        unset($content_actions['delete']);
        unset($content_actions['talk']);
        unset($content_actions['move']);
        unset($content_actions['edit']);
        unset($content_actions['addsection']);
        unset($content_actions['editTemplate']);
        unset($content_actions['Create from template']);
        unset($content_actions['instance list']);
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
        $wgHooks['SkinTemplateTabs'][] = 'TabUtils::clearTabs';
    }
}

?>

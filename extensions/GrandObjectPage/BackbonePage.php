<?php
$wgHooks['LoadAllMessages'][] = 'BackbonePage::onLoadAllMessages';

/**
 * @package GrandObjectPage
 */
abstract class BackbonePage extends SpecialPage{

    static $messages = array();

    static function onLoadAllMessages(){
        global $wgLang;
        foreach(self::$messages as $key => $message){
            $wgLang->messages[$key] = $message;
        }
        return true;
    }

    /**
     * Registers a BackbonePage so that mediawiki knows about it
     * @param string $class The name of the BackbonePage class
     * @param string $title The title of the BackbonePage
     * @param string $pageGroup The SpecialPage grouping that this BackbonPage will show up under
     */
    static function register($class, $title, $pageGroup) {
        global $wgSpecialPages, $wgSpecialPageGroups, $wgExtensionMessagesFiles, $wgHooks;
        $wgSpecialPages[$class] = $class; # Let MediaWiki know about the special page.
        $dir = dirname(__FILE__) . '/';
        $wgExtensionMessagesFiles[$class] = $dir . 'BackbonePage.i18n.php';
        self::$messages[strtolower($class)] = $title;
        $wgSpecialPageGroups[$class] = $pageGroup;
    }
    
    function BackbonePage(){
        SpecialPage::SpecialPage(get_class($this), INACTIVE.'+', true);
    }
    
    function execute($par){
        global $wgOut;
        $class = get_class($this);
        $wgOut->setPageTitle(self::$messages[strtolower($class)]);
        $this->loadTemplates();
        $this->loadViews();
    }
    
    /**
     * Loads the required templates and adds it to the OutputPage
     */
    function loadTemplates(){
        global $wgOut;
        $views = $this->getViews();
        foreach($views as $view){
            if(file_exists($view)){
                $tpl = file_get_contents($view);
                $exploded = explode("/", $view);
                $name = str_replace(".html", $exploded[count($exploded)-1]);
                $wgOut->addScript("<script type='text/template' id='{$name}_template'>\n$tpl</script>\n");
            }
        }
    }
    
    /**
     * Returns a string array, representing the path + filename of the templates required for this page
     * @return array
     */
    abstract function getTemplates();
    
    /**
     * Loads the required views and adds it to the OutputPage
     */
    function loadViews(){
        global $wgOut;
        $views = $this->getViews();
        foreach($views as $view){
            $wgOut->addScript("<script type='text/javascript' src='{$view}'></script>");
        }
    }
    
    /**
     * Returns a string array, representing the path + filename of the views required for this page
     * @return array
     */
    abstract function getViews();
  
}  
?>

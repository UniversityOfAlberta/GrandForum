<?php
$wgHooks['LoadAllMessages'][] = 'BackbonePage::onLoadAllMessages';

/**
 * @package GrandObjectPage
 */
abstract class BackbonePage extends SpecialPage {

    static $messages = array();
    static $dirs = array();

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
     * @param string $pageGroup The SpecialPage grouping that this BackbonePage will show up under
     * @param string $dir The directory of the class
     */
    static function register($class, $title, $pageGroup, $dir) {
        global $wgSpecialPages, $wgSpecialPageGroups, $wgExtensionMessagesFiles, $wgHooks, $wgMessage;
        try{
            if(!is_dir("$dir/Templates")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing <i>Templates</i> directory");
            }
            if(!is_dir("$dir/Views")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing <i>Views</i> directory");
            }
            if(!file_exists("$dir/routes.js")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing a <i>routes.js</i> file");
            }
            if(!file_exists("$dir/main.js")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing a <i>main.js</i> file");
            }
            $wgSpecialPages[$class] = $class; # Let MediaWiki know about the special page.
            $backboneDir = dirname(__FILE__) . '/';
            $wgExtensionMessagesFiles[$class] = $backboneDir . 'BackbonePage.i18n.php';
            self::$messages[strtolower($class)] = $title;
            self::$dirs[strtolower($class)] = $dir;
            $wgSpecialPageGroups[$class] = $pageGroup;
        }
        catch (Exception $e){
            $wgMessage->addError($e->getMessage());
        }
    }
    
    function BackbonePage(){
        SpecialPage::SpecialPage(get_class($this), INACTIVE.'+', true);
    }
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $class = get_class($this);
        $wgOut->addScript("<style>
                                #bodyContent > h1 { display:none !important; }
                                #pageTitle { margin-top:0; }
                                #contentSub { display:none; }
                           </style>");
        $wgOut->addHTML("<div id='backbone_main'></div>");
        $this->loadRoutes();
        $this->loadTemplates();
        $this->loadModels();
        $this->loadViews();
        $wgOut->addHTML("<script type='text/javascript'>
            main = new Main({title: '".str_replace("'", "&#39;", self::$messages[strtolower($class)])."'});
            mainView = new MainView({el: $('#backbone_main'), model: main}).render();
        </script>\n");
        $this->loadMain();
    }
    
    /**
     * Adds the routes script to the OutputPage
     */
    function loadRoutes(){
        global $wgServer, $wgScriptPath, $wgOut;
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/routes.js'></script>");
    }
    
    /**
     * Adds the main script to the OutputPage
     */
    function loadMain(){
        global $wgServer, $wgScriptPath, $wgOut;
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/main.js'></script>");
    }
    
    /**
     * Loads the required templates and adds it to the OutputPage
     */
    function loadTemplates(){
        global $wgOut, $wgMessage;
        $templates = $this->getTemplates();
        $templates[] = 'main';
        foreach($templates as $template){
            $fileName = self::$dirs[strtolower(get_class($this))]."/Templates/{$template}.html";
            if(file_exists($fileName)){
                $tpl = file_get_contents($fileName);
                $exploded = explode("/", $template);
                $name = $exploded[count($exploded)-1];
                $wgOut->addHTML("<script type='text/template' id='{$name}_template'>\n$tpl</script>\n");
            }
            else if(file_exists(dirname(__FILE__)."/Templates/{$template}.html")){
                $tpl = file_get_contents(dirname(__FILE__)."/Templates/{$template}.html");
                $exploded = explode("/", $template);
                $name = $exploded[count($exploded)-1];
                $wgOut->addHTML("<script type='text/template' id='{$name}_template'>\n$tpl</script>\n");
            }
            else{
                $wgMessage->addWarning("BackbonePage <b>".get_class($this)."</b> is missing <i>$template.html</i>");
            }
        }
    }
    
    /**
     * Returns a string array, representing the filename of the templates required for this page
     * @return array
     */
    abstract function getTemplates();
    
    /**
     * Loads the required views and adds it to the OutputPage
     */
    function loadViews(){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage;
        $views = $this->getViews();
        $views[] = 'MainView';
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        foreach($views as $view){
            $fileName = self::$dirs[strtolower(get_class($this))].'/Views/'.$view.'.js';
            if(file_exists($fileName)){
                $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/{$view}.js'></script>");
            }
            else if(file_exists(dirname(__FILE__)."/Views/{$view}.js")){
                $exploded = explode("extensions/", dirname(__FILE__));
                $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/{$view}.js'></script>");
            }
            else{
                $wgMessage->addWarning("BackbonePage <b>".get_class($this)."</b> is missing <i>$view.js</i>");
            }
        }
    }
    
    /**
     * Returns a string array, representing the filename of the views required for this page
     * @return array
     */
    abstract function getViews();
    
    /**
     * Loads the required models and adds it to the OutputPage
     */
    function loadModels(){
        global $wgOut, $wgServer, $wgScriptPath, $wgMessage;
        $models = $this->getModels();
        $models[] = 'Main';
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        foreach($models as $model){
            $fileName = self::$dirs[strtolower(get_class($this))].'/Models/'.$model.'.js';
            if(file_exists($fileName)){
                $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/{$model}.js'></script>");
            }
            else if(file_exists(dirname(__FILE__)."/Models/{$model}.js")){
                $exploded = explode("extensions/", dirname(__FILE__));
                $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/{$model}.js'></script>");
            }
            else{
                $wgMessage->addWarning("BackbonePage <b>".get_class($this)."</b> is missing <i>$model.js</i>");
            }
        }
    }
    
    /**
     * Returns a string array, representing the filename of the models required for this page
     * @return array
     */
    abstract function getModels();
  
}  
?>

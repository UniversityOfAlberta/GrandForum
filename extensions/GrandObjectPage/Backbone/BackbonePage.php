<?php
$wgHooks['MessagesPreLoad'][] = 'BackbonePage::onLoadAllMessages';

BackbonePage::$dirs['backbone'] = dirname(__FILE__);

/**
 * @package GrandObjectPage
 */
abstract class BackbonePage extends SpecialPage {

    static $messages = array();
    static $dirs = array();
    static $loaded = array();

    static function onLoadAllMessages($title, &$message){
        global $wgLang, $wgContLang, $wgMessageCache;
        foreach(self::$messages as $key => $msg){
            if(strtolower($title) == $key){
                $message = $msg;
            }
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
            if(!file_exists("$dir/routes.js")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing a <i>routes.js</i> file");
            }
            if(!file_exists("$dir/main.js")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing a <i>main.js</i> file");
            }
            if(!file_exists("$dir/helpers.js")){
                throw new Exception("BackbonePage <b>{$class}</b> is missing a <i>helpers.js</i> file");
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
        SpecialPage::__construct(get_class($this), 'NULL', false);
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser;
        if(!$this->userCanExecute($wgUser)){
            permissionError();
        }
        $class = get_class($this);
        $wgOut->addScript("<style>
                                #bodyContent > h1 { display:none !important; }
                                #pageTitle { margin-top:0; }
                                #contentSub { display:none; }
                                #currentViewSpinner {text-align: center; margin-top:10%;}
                           </style>");
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        if(file_exists(self::$dirs[strtolower(get_class($this))]."/style.css")){
            $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/{$exploded[1]}/style.css?".filemtime("extensions/{$exploded[1]}/style.css")."' type='text/css' rel='stylesheet' />");
        }
        $wgOut->addHTML("<div id='backbone_main'></div>");
        $this->loadTemplates();
        $this->loadModels();
        $this->loadHelpers();
        $this->loadViews();
        $wgOut->addHTML("<script type='text/javascript'>
            main = new Main({title: '".str_replace("'", "&#39;", self::$messages[strtolower($class)])."'});
            mainView = new MainView({el: $('#backbone_main'), model: main}).render();
        </script>\n");
        $this->loadRoutes();
        $this->loadMain();
    }
    
    /**
     * Adds the routes script to the OutputPage
     */
    function loadRoutes(){
        global $wgServer, $wgScriptPath, $wgOut;
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/routes.js?".filemtime("extensions/{$exploded[1]}/routes.js")."'></script>\n");
    }
    
    /**
     * Adds the main script to the OutputPage
     */
    function loadMain(){
        global $wgServer, $wgScriptPath, $wgOut;
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/GrandObjectPage/Backbone/main.js?".filemtime(dirname(__FILE__)."/main.js")."'></script>\n");
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/main.js?".filemtime("extensions/{$exploded[1]}/main.js")."'></script>\n");
    }
    
    /**
     * Adds the helpers script to the OutputPage
     */
    function loadHelpers(){
        global $wgServer, $wgScriptPath, $wgOut;
        $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/GrandObjectPage/Backbone/helpers.js?".filemtime(dirname(__FILE__)."/helpers.js")."'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/helpers.js?".filemtime("extensions/{$exploded[1]}/helpers.js")."'></script>");
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
            if(strstr($template, "/") !== false){
                foreach(self::$dirs as $key => $dir){
                    $explodedTemplate = explode("/", $template);
                    $class = $explodedTemplate[0];
                    if(strtolower($class) == $key){
                        if(strstr($template, "/*") !== false){
                            $templateDir = $dir."/Templates";
                            if(is_dir($templateDir)){
                                $files = @scandir($templateDir);
                                foreach($files as $file){
                                    if(strstr($file, ".html") !== false){
                                        $exploded = explode("extensions/", $dir);
                                        if($key != "backbone" || $file != "main.html"){
                                            $fileName = $dir."/Templates/".$file;
                                            if(!isset(self::$loaded["$fileName"])){
                                                $tpl = file_get_contents($fileName);
                                                $name = str_replace(".html", "", $file);
                                                $wgOut->addScript("<script type='text/template' id='{$name}_template'>\n$tpl</script>");
                                                self::$loaded["$fileName"] = true;
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $wgMessage->addWarning("The folder <b>{$templateDir}</b> does not exist");
                            }
                        }
                        else{
                            $dir = self::$dirs[strtolower($explodedTemplate[0])];
                            $exploded = explode("extensions/", $dir);
                            $file = $explodedTemplate[count($explodedTemplate)-1];
                            $fileName = $dir."/Templates/".$file.".html";
                            if(file_exists($fileName)){
                                if(!isset(self::$loaded["$fileName"])){
                                    $tpl = file_get_contents($fileName);
                                    $name = $file;
                                    $wgOut->addScript("<script type='text/template' id='{$name}_template'>\n$tpl</script>");
                                    self::$loaded["$fileName"] = true;
                                }
                            }
                            else{
                                $wgMessage->addWarning("BackbonePage <b>$class</b> is missing <i>$file.html</i>");
                            }
                        }
                        break;
                    }
                }
            }
            else if(file_exists($fileName)){
                if(!isset(self::$loaded["$fileName"])){
                    $tpl = file_get_contents($fileName);
                    $exploded = explode("/", $template);
                    $name = $exploded[count($exploded)-1];
                    $wgOut->addScript("<script type='text/template' id='{$name}_template'>\n$tpl</script>");
                    self::$loaded["$fileName"] = true;
                }
            }
            else if(file_exists(dirname(__FILE__)."/Templates/{$template}.html")){
                if(!isset(self::$loaded[dirname(__FILE__)."/Templates/{$template}.html"])){
                    $tpl = file_get_contents(dirname(__FILE__)."/Templates/{$template}.html");
                    $exploded = explode("/", $template);
                    $name = $exploded[count($exploded)-1];
                    $wgOut->addScript("<script type='text/template' id='{$name}_template'>\n$tpl</script>");
                    self::$loaded[dirname(__FILE__)."/Templates/{$template}.html"] = true;
                }
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
            $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
            $fileName = self::$dirs[strtolower(get_class($this))].'/Views/'.$view.'.js';
            if(strstr($view, "/") !== false){
                foreach(self::$dirs as $key => $dir){
                    $explodedView = explode("/", $view);
                    $class = $explodedView[0];
                    if(strtolower($class) == $key){
                        if(strstr($view, "/*") !== false){
                            $viewDir = $dir."/Views";
                            if(is_dir($viewDir)){
                                $files = @scandir($viewDir);
                                foreach($files as $file){
                                    if(strstr($file, ".js") !== false){
                                        $exploded = explode("extensions/", $dir);
                                        if($key != "backbone" || $file != "MainView.js"){
                                            if(!isset(self::$loaded["{$exploded[1]}/Views/$file"])){
                                                $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/$file?".filemtime($viewDir."/".$file)."'></script>");
                                                self::$loaded["{$exploded[1]}/Views/$file"] = true;
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $wgMessage->addWarning("The folder <b>{$viewDir}</b> does not exist");
                            }
                        }
                        else{
                            $dir = self::$dirs[strtolower($explodedView[0])];
                            $exploded = explode("extensions/", $dir);
                            $file = $explodedView[count($explodedView)-1];
                            if(file_exists($dir."/Views/".$file.".js")){
                                if(!isset(self::$loaded["{$exploded[1]}/Views/$file"])){
                                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/{$file}.js?".filemtime($dir."/Views/".$file.".js")."'></script>");
                                    self::$loaded["{$exploded[1]}/Views/$file"] = true;
                                }
                            }
                            else{
                                $wgMessage->addWarning("BackbonePage <b>$class</b> is missing <i>$file.js</i>");
                            }
                        }
                        break;
                    }
                }
            }
            else if(file_exists($fileName)){
                if(!isset(self::$loaded["{$exploded[1]}/Views/$view"])){
                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/{$view}.js?".filemtime($fileName)."'></script>");
                    self::$loaded["{$exploded[1]}/Views/$view"] = true;
                }
            }
            else if(file_exists(dirname(__FILE__)."/Views/{$view}.js")){
                $exploded = explode("extensions/", dirname(__FILE__));
                if(!isset(self::$loaded["{$exploded[1]}/Views/$view"])){
                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Views/{$view}.js?".filemtime(dirname(__FILE__)."/Views/{$view}.js")."'></script>");
                    self::$loaded["{$exploded[1]}/Views/$view"] = true;
                }
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
        foreach($models as $model){
            $exploded = explode("extensions/", self::$dirs[strtolower(get_class($this))]);
            $fileName = self::$dirs[strtolower(get_class($this))].'/Models/'.$model.'.js';
            if(strstr($model, "/") !== false){
                foreach(self::$dirs as $key => $dir){
                    $explodedModel = explode("/", $model);
                    $class = $explodedModel[0];
                    if(strtolower($class) == $key){
                        if(strstr($model, "/*") !== false){
                            $modelDir = $dir."/Models";
                            if(is_dir($modelDir)){
                                $files = @scandir($modelDir);
                                foreach($files as $file){
                                    if(strstr($file, ".js") !== false){
                                        $exploded = explode("extensions/", $dir);
                                        if($key != "backbone" || $file != "Main.js"){
                                            if(!isset(self::$loaded["{$exploded[1]}/Models/$file"])){
                                                $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/$file?".filemtime($modelDir."/".$file)."'></script>");
                                                self::$loaded["{$exploded[1]}/Models/$file"] = true;
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $wgMessage->addWarning("The folder <b>{$modelDir}</b> does not exist");
                            }
                        }
                        else{
                            $dir = self::$dirs[strtolower($explodedModel[0])];
                            $exploded = explode("extensions/", $dir);
                            $file = $explodedModel[count($explodedModel)-1];
                            if(file_exists($dir."/Models/".$file.".js")){
                                if(!isset(self::$loaded["{$exploded[1]}/Models/$file"])){
                                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/{$file}.js?".filemtime($dir."/Models/".$file.".js")."'></script>");
                                    self::$loaded["{$exploded[1]}/Models/$file"] = true;
                                }
                            }
                            else{
                                $wgMessage->addWarning("BackbonePage <b>$class</b> is missing <i>$file.js</i>");
                            }
                        }
                        break;
                    }
                }
            }
            else if(file_exists($fileName)){
                if(!isset(self::$loaded["{$exploded[1]}/Models/{$model}"])){
                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/{$model}.js?".filemtime($fileName)."'></script>\n");
                    self::$loaded["{$exploded[1]}/Models/$model"] = true;
                }
            }
            else if(file_exists(dirname(__FILE__)."/Models/{$model}.js")){
                $exploded = explode("extensions/", dirname(__FILE__));
                if(!isset(self::$loaded["{$exploded[1]}/Models/{$model}"])){
                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/{$exploded[1]}/Models/{$model}.js?".filemtime(dirname(__FILE__)."/Models/{$model}.js")."'></script>");
                    self::$loaded["{$exploded[1]}/Models/$model"] = true;
                }
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

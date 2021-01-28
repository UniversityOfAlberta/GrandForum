<?php

// Sort of re-implements the old UnknownAction hook
class UnknownAction extends Action {
    
    static $unknownActions = array();
    
    static function createAction($fn){
        self::$unknownActions[] = $fn;
    } 
    
    public function getName(){
        return "nosuchaction";
    }
    
    public function show(){
        $name = $_GET['action'];
        foreach(self::$unknownActions as $action){
            if(is_array($action)){
                $obj = $action[0];
                $fn = $action[1];
                $ret = $obj->{$fn}($name, $this->getArticle());
            }
            else{
                eval("\$ret = $action('$name', \$this->getArticle());");
            }
            if(!$ret){
                return;
            }
        }
        // Action not found
        throw new ErrorPageError( $this->msg( 'nosuchaction' ), $this->msg( 'nosuchactiontext' ) );
    }
    
}

$wgActions['nosuchaction'] = "UnknownAction";

?>

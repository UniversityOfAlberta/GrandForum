<?php

/**
 * @package API
 */
abstract class RESTAPI extends API {

    var $params = array();
    
    function processRequest($params=null){
		global $wgUser;
		if(isset($_GET['getHelp'])){
			$this->getHelp();
		}
		else{
			if($this->isLoginRequired() && !$wgUser->isRegistered()){
                header("HTTP/1.0: 403 Authentication Required");
                close();
            }
			$this->processParams($params);
		    $this->doAction();
		}
	}
	
	/**
	 * Returns the value of the specified parameter if it exists ("" otherwise)
	 * @param string $id The id of the parameter
	 * @return string Returns the value of the parameter
	 */
	function getParam($id){
	    return (isset($this->params[$id])) ? $this->params[$id] : "";
	}
	
	/**
	 * In most cases for the RESTAPI login should not be required.
	 * This should instead be implemented for each of the doACTION functions.
	 */
	function isLoginRequired(){
        return false;
    }
	
	/**
	 * Generates a error message via the HTTP 400 return code, and exits execution
	 * @param string $message The message to display
	 * @param int $code the HTTP error code
	 */
	function throwError($message, $code=400){
	    header("HTTP/1.0: $code $message");
	    echo $message;
	    close();
	}
    
    function doAction(){
        global $wgUser;
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "GET" || $method == "HEAD"){
            $json = $this->doGET();
        }
        else if($method == "PUT" || ($method == "POST" && @$_POST['_method'] == "PUT")){
            $json = $this->doPUT();
            DBFunctions::commit();
        }
        else if($method == "DELETE" || ($method == "POST" && @$_POST['_method'] == "DELETE")){
            $json = $this->doDELETE();
            DBFunctions::commit();
        }
        else if($method == "POST"){
            $json = $this->doPOST();
            DBFunctions::commit();
        }
        header('Content-Type: application/json');
        ob_start("ob_gzhandler");
        echo $json;
        close();
    }
    
    function processParams($params){ }
    
    /**
     * CREATE/POST
     * @abstract
     */
    abstract function doPOST();
    
    /**
     * READ/GET
     * @abstract
     */
    abstract function doGET();

    /**
     * UPDATE/PUT
     * @abstract
     */
    abstract function doPUT();
    
    /**
     * DELETE/DELETE
     * @abstract
     */
    abstract function doDELETE();
    
}

?>

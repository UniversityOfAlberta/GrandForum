<?php

function initValidations(){
    global $formValidations, $validations;
    $formValidations = array('NOTHING'  => 'NothingValidation',
                             'NULL'     => 'NullValidation',
                             'NOSPACES' => 'NoSpacesValidation',
                             'NUMERIC'  => 'NumericValidation',
                             'PERCENT'  => 'PercentValidation',
                             'PERSON'   => 'PersonValidation',
                             'NI'       => 'NIValidation',
                             'EMAIL'    => 'EmailValidation');
    $i = 0;
    foreach($formValidations as $key => $validation){
        define('VALIDATE_'.$key, pow(2, ($i)*2));
        define('VALIDATE_NOT_'.$key, pow(2, ($i)*2 + 1));
        $validations[pow(2, ($i)*2)] = $validation;
        $validations[pow(2, ($i)*2 + 1)] = $validation;
        $i++;
    }
}

initValidations();

/**
 * This class is to help make creating forms easier to make,
 * by reducing the amount of code (and code duplication) required
 * on Special pages.  This class will allow for automatic cleanup of POST variables, 
 * as well as simple validation checks
 */
 
require_once("UIElementArray.php");
require_once("UIValidation.php");

autoload_register('UI/Arrays');
autoload_register('UI/Elements');
autoload_register('UI/Validations');

abstract class UIElement {
    
    var $parent;
    var $id;
    var $name;
    var $value;
    var $default;
    var $tooltip;
    var $validations;
    var $extraValidations;
    var $attr;
    
    function __construct($id, $name, $value, $validations){
        $this->parent = null;
        $this->id = $id;
        $this->name = $name;
        $this->attr = array();
        $this->default = $this->clearValue($value);
        if(isset($_POST[str_replace("[]", "", $this->id)])){
            $this->value = $this->clearValue($_POST[str_replace("[]", "", $this->id)]);
        }
        else{
            $this->value = $this->clearValue($value);
        }
        $this->validations = $validations;
        $this->extraValidations = array();
    }
    
    private function clearValue($value){
        if(is_array($value)){
            $newValue = array();
            foreach($value as $key => $v){
                $v = $this->clearValue($v);
                $newValue[$key] = $v;
            }
            $value = $newValue;
        }
        else{
            $value = str_replace("'", "&#39;", trim($value));
        }
        return $value;
    }

    /**
     * Returns this UIElement's parent
     * @return UIElement this UIElement's parent
     */
    function parent(){
        return $this->parent;
    }
    
    /**
     * Inserts another UIElement before this UIElement
     * @param UIElement $element The UIElement to insert
     */
    function insertBefore($element){
        if($this->parent() != null){
            $this->parent()->insertBefore($element, $this->id);
        }
    }
    
    /**
     * Inserts another UIElement before this UIElement
     * @param UIElement $element
     */
    function insertAfter($element){
        if($this->parent() != null){
            $this->parent()->insertAfter($element, $this->id);
        }
    }
    
    /**
     * Removes this UIElement from it's parent
     */
    function remove(){
        if($this->parent() != null){
            $this->parent()->remove($this->id);
        }
    }
    
    /**
     * Hides the html in the dom for this UIElement
     */
    function hide(){
        $this->attr('style', 'display:none;');
    }
    
    // Sets the value of an attribute
    // If $value is null, the value of the attr is instead returned
    /**
     * Sets the value of an attribute
     * If the $value is null, the value of the attr is instead returned
     * @param string $attr The attribute to set/return
     * @param string $value The value of the attribute
     * @return Returns the value of the attribute
     */
    function attr($attr, $value=null){
        if($value === null){
            if(isset($this->attr[$attr])){
                return $this->attr[$attr];
            }
            else{
                return "";
            }
        }
        else{
            $this->attr[$attr] = $value;
            return $this;
        }
    }
    
    /**
     * Returns a string for the attributes as html attributes
     * @return string The attributes as html attributes
     */
    protected function renderAttr(){
        $str = "";
        if(count($this->attr) > 0){
            foreach($this->attr as $attr => $value){
                $str .= "{$attr}='{$value}' ";
            }
        }
        return $str;
    }
    
    abstract function render();
    
    /**
     * Resets the UIElement's value to the default, and unsets the $_POST variable's index
     */
    function reset(){
        if(isset($_POST[$this->id])){
            unset($_POST[$this->id]);
        }
        $this->value = $this->default;
    }
    
    /**
     * Registers a validation for this UIElements
     * @param UIValidation $validation The UIValidation to use
     */
    function registerValidation($validation){
        if($validation instanceof UIValidation){
            $this->extraValidations[] = $validation;
        }
    }
    
    /**
     * Returns an array containing all the failed validations
     * if $value is false, then use the $this->value, otherwise use $value
     * @param boolean $value Whether or not to use $this->value or $value
     * @return array An Array containing all the failed validations
     */
    function validate($value=false){
        global $validations, $wgMessage;
        $fails = array();
        if($value === false){
            if(is_array($this->value)){
                if($this->isValidationSet(VALIDATE_NOT_NULL)){
                    $validation = new NullValidation(VALIDATION_NEGATION, VALIDATION_ERROR);
                    if(!$validation->validate($this->value)){
                        $fails[] = $validation->getMessage($this->name);
                    }
                }
                foreach($this->value as $value){
                    $fails = array_merge($fails, $this->validate($value));
                }
            }
            else{
                $fails = $this->validate($this->value);
            }
            $result = true;
            foreach($fails as $fail){
                if(isset($fail['warning'])){
                    if(isset($_POST['ignore_warnings'])){
                        // User has pressed the Ignore button
                        continue;
                    }
                    $wgMessage->addWarning($fail['warning']);
                    $postArr = array();
                    foreach($_POST as $key => $post){
                        if(is_array($post) && count($post) > 0){
                            foreach($post as $k => $p){
                                $p = str_replace("'", "&#39;", trim($p));
                                $postArr[] = "<input type='hidden' name='{$key}[]' value='{$p}' />";
                            }
                        }
                        else{
                            $post = str_replace("'", "&#39;", trim($post));
                            $postArr[] = "<input type='hidden' name='$key' value='{$post}' />";
                        }
                    }
                    $wgMessage->addWarning("<form action='' method='post' enctype='multipart/form-data'>
                        <br />Do you still want to continue with the submission?<br />
                        <div style='display:none;'>".implode("", $postArr)."</div>
                        <input type='submit' name='ignore_warnings' value='Yes' /> <button onClick='closeParent($(this).parent().parent());return false;'>Cancel</button>
                    </form>", 100);
                    $result = false;
                }
                else{
                    $wgMessage->addError($fail['error']);
                    $result = false;
                }
            }
            if(count($wgMessage->errors) > 0){
                unset($wgMessage->warnings[100]);
            }
            return $result;
        }

        foreach($validations as $key => $val){
            if($this->isValidationSet($key)){
                $neg = (log($key, 2) % 2 == 1);
                $type = $val;
                $validation = new $type($neg);
                $result = $validation->validate($value);
                if(!$result){
                    $fails[] = $validation->getMessage($this->name);
                }
            }
        }
        // Extra Validations
        if(count($this->extraValidations) > 0){
            foreach($this->extraValidations as $validation){
                $result = $validation->validate($value);
                if(!$result){
                    $fails[] = $validation->getMessage($this->name);
                }
            }
        }
        return $fails;
    }
    
    /**
     * Sets the specified POST value to this UIElement's value
     * (used for preparing API calls)
     * @param string $index The POST value to set
     */
    function setPOST($index){
        if(is_array($this->value)){
            foreach($this->value as $key => $value){
                $_POST[$index][$key] = $value;
            }
        }
        else{
            $_POST[$index] = $this->value;
        }
    }
    
    /**
     * Checks whether a validation is set or not
     * @param int $validation Which validation to check for (use constants ie. VALIDATE_NOSPACES)
     * @return boolean Whether or not the validation is set or not
     */
    function isValidationSet($validation){
        return (($this->validations & $validation) !== 0);
    }
}

?>

<?php
define('VALIDATE_NOTHING', 0);
define('VALIDATE_NOT_NULL', 1);
define('VALIDATE_IS_NUMERIC', 2);
define('VALIDATE_IS_PERCENT', 4);
define('VALIDATE_IS_PROJECT', 8);
define('VALIDATE_IS_NOT_PROJECT', 16);
define('VALIDATE_IS_PERSON', 32);
define('VALIDATE_IS_NOT_PERSON', 64);

/*
 * This class is to help make creating forms easier to make,
 * by reducing the amount of code (and code duplication) required
 * on Special pages.  This class will allow for automatic cleanup of POST variables, 
 * as well as simple validation checks
 */
 
require_once("UIElementArray.php");

autoload_register('UI/Arrays');
autoload_register('UI/Elements');

abstract class UIElement {
    
    var $parent;
    var $id;
    var $name;
    var $value;
    var $default;
    var $tooltip;
    var $validations;
    
    function UIElement($id, $name, $value, $validations){
        $this->parent = null;
        $this->id = $id;
        $this->name = $name;
        $this->default = $this->clearValue($value);
        if(isset($_POST[$this->id])){
            $this->value = $this->clearValue($_POST[$this->id]);
        }
        else{
            $this->value = $this->clearValue($value);
        }
        $this->validations = $validations;
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
    
    // Returns this UIElement's parent
    function parent(){
        return $this->parent;
    }
    
    // Inserts $element before this UIElement
    function insertBefore($element){
        if($this->parent() != null){
            $this->parent()->insertBefore($element, $this->id);
        }
    }
    
    // Inserts $element after this UIElement
    function insertAfter($element){
        if($this->parent() != null){
            $this->parent()->insertAfter($element, $this->id);
        }
    }
    
    // Removes this UIElement from it's parent
    function remove(){
        if($this->parent() != null){
            $this->parent()->remove($this->id);
        }
    }
    
    abstract function render();
    
    // Resets the UIElements value to the default, and unsets the $_POST variable's index
    function reset(){
        if(isset($_POST[$this->id])){
            unset($_POST[$this->id]);
        }
        $this->value = $this->default;
    }
    
    // Returns an array containing all the failed validations
    // if $value is false, then use the $this->value, otherwise use $value
    function validate($value=false){
        $fails = array();
        if($value === false){
            if(is_array($this->value)){
                foreach($this->value as $value){
                    $fails = array_merge($fails, $this->validate($value));
                }
            }
            else{
                $fails = $this->validate($this->value);
            }
            return $fails;
        }
        
        if($this->isValidationSet(VALIDATE_NOT_NULL)){
            $result = $this->validateNotNull($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must not be empty";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_NUMERIC)){
            $result = $this->validateIsNumeric($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must be a valid number";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_PERCENT)){
            $result = $this->validateIsPercent($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must be a valid percent";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_PROJECT)){
            $result = $this->validateIsProject($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must be a valid Project (value used: $value)";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_NOT_PROJECT)){
            $result = !$this->validateIsProject($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must not be an already existing Project (value used: $value)";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_PERSON)){
            $result = $this->validateIsPerson($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must be a valid Person (value used: $value)";
            }
        }
        if($this->isValidationSet(VALIDATE_IS_NOT_PERSON)){
            $result = !$this->validateIsPerson($value);
            if(!$result){
                $fails[] = "The field '".ucfirst($this->name)."' must not be an already existing Person (value used: $value)";
            }
        }
        return $fails;
    }
    
    // Sets the specified POST value to this UIElement's value
    // (used for preparing API calls)
    function setPOST($index){
        if(is_array($this->value)){
            foreach($this->value as $key => $value){
                $_POST[$index][$key] = mysql_real_escape_string($value);
            }
        }
        else{
            $_POST[$index] = mysql_real_escape_string($this->value);
        }
    }
    
    function isValidationSet($validation){
        return (($this->validations & $validation) !== 0);
    }
    
    function validateNotNull($value){
        
        return !($value == null || $value == "");
    }
    
    function validateIsNumber($value){
        return (!$this->validateNotNull($value) || is_numeric($value));
    }
    
    function validateIsPercent($value){
        return (!$this->validateNotNull($value) || (is_numeric($value) && $value >= 0 && $value <= 100));
    }
    
    function validateIsProject($value){
        $project = Project::newFromName($value);
        return ($project != null && $project->getName() != "");
    }
    
    function validateIsPerson($value){
        $person = Person::newFromNameLike($value);
        return ($person != null && $person->getName() != "");
    }
}

?>

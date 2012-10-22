<?php
/** 
 * Contains the class Template.
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey
 */	

/** 
 * Class to represent a template instance and convert it to WikiText. 
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey
 */
class Template {
  /**
   * The name of the template.  Must match an existing page in the Template namespace.
   * @var string
   */
  var $mName = '';
  
  /**
   * An associative array of template fields and their current value for this instance.
   * @var array 
   */
  var $mVars = array();
  
  /**
   * An associative array used to store information for fields that do not exist in the Template.
   * @var array 
   */
  var $mInvalidVars = array();
  
  /**
   * If there is more than one instance of a Template on a page, this value represents its unique index identifier.  Defaults to 0.
   * @var int 
   */
  var $mInstance = 0;

  /**
   * Create a new Template.
   * @param string $name The name of the template, which must be the same as the page in the Template namespace.
   */ 
  public function __construct($name){
    $this->mName = $name;
  }

  /**
   * Generates a string representation of the template instance.  Used for debugging purposes.
   *
   * @return string The string representation of the template instance.
   */
  public function __toString(){
    return $this->mName.' ('.$this->mInstance.'): '.print_r($this->mVars, true).' /// '.print_r($this->mInvalidVars, true);
  }

  /** 
   * Creates and returns a WikiText representation of the Template. 
   * @param bool $includeInvalids If true, include variables that have been added to the template 
   * instance but are not included in the Template page of which this object is an instance.
   * @return string The WikiText representing a template instance, complete with variable values.
   */
  public function toWikiText($includeInvalids = false){
    $wikiText = '{{'.$this->mName."\n";
    foreach ($this->mVars as $var => $value){
      //if ($value != '')
      $wikiText .= "|${var} = $value\n";
    }

    if ($includeInvalids){
      foreach ($this->mInvalidVars as $var => $value){
	//if ($value != '')
	$wikiText .= "|${var} = $value\n";
      }
    }
    
    $wikiText .= '}}';

    return $wikiText;
  }

  /** 
   * Reads the variables that are used in this template from the page in the Template namespace,
   * creates the mVars associative array with the variable names as keys and empty default values.
   */
  public function populateVars(){
    $vars = TemplateFunctions::getTemplateVariables($this->mName);
    
    foreach ($vars as $var){
      $this->mVars[$var] = '';
    }
  }
}



?>
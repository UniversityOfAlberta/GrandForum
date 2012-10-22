<?php
$editor = new TemplateEditor();
$wgHooks['EditPage::showEditForm:initial'][] = array($editor, 'updateFromRequest');
$wgHooks['EditPage::showEditForm:fields'][] = array($editor, 'showCustomFields');
$wgHooks['EditFilter'][] = $editor;
define ( "TEXTFIELD", 0 );
define ( "TEXTAREA", 1 );
define("EX_TEMPLATE_EDITOR", true);

$wgExtensionCredits['other'][] = array(
				       'name' => 'TemplateEditor',
				       'author' =>'UofA: SERL',
				       //'url' => 'http://www.mediawiki.org/wiki/User:JDoe',
				       'description' => 'Provides an easy-to-use interface for creating and editing template-based pages.'
				       );


/**
 * This class is responsible for allowing pages that use templates to be edited using a series of custom edit fields rather than the regular mediawiki edit box
 * Right now it is a bit too SRN specific and can't really be used for any template.
 * TODO: make it more generic and more flexible
 * TODO: reorganize the methods a bit (maybe extract some to a different class?)
 * TODO: make it work with multiple templates used on the same page
 * @author Veselin Ganev
 */

class TemplateEditor {
	/**
	 * Associative array holding information about how each of the template variables should be handled by the template editor.
	 * Form is (variableName => (curValue => (string) editable => (true/false), type => (textfield/textarea/etc.), errors => array(), validate = array(validateFunctions))
	 * 		if editable is false then no form field will be shown for that variable
	 * 		errors is an array containing any errors in the current request
	 * 		validate is an array to validation functions all of which will be called with the field text as first argument
	 * 			if the text is valid those functions should return true
	 * 			otherwise they should return a string that says what the problem is (this will be shown in red beside the textfield)
	 *
	 * @var array
	 */
	private $fieldProperties = array ( );
	
	/**
	 * The default settings for a new field. Can be overriden after the fieldProperties variable has been initialized.
	 *
	 * @var unknown_type
	 */
	private $defaultField = array ('curValue' => "", 'editable' => true, 'type' => 0, 'errors' => array ( ), 'validate' => array ( ) );
	
	
/**
 * Checks if the custom editor is needed
 * TODO: make this method more generic and flexible
 * @param Title $title
 */
	
private static function isEditorNeeded($title) {	
	$ns = $title->getNamespace ();
	
	//for now we check if it the namespace is any of the SRN custom namespaces; this is obviously not generic enough
	if ($ns == NS_PAPER || $ns == NS_RESEARCHER || $ns == NS_ORGANIZATION || $ns == NS_CONFERENCE) {
		return true;
	}
	
	return false;
}

/**
 * sets the textifelds types and restrictions for SRN editing 
 *
 * @param Title $title
 */

private function fixSRNTypes($title) {
	$ns = $title->getNamespace ();
	
	$nonblanks = array("Name", "Title", "Year");
	
	foreach ($nonblanks as $nonblank) {
		if (array_key_exists($nonblank, $this->fieldProperties)) {
			$this->addValidation($nonblank, "cannotBeEmpty");
		}
	}
	if ($ns == NS_PAPER) {	
		$this->setElementType ( "Abstract", TEXTAREA );
	} else if ($ns == NS_RESEARCHER) {
		$this->setElementType ( "About", TEXTAREA );
	}
}

/**
 * Adds a validation function for the given field name
 *
 * @param string $fieldName
 * @param string (callable function) $function
 */
private function addValidation ($fieldName, $function) {
	$this->fieldProperties[$fieldName]['validate'][] = $function;
}

/**
 * Updates the template call in the given text to have the current values 
 *
 * @param string $currentText
 * @return string
 */

private function updateTemplateCall($currentText) {
	foreach ( $this->fieldProperties as $varName => $prop ) {
		$varValue = $prop ['curValue'];
		$currentText = preg_replace ( "/\|$varName=(.*)\n/", "|$varName=$varValue\n", $currentText );
	}
	
	return $currentText;
		
}

/**
 * update editPage->textbox1 from request values if this is a preview
		should actually replace the existing template call with the one formed by the edit fields
			for now assume that the call was complete initially
		it will also update the errors arrays of the fields if needed
 *
 * @param EditPage $editPage
 */
public function updateFromRequest(&$editPage) {
	if (!TemplateEditor::isEditorNeeded($editPage->mTitle))
		return true;
	//for now assume that editBox must contain a valid template call...
	$this->parseTemplateCall($editPage->textbox1); //initializes the field properties
	$this->fixSRNTypes($editPage->mTitle);
	if ($this->parseWgRequest()) {	
		$editPage->textbox1 = $this->updateTemplateCall($editPage->textbox1); 
	}
	
	return true;
	
}

/**
 * called by EditPage::showEditForm:fields. Shows the custom fields for this template .
 *
 * @param EditPage $editPage
 * @return boolean
 */
public function showCustomFields($editPage) {
	if (!TemplateEditor::isEditorNeeded($editPage->mTitle)) {
		return true;
	}
	
	$this->validate();
	$editPage->editFormTextBeforeContent = $this->getFormElements();
	//TODO hide main box
	return true;
}

/**
 * Called by the hook EditFilter before the page is updated. Lets mediawiki know if it should proceed with saving the page or if there
 * are any errors in the custom fields.
 *
 * @param EditPage $editPage
 * @param unknown_type $text (not used)
 * @param unknown_type $section (not used)
 * @param string $error
 * @return boolean
 */

public function onEditFilter($editPage, $text, $section, $error) {
	if (!TemplateEditor::isEditorNeeded($editPage->mTitle)) {
		return true;
	}
	$this->updateFromRequest($editPage); // I guess the ::initial hook doesnt get called in this case??
	if (!$this->validate()) {
		//TODO also show the error when previewing?
		$error .= "<p class='error'>There were some errors while processing the form you submitted. Please see below for more information</p>";
		//by setting $error to something it will cause mediawiki to display the edit form again (with our custom fields)
		//the actual errors will be shown beside the textfields 	
	}
	
	return true;
	
}

/**
 * validates the current values of the fields
 * if there are any errors then the error fields of the relevant fields will be populated so that the errors can be shown beside the textfields 
 *
 * @return true if there are no errors
 */
function validate() {
	$result = true;
	foreach ($this->fieldProperties as &$prop) {
		foreach ($prop['validate'] as $validationFunction) {
			$validate = $this->$validationFunction($prop['curValue']) ;
			if ($validate !== true) {
				$result = false;
				$prop['errors'][] = $validate; 
			}
		}
	}
	return $result;
}

/**
 * a simple validation function that ensures that the field is not empty
 *
 * @param string $text
 * @return mixed (true if it is not empty and an error message if it is)
 */

private static function cannotBeEmpty($text) {
	if (trim($text) != "")
		return true;
	else
		return "Cannot be blank.";
}

/**
 * Parses the mediawiki $wgRequest and updates the state of the editor with any of the values in the request.
 * This is needed for previews and for when there are errors in any of the fields (so that the user doesn't have to retype everything...)
 *
 * @return boolean
 */
private function parseWgRequest() {
	global $wgRequest;
	
	if (!$wgRequest->wasPosted())
		return false;
	//assumes that $fieldProperties already contains an element for all the template's variables
	foreach ($this->fieldProperties as $fieldName => &$props) {
		$props['curValue'] = $wgRequest->getText($fieldName);
	}
	  
	return true;
}
	/**
	 * Parses an existing template call and fill in the $fieldProperties variable
	 *
	 * @param string $templateCall the wiki template call
	 */
	public function parseTemplateCall($templateCall) {
		$vars = TemplateEditor::extractTemplateVariables ( $templateCall );
		//fill in each template variable with the default behaviour
		foreach ( $vars as $varName => $varValue ) {
			$this->fieldProperties [$varName] = $this->defaultField;
			$this->fieldProperties [$varName] ['curValue'] = trim ( $varValue );
		}
	}
	
	/**
	 * Changes the type of the given element
	 *
	 * @param string $elementName
	 * @param int $newType
	 */
	public function setElementType($elementName, $newType) {
		$this->fieldProperties [$elementName] ['type'] = $newType;
		//temp. for testing
		//$this->fieldProperties [$elementName] ['errors'] [] = "Cannot be blank!";
	}
	/**
	 * returns an assoc array of the structure (templateVarName => templateVarValue) 
	 *
	 * @param string $templateCall the text of the template call
	 */
	private static function extractTemplateVariables($templateCall) {
		$templateArray = array ( );
		$matches = array ( );
		
		preg_match_all ( "/\|(.*)=(.*)\n/i", $templateCall, $matches );
		for($i = 0; $i < count ( $matches [1] ); $i ++) {
			$templateVarName = $matches [1] [$i];
			$templateVarValue = $matches [2] [$i];
			$templateArray [$templateVarName] = $templateVarValue;
		}
		return $templateArray;
	}
	
	/**
	 * Converts the fields in $fieldProperties to HTML form elements
	 *
	 * @return string containing the form elements
	 */
	public function getFormElements() {
		//TODO use Xml:: functions instead; use CSS instead of table?
		$fieldFormat = "<tr><td>%s&nbsp;&nbsp;&nbsp;</td><td><input type='field' name='%s'value='%s' size='80'/><font color='red'>&nbsp;&nbsp;%s</font></td></tr><tr><td>&nbsp;</td></tr>\n";
		$textAreaFormat = "<tr><td>%s&nbsp;&nbsp;&nbsp;</td><td><TEXTAREA name='%s' rows='6' cols='80'>%s</TEXTAREA><font color='red'>&nbsp;&nbsp;%s</font></td></tr>\n";
		
		$ret = "<table>";
		
		foreach ( $this->fieldProperties as $fieldName => $prop ) {
			$format = $fieldFormat;
			if ($prop ['type'] == TEXTAREA) {
				$format = $textAreaFormat;
			}
			
			$ret .= sprintf ( $format, $fieldName, $fieldName, $prop ['curValue'], $this->getError ( $fieldName ) );
		}
		
		$ret .= "</table><p><hr>";
		return $ret;
	}
	
	/**
	 * Checks if the given field name has any errors and if it does - returns the first one; if not - returns empty string
	 *
	 * @param string $fieldName
	 * @return string containing the first error (if there is one); empty string if there are no errors
	 */
	private function getError($fieldName) {
		$errors = $this->fieldProperties [$fieldName] ['errors'];
		if (count ( $errors ) > 0) {
			return $errors [0];
		} else {
			return "";
		}
	}
}

?>

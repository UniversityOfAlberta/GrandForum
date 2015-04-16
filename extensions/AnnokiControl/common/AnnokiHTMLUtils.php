<?php 
/** 
 * Convenience class for creating HTML entities.
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */	

/** 
 * Functions that can be used for creating custom HTML useful to Annoki. 
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */
class AnnokiHTMLUtils {
	/**
	 * The option representing 'no choice' in a dropdown list.
	 * @var string 
	 */
  const no_option = '--None--';

  /**
   * Create a dropdown list.  This differs from Xml::listDropDown in that dropdowns made using 
   * Xml::listDropDown place an empty item in the list; this will not.  Also allows for a custom 
   * 'other' choice.
   * @param array $contents An array of the options to be listed in the dropdown.  The values int he array will be used for both option name and form submission value.
   * @param int $id The name and id of the 'select' HTML element.
   * @param string $selected The item to be selected by default (if any).
   * @param string $noOption The 'other' option; can be AnnokiHTMLUtils::no_option.
   * @param string $label A label to apply to the content list.
   * @return string HTML representing a dropdown list.
   */
  static function makeDropdown($contents, $id, $selected=false, $noOption=false, $label=false){
    $out = Xml::openElement( 'select', array('id'=>$id, 'name'=>$id));
    if ($noOption)
      $out .= Xml::option($noOption, $noOption);
    if ($label)
      $out .= Xml::openElement('optgroup', array('label'=>$label));

    foreach ($contents as $option){
      $option = trim($option);
      if ($option == '')
	continue;
      $out .= Xml::option($option, $option, $selected==$option);
    }

    if ($label)
      $out .= Xml::closeElement('optgroup');
    $out .= Xml::closeElement('select');

    return $out;
  }

  /**
   * Create a dropdown list.  This differs from Xml::listDropDown in that dropdowns made using 
   * Xml::listDropDown place an empty item in the list; this will not.  Also allows for a custom 
   * 'other' choice.
   * @param array $contents An associative array of the [id => name] to be listed in the dropdown.  The name will be displayed in the list, and the id will be used as the form submission.
   * @param int $id The name and id of the 'select' HTML element.
   * @param string $selected The item to be selected by default (if any).
   * @param string $noOption The 'other' option; can be AnnokiHTMLUtils::no_option.
   * @param string $label A label to apply to the content list.
   * @return string HTML representing a dropdown list.
   */
  static function makeDropdownWithIDs($contents, $id, $selected=false, $noOption=false, $label=false){
    $out = Xml::openElement( 'select', array('id'=>$id, 'name'=>$id));
    if ($noOption)
      $out .= Xml::option($noOption, $noOption);
    if ($label)
      $out .= Xml::openElement('optgroup', array('label'=>$label));

    foreach ($contents as $id => $option){
      $option = trim($option);
      if ($option == '')
	continue;
      $out .= Xml::option($option, $id, $selected==$option);
    }

    if ($label)
      $out .= Xml::closeElement('optgroup');
    $out .= Xml::closeElement('select');

    return $out;
  }

  /**
   * Create a selection list.
   * @param array $contents Either an array of elements, or an associative array of [id => element].  Set $contentsHaveIds accordingly.
   * @param string $id The id of the selection list.
   * @param boolean $contentsHaveIds True if $contents is an associative array of [id => element], false if it is a simple array of elements.
   * $param mixed $selectedItem Either the element to be selected, or false.
   * $param boolean $multiple True if multiple items can be selected in the list at once, or false otherwise.
   * @param int $maxSize The maximum size of the selection list.
   * @param mixed $style Either false, or a string describing the element's style attribute.
   * @param string $onChange The onchange attribute value for the selection list.
   * @return string The HTML corresponding to the selection list.
   */
  static function makeSelector($contents, $id, $contentsHaveIds=false, $selectedItem=false, $multiple=false, $maxSize=15, $style=false, $onChange=''){
      $size = (count($contents) > $maxSize ? $maxSize : count($contents));

      $selectorAttribs = array(
			       'id' => $id,
			       'name' => $id,
			       'size' => $size,
			       'onchange' => $onChange,
			       );

      if ($multiple)
	  $selectorAttribs['multiple'] = 'true';

      if ($style)
	  $selectorAttribs['style'] = $style;

      $html = Xml::openElement('select', $selectorAttribs)."\n";

      if ($contentsHaveIds){
	  foreach ($contents as $id => $item)
	      $html .= Xml::option($item, $id, $item == $selectedItem)."\n";
      }
      else {
	  foreach ($contents as $item)
	      $html .= Xml::option($item, $item, $item == $selectedItem)."\n";
      }

      $html .= Xml::closeElement('select')."\n";

      return $html;
  }

}

?>

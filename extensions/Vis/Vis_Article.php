<?php

  //TODO: This entire file has been deprecated, and will likely disappear soon.  Don't rely on it.
class Vis_Article {

  static function getVisHTML($article){
    global $egVisEnableWikiMap, $egVisEnableWiego;
    //$visBox = '<font color=#000000><b>Visualizations</b><br/><ul>';
    
    $nsId = $article->getTitle()->getNamespace();
    if (Namespace::isTalk($nsId) || $nsId < 100 && $nsId != NS_MAIN)
      return '';

    if (!($egVisEnableWikiMap || $egVisEnableWiego))
      return '';
    
    $visBox = '<ul>';
    if ($egVisEnableWikiMap)
      $visBox .= '<li>&#8226; '.Vis_Article::getWikiMapHTML($article).'</li>';
    if ($egVisEnableWiego)
      $visBox .= '<li>&#8226; '.Vis_Article::getWiegoHTML($article).'</li>';

    //$visBox .= '<li>&#8226; '.Vis_Article::getCalendarHTML($article).'</li>';

    $visBox .= '</ul>';

    $header ='
<table class="toc" summary="Visualizations">
<tr><td width=228px>
<div id=vis><center><h2>Visualizations</h2></center></div>
<div>'.$visBox.'</div>
</td>
</tr>
</table>';
    //      <tr align = "left" valign="top">
    //<td width = 220px>
    //<div id=vis style="border-width: 1px 1px 1px 1px; border-style: solid; padding: 0px 3px; border-color: #084B8A;
//border-spacing: 1; background: #F9F9F9; -moz-border-radius: 0px;">'.$visBox.'
//    </div></td></tr></table>';

    return $header;
  }

  /**********************************************************************/
  /** function to check if user wants to load/unload topic map***********/
    static function getWikiMapHTML($article){
      global $wgRequest,$wgOut,$wgServer,$wgScriptPath,$egVisEnableAuthorDisplay;

    $baseUrl = $wgServer.''.$wgScriptPath;
    $wikiURL = $baseUrl.'/index.php';
    
	$title = $article->getTitle();
	$fullTitle = $title->getFullText();
    switch ($title->getNamespace()) {
    	case NS_USER:
    		$type = "Author";
    		break;
    	case NS_CATEGORY:
    		$type = "Category";
    		$fullTitle = $title->getText(); //must not have the Category: prefix
    		break;
    	default:
    		$type = "Article";
    }
    $popuplink  = $baseUrl.'/extensions/Vis/Vis_WikiMap/WikiMapFlexProject.html#baseURL='.$wikiURL . ";view=$fullTitle;type=$type";
    
    if ($egVisEnableAuthorDisplay)
      $popuplink .= ";aInfo=true";

    $result = '<a href="'.$popuplink.'" target="new">Open Wiki Map</a><br/>';
    return $result;
  }

  static function getWiegoHTML($article){
    global $wgRequest,$wgOut,$wgServer,$wgScriptPath;

    $baseUrl = $wgServer.''.$wgScriptPath;
    $wikiURL = $baseUrl.'/index.php';
    //$xmlBaseUrl = $baseUrl.'/index.php?action=makeGO';
    $popuplink  = $baseUrl.'/extensions/Vis/Vis_wiEGO/Wiego.html#wikiURL='.$wikiURL;

    $result = '<a href="'.$popuplink.'" target="new">Open wiEGO Tool</a><br/>';
    return $result;
  }

  /*  static function getCalendarHTML($article){
    global $wgRequest,$wgOut,$wgServer,$wgScriptPath,$wgTitle;

    $baseUrl = $wgServer.''.$wgScriptPath;
    $wikiURL = $baseUrl.'/index.php';
    //$xmlBaseUrl = $baseUrl.'/index.php?action=makeGO';
    $popuplink  = $baseUrl.'/extensions/Vis/Calendar/app.html#wikiURL='.$wikiURL.'#title='.$wgTitle->getText();

    $result = '<a href="'.$popuplink.'" target="new">Open Calendar Tool</a><br/>';
    return $result;
  }*/

}

?>

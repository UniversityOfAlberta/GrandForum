<?php 

require_once("Researcher.php");

$userVisualization = new ReaSoN();

$wgHooks['ParserAfterTidy'][] = array($userVisualization, 'addReaSoNLink');

class ReaSoN {
	
	var $researcher;
	
	function addReaSoNLink($parser, &$text){
		global $wgTitle, $wgRoles, $wgOut, $wgServer, $wgScriptPath;
		if($wgTitle != null){
			if(array_search($wgTitle->getNsText(), $wgRoles) !== false &&
			   $this->researcher == null){
				$name = str_replace(".", " ", $wgTitle->getText());
				$this->researcher = new Researcher($name);
				if($name != ""){
					$key = $this->researcher->getCache();
					if($key != ""){
						$text = str_replace("<div id=\"special_links\">", "<div id=\"special_links\"><a href='".Researcher::getReasonURL($key)."' target='_blank'><img height='30px' style='vertical-align:bottom;' src='$wgServer$wgScriptPath/skins/reason.gif' alt='ReaSoN' /></a>", $text);
					}
				}
			}
		}
		return true;
	}
}

?>

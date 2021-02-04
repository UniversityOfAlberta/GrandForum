<?php

use MediaWiki\MediaWikiServices;

$wgSearchType = "CustomSearchEngine";

class CustomSearchEngine extends SearchMySQL {
	public static function allowedSearchableNS() {
		global $egAnnokiNamespaces, $wgUser, $wgExtraNamespaces;

		$searchableNS = MediaWikiServices::getInstance()->getSearchEngineConfig()->searchableNamespaces();
		$accessibleNS = array_flip(AnnokiNamespaces::getNamespacesForUser($wgUser));

		$namespaces = array();
		foreach ($searchableNS as $id => $nsName) {
			if ($id < 100)
				$namespaces[$id] = $nsName;

			else if (isset($accessibleNS[$nsName])) {
				$namespaces[$id] = $nsName;
			}
			
			else if (MWNamespace::isTalk($id)) {
				$main = MWNamespace::getSubject($id);
				if(array_key_exists($main, $wgExtraNamespaces)){
					$mainName = $wgExtraNamespaces[$main];
					if (isset($accessibleNS[$mainName])) {
						$namespaces[$id] = $nsName;
					}
				}
			}
		}
		return $namespaces;
	}
	
	function updateNamespaces() {
		global $wgRequest;
		$allowed = array_keys(CustomSearchEngine::allowedSearchableNS());;
		
		if ($wgRequest->getText("fulltext") == "Search")
			$this->namespaces = $allowed;
			
		else if (count($this->namespaces) == 1 && $this->namespaces[0] == 0 && $wgRequest->getBool("ns0") === false)
			$this->namespaces = $allowed;
	}
	
	function searchText($term) {
		$this->updateNamespaces();	 
		return parent::searchText($term);
	}
	
	function searchTitle($term) {
		$this->updateNamespaces();
		return parent::searchTitle($term);
	}
}
?>

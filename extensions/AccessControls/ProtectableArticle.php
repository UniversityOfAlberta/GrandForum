<?php

require_once("CustomProtectionForm.php");

$wgHooks['ArticleFromTitle'][] = 'showProtectableArticle';

/**
 * we use the ArticleFromTitle hook in order to make mediawiki use our custom protectable article in stead of the standard article
 * @param Title $title
 * @param Article $article
 * @return unknown
 */
function showProtectableArticle(&$title, &$article) {
	/*
	 * only pages in the custom namespaces can be protected; pages in the main namespace should be moved 
	 * to a custom namespace before they can be protected 
	 */
	if ($title->getNamespace() >= 100) {
		$article =  new ProtectableArticle($title);
		return true;
	}
	else {
		return false;
	}
}

/**
 * This class implements an article that uses our custom protect action handler rather than the default one
 */
class ProtectableArticle extends Article {
	function protect() {
		$form = new CustomProtectionForm( $this );
		$form->execute();
	}
}
?>

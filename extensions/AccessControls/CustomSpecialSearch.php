<?php

use MediaWiki\MediaWikiServices;

require_once("$IP/includes/specials/SpecialSearch.php");

/*
 * this function is the same as the original wfSpecialSearch except it instantiates
 * the class CustomSpecialSearch which extends SpecialSearch
 */
function wfSpecialCustomSearch( $par = '' ) {
	global $wgRequest, $wgUser;

	$search = str_replace( "\n", " ", $wgRequest->getText( 'search', $par ) );
	$searchPage = new CustomSpecialSearch( $wgRequest, $wgUser );
	if( $wgRequest->getVal( 'fulltext' )
	|| !is_null( $wgRequest->getVal( 'offset' ))
	|| !is_null( $wgRequest->getVal( 'searchx' ))) {
		$searchPage->showResults( $search, 'search' );
	} else {
		$searchPage->goResult( $search );
	}
}

class CustomSpecialSearch extends SpecialSearch {
	function powerSearch( &$request ) {
		global $egAnnokiNamespaces, $wgUser;
	
		$searchableNS = MediaWikiServices::getInstance()->getSearchEngineConfig()->searchableNamespaces();
		$accessibleNS = array_flip(AnnokiNamespaces::getNamespacesForUser($wgUser));
		
		
		$namespaces = array();
		foreach ($searchableNS as $id => $nsName) {
			if ($id < 100)
				$namespaces[] = $id;
				
			if (isset($accessibleNS[$nsName]))
				$namespaces[] = AnnokiNamespaces::getNamespaceID($nsName);	
		}
		
		return $namespaces;
	}
	function powerSearchBox( $term ) {
		global $wgScript, $wgUser;

		$namespaces = '';
		$groups = AnnokiNamespaces::getNamespacesForUser($wgUser);
		$groups = array_flip($groups);
		
		foreach( MediaWikiServices::getInstance()->getSearchEngineConfig()->searchableNamespaces() as $ns => $name ) {
			$name = str_replace( '_', ' ', $name );
			if( '' == $name ) {
				$name = wfMsg( 'blanknamespace' );
			}
			$namespaces .= Xml::openElement( 'span', array( 'style' => 'white-space: nowrap' ) );
			//print_r($groups);
			if ($ns < 100 || isset($groups[$name])) {
				if (MWNamespace::isMain($ns)) {
					$namespaces .= Xml::checkLabel( $name, "ns{$ns}", "mw-search-ns{$ns}", true );
				}
				else if (MWNamespace::isTalk($ns)) {
					$namespaces .= Xml::hidden("ns{$ns}", "true");
				}
			}
			//Xml::checkLabel( $name, "ns{$ns}", "mw-search-ns{$ns}", in_array( $ns, $this->namespaces ) ) .
			$namespaces .= Xml::closeElement( 'span' ) . "\n";
		}

		$redirect = Xml::check( 'redirs', $this->searchRedirects, array( 'value' => '1', 'id' => 'redirs' ) );
		$redirectLabel = Xml::label( wfMsg( 'powersearch-redir' ), 'redirs' );
		$searchField = Xml::input( 'search', 50, $term, array( 'type' => 'text', 'id' => 'powerSearchText' ) );
		$searchButton = Xml::submitButton( wfMsg( 'powersearch' ), array( 'name' => 'fulltext' ) ) . "\n";

		$out = Xml::openElement( 'form', array(	'id' => 'powersearch', 'method' => 'get', 'action' => $wgScript ) ) .
		Xml::fieldset( wfMsg( 'powersearch-legend' ),
		Xml::hidden( 'title', 'Special:Search' ) .
				"<p>" .
		wfMsgExt( 'powersearch-ns', array( 'parseinline' ) ) .
				"<br />" .
		$namespaces .
				"</p>" .
				"<p>" .
		$redirect . " " . $redirectLabel .
				"</p>" .
		wfMsgExt( 'powersearch-field', array( 'parseinline' ) ) .
				"&nbsp;" .
		$searchField .
				"&nbsp;" .
		$searchButton ) .
			"</form>";

		return $out;
	}
}
?>

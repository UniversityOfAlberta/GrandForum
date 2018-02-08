<?php

/**
 * @package GrandObjects
 */

class BibTexExporter {
	var $document_type;
	var $authors;
	var $title;
	var $journal;
	var $year;
	var $pages;
	var $doi;
	var $note;
	var $url;
	var $source;

	/**
	* Takes a Paper object and converts to BibTeX format
	* returns a String
	*/
	static function exportProduct($paper) {
		var_dump($paper);
		exit;
	}
}
?>
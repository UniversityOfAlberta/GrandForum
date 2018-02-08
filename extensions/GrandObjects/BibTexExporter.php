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
		print_r($paper);
		print_r("\n");
		$bibtex = "@";
		$data = $paper->data;
		$something = array_flip(ImportBibTeXAPI::$bibtexHash);
		$bibtex .= strtoupper($something[$paper->type]) . "{" . $paper->bibtex_id . ",\n";
		$bibtex .= "author={";
		$temp_arr = array();
		foreach($paper->getAuthors() as $person) {
			array_push($temp_arr, $person->getNameForProduct("{%Last}, {%F.}{%M.}"));
		}
		$bibtex .= implode(" and ", $temp_arr);
		$bibtex .= "},\n";
		$bibtex .= "title={" . $paper->title . "},\n";
		// check type
		$structure = Product::structure();
		$data_structure = $structure['categories'][$paper->getCategory()]['types'][$paper->getType()]['data'];
		foreach($data as $k=>$d) {
			if (isset($data_structure[$k])) {
				if ($data_structure[$k]['bibtex']) {
					$bibtex .= "{$data_structure[$k]['bibtex']}={{$d}},\n";
				}
			}
		}
		//$bibtex .= "journal={" . @$data['event_title'] . "},\n"; # this might need to change depending on type of product
		$bibtex .= "year={" . $paper->getYear() . "},\n";
		$bibtex .= "}";
		print_r($bibtex);
		exit;
	}

}
?>
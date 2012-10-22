<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ImportBibTex'] = 'ImportBibTex'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ImportBibTex'] = $dir . 'ImportBibTex.i18n.php';
$wgSpecialPageGroups['ImportBibTex'] = 'grand-tools';

function runImportBibTex($par) {
  ImportBibTex::run($par);
}

class ImportBibTex extends SpecialPage{

	function ImportBibTex() {
		wfLoadExtensionMessages('ImportBibTex');
		SpecialPage::SpecialPage("ImportBibTex", HQP.'+', true, 'runImportBibTex');
	}

	/// Extracts a BibTeX chunk from #text, returning it as a substring,
	/// and indicating the possible type in #bibtype (without the @
	/// delimiter) .  In addition, #ind is updated as the starting index of
	/// a future request.
	function nextEntry(&$text, &$ind, &$bibtype) {
		$at = strpos($text, '@', $ind);
		if ($at === false) {
			return null;
		}
		$valid = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		// Skip anything not alphabetic.
		$sta = $at + strcspn($text, $valid, $at);
		$biblen = strspn($text, $valid, $sta);
		$bibtype = substr($text, $sta, $biblen);

		$brace = strpos($text, '{', $sta + $biblen);
		$rem = strlen($text);
		$stack = 1;
		$fini = $brace + 1;
		while ($fini < $rem && $stack > 0) {
			// Skip non-braces until a brace.
			$fini += strcspn($text, "{}", $fini);
			// Add 1 every opening brace, subtract 1 every closing brace.
			$stack += ($text[$fini] === '{') - ($text[$fini] === '}');
			// Skip brace.
			$fini++;
		}

		// Update cursor for a future query.
		$ind = $fini;
		if ($stack === 0) {
			// BibTeX chunk looks good.
			return substr($text, $at, $fini - $at);
		}
		return null;
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		if($wgUser->isLoggedIn()){
			if(isset($_POST['submit'])){
				// Identify chunks of BibTeX entries.
				$ind = 0;
				$rejects = "";
				$text = $_POST['text'];
				$returns = array();
				while (($nextbib = self::nextEntry($text, $ind, $bibtype)) !== null) {
				    //Sort of a hack to prevent poorly formatted bibtex

				    $nextbib = str_replace("\t", " ", $nextbib);
				    $nextbib = str_replace("\r\n", "\n", $nextbib);
				    $nextbib = str_replace("\r", "\n", $nextbib);
				    $nextbib = str_replace(" \n", "\n", $nextbib);	// Hack for Duane's issue.
				    $nextbib = str_replace("\n", "\n\n", $nextbib);
				    $nextbib = str_replace(",\n", ",\n\n", $nextbib);
				    $nextbib = str_replace("\n\n", "", $nextbib);
				    $nextbib = str_replace("  ", " ", $nextbib);

					$api = null;
					$lines = explode("\n", $nextbib);
					switch (strtolower($bibtype)) {
					    case 'article':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new JournalPaperAPI(ImportBibTex::alreadyExists());
						    break;
					    case 'book':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new BookAPI(ImportBibTex::alreadyExists());
						    break;
					    case 'proceedings':
					    case 'inproceedings':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new ProceedingsPaperAPI(ImportBibTex::alreadyExists());
						    break;
				        case 'collection':
				        case 'incollection':
				            ImportBibTex::parseBibTeX($lines);
						    $api = new CollectionAPI(ImportBibTex::alreadyExists());
						    break;
					    case 'manual':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new ManualAPI(ImportBibTex::alreadyExists());
						    break;
					    case 'mastersthesis':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new MastersThesisAPI(ImportBibTex::alreadyExists());
						    break;
                        case 'bachelorsthesis' :
                            ImportBibTex::parseBibTeX($lines);
                            $api = new BachelorsThesisAPI(ImportBibTex::alreadyExists());
                            break;
					    case 'phdthesis':
					    case 'thesis':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new PHDThesisAPI(ImportBibTex::alreadyExists());
						    break;
                        case 'poster':
                            ImportBibTex::parseBibTeX($lines);
                            $api = new PosterAPI(ImportBibTex::alreadyExists());
                            break;
					    case 'techreport':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new TechReportAPI(ImportBibTex::alreadyExists());
						    break;
					    default:
					    case 'misc':
						    ImportBibTex::parseBibTeX($lines);
						    $api = new MiscAPI(ImportBibTex::alreadyExists());
						    break;
					}
					if($api != null){
					    $return = str_replace("\n", "", $api->doAction(false));
					    $title = str_replace("'", "&#39;", $_POST['title']);
					    if(strstr($return, "was not") === false){
					        $success = true;
					        $return = "<a target='_blank' href='$wgServer$wgScriptPath/index.php/Publication:".str_replace("?", "%3F", $title)."'>".$return."</a>";
					    }
					    else{
					        $return = "$title was not entered correctly";
					    }
					    $returns[] = $return;
					}
				}
				if(count($returns) > 0){
				    $wgOut->addHTML("Please verify that the entered products are correct.<br />");
				    $wgOut->addHTML("<ul>");
				    foreach($returns as $return){
				        $wgOut->addHTML("<li>$return</li>");
				    }
				    $wgOut->addHTML("</ul>");
				}
				ImportBibTex::generateFormHTML($wgOut);
			}
			else {
				ImportBibTex::generateFormHTML($wgOut);
			}
		}
		else {
			$wgOut->addHTML("This page is not public.  <a href='$wgScriptPath/index.php?title=Special:UserLogin&returnto={$wgTitle->getNsText()}:{$wgTitle->getText()}'>Click Here</a> to login.");
		}
	}
	
	// Checks whether or not the publication was already added or not
	function alreadyExists(){
	    if(isset($_POST['title'])){
            $paper = Paper::newFromTitle($_POST['title']);
            if($paper->getTitle() != ""){
                return true;
            }
        }
        return false;
	}
	
	function getPaper(){
	    if(isset($_POST['title'])){
            $paper = Paper::newFromTitle($_POST['title']);
            return $paper;
        }
        return null;
	}
	
	function parseBibTeX($lines){
	    foreach($lines as $line){
			if(ImportBibTex::getBibTexVariable($line, "author") !== false){
				$author = ImportBibTex::getBibTexVariable($line, "author");
				$authors = explode(" and ", $author);
				$names = array();
				foreach($authors as $auth){
					$auth_names = explode(", ", $auth);
					$first = isset($auth_names[1]) ? $auth_names[1] : "";
					$last = isset($auth_names[0]) ? $auth_names[0] : "";
					$names[] = "$first $last";
				}
				$_POST['authors'] = $names;
			}
			else if(ImportBibTex::getBibTexVariable($line, "title") !== false){
				$_POST['title'] = ImportBibTex::getBibTexVariable($line, "title");
			}
			else if(ImportBibTex::getBibTexVariable($line, "booktitle") !== false){
				$_POST['book_title'] = ImportBibTex::getBibTexVariable($line, "booktitle");
			}
			else if(ImportBibTex::getBibTexVariable($line, "journal") !== false){
				$_POST['journal_title'] = ImportBibTex::getBibTexVariable($line, "journal");
			}
			else if(ImportBibTex::getBibTexVariable($line, "publisher") !== false){
				$_POST['publisher'] = ImportBibTex::getBibTexVariable($line, "publisher");
			}
			else if(ImportBibTex::getBibTexVariable($line, "address") !== false){
				$_POST['address'] = ImportBibTex::getBibTexVariable($line, "address");
			}
			else if(ImportBibTex::getBibTexVariable($line, "doi") !== false){
				$_POST['doi'] = ImportBibTex::getBibTexVariable($line, "doi");
			}
			else if(ImportBibTex::getBibTexVariable($line, "year") !== false){
				$_POST['year'] = ImportBibTex::getBibTexVariable($line, "year");
			}
			else if(ImportBibTex::getBibTexVariable($line, "month") !== false){
				$_POST['month'] = ImportBibTex::getBibTexVariable($line, "month");
			}
			else if(ImportBibTex::getBibTexVariable($line, "isbn") !== false){
				$_POST['isbn'] = ImportBibTex::getBibTexVariable($line, "isbn");
			}
			else if(ImportBibTex::getBibTexVariable($line, "issn") !== false){
				$_POST['issn'] = ImportBibTex::getBibTexVariable($line, "issn");
			}
			else if(ImportBibTex::getBibTexVariable($line, "volume") !== false){
				$_POST['volume'] = ImportBibTex::getBibTexVariable($line, "volume");
			}
			else if(ImportBibTex::getBibTexVariable($line, "series") !== false){
				$_POST['series'] = ImportBibTex::getBibTexVariable($line, "series");
			}
			else if(ImportBibTex::getBibTexVariable($line, "edition") !== false){
				$_POST['edition'] = ImportBibTex::getBibTexVariable($line, "edition");
			}
			else if(ImportBibTex::getBibTexVariable($line, "number") !== false){
				$_POST['number'] = ImportBibTex::getBibTexVariable($line, "number");
			}
			else if(ImportBibTex::getBibTexVariable($line, "pages") !== false){
				$_POST['pages'] = ImportBibTex::getBibTexVariable($line, "pages");
			}
			else if(ImportBibTex::getBibTexVariable($line, "school") !== false){
				$_POST['university'] = ImportBibTex::getBibTexVariable($line, "school");
			}
			else if(ImportBibTex::getBibTexVariable($line, "institution") !== false){
				$_POST['institution'] = ImportBibTex::getBibTexVariable($line, "institution");
			}
			else if(ImportBibTex::getBibTexVariable($line, "howpublished") !== false){
			    $_POST['how_published'] = ImportBibTex::getBibTexVariable($line, "howpublished");
			}
			else if(ImportBibTex::getBibTexVariable($line, "note") !== false){
			    $_POST['note'] = ImportBibTex::getBibTexVariable($line, "note");
			}
		}
		$_POST['date'] = "";
		if(isset($_POST['year'])){
		    $_POST['date'] = $_POST['year']."-";
		}
		else{
		    $_POST['date'] = "0000-";
		}
		if(isset($_POST['month'])){
		    if(is_numeric($_POST['month'])){
		        $_POST['date'] .= $_POST['month']."-";
		    }
		    else{
		        $_POST['month'] = substr(strtolower($_POST['month']), 0, 3);
		        $_POST['month'] = str_replace("jan", "01", $_POST['month']);
		        $_POST['month'] = str_replace("feb", "02", $_POST['month']);
		        $_POST['month'] = str_replace("mar", "03", $_POST['month']);
		        $_POST['month'] = str_replace("apr", "04", $_POST['month']);
		        $_POST['month'] = str_replace("may", "05", $_POST['month']);
		        $_POST['month'] = str_replace("feb", "06", $_POST['month']);
		        $_POST['month'] = str_replace("jun", "06", $_POST['month']);
		        $_POST['month'] = str_replace("jul", "07", $_POST['month']);
		        $_POST['month'] = str_replace("aug", "08", $_POST['month']);
		        $_POST['month'] = str_replace("sep", "09", $_POST['month']);
		        $_POST['month'] = str_replace("oct", "10", $_POST['month']);
		        $_POST['month'] = str_replace("nov", "11", $_POST['month']);
		        $_POST['month'] = str_replace("dec", "12", $_POST['month']);
		        $_POST['date'] .= $_POST['month']."-";
		    }
		}
		else{
		    $_POST['date'] .= "01-";
		}
		$_POST['date'] .= "01";
		$_POST['new_title'] = @$_POST['title'];
		$paper = ImportBibTex::getPaper();
	    if($paper != null){
	        $_POST['product_id'] = $paper->getId();
	    }
	}
	
	function generateFormHTML($wgOut){
		global $wgServer, $wgScriptPath;
		$wgOut->addHTML("<p>Paste BibTeX entries in the text box below for importing:
				<form action='$wgServer$wgScriptPath/index.php/Special:ImportBibTex' method='post'>
					<textarea name='text' style='width:650px; height:300px;'></textarea><br /><br />
					<input type='submit' name='submit' value='Submit' />
				</form>
<h2>Example Usage</h2>
The following BibTeX entry types are supported by the importing mechanism.  If the BibTeX type you wish to import is not included below, it will still be imported, however it will only import some of the data fields provided.
Each type is accompanied of a fictitious entry that can serve as a reference, if needed.  Because there are a variety of conventions for using bibtex, you will likely have to edit your publication entry (through the Add/Edit form) after you have uploaded a bibtex file.
<ul>
	<li><b>@book</b><br /><div><pre>
@book{Book89,
	author = {First Author and Second Author},
	title = {A Great Book Title},
	publisher = {Fine Publisher},
	address = {Publisher City, State, Country},
	year = {1989},
	doi = {dx.doi.org/1989.12/34567890}
}
</pre></div>
	<li><b>@article</b><br /><div><pre>
@article{Article2002,
	author = {First Author and Second Author and Third Author},
	title = {An Incredible Research},
	journal = {Super Journal of Computing},
	volume = {2122},
	number = {33},
	pages = {10--29},
	publisher = {Some Publisher},
	address = {Publisher City, State, Country},
	year = {2002},
	doi = {dx.doi.org/2002.01/234567890}
}
</pre></div>
	<li><b>@inproceedings</b><br /><div><pre>
@inproceedings{Paper2002,
	author = {First Author and Second Author and Third Author},
	title = {An Incredible Research Paper},
	booktitle = {Super Conference of Computing},
	pages = {10--19},
	publisher = {Some Publisher},
	address = {Publisher City, State, Country},
	year = {2002},
	isbn = {1234567890},
	doi = {dx.doi.org/2002.05/567876811}
}
</pre></div>
    <li><b>@incollection</b><br /><div><pre>
@incollection{Paper2002,
	author = {First Author and Second Author and Third Author},
	title = {An Incredible Research Paper},
	booktitle = {Super Conference of Computing},
	pages = {10--19},
	publisher = {Some Publisher},
	address = {Publisher City, State, Country},
	year = {2002},
	isbn = {1234567890},
	doi = {dx.doi.org/2002.05/567876811}
}
</pre></div>
	<li><b>@phdthesis</b><br /><div><pre>
@phdthesis{Thesis2006,
	author = {First Author},
	title = {The Tome of Computing},
	school = {University of Fine Studies},
	year = {2006},
	month = {mar},
	address = {City, State, Country},
	doi = {dx.doi.org/2006.03/787672211}
}
</pre></div>
	<li><b>@mastersthesis</b><br /><div><pre>
@mastersthesis{Thesis2004,
	author = {First Author},
	title = {The Tome of Computing},
	school = {University of Fine Studies},
	year = {2004},
	month = {sep},
	address = {City, State, Country},
	doi = {dx.doi.org/2004.09/355518838}
}
</pre></div>
	<li><b>@bachelorsthesis</b><br /><div><pre>
@bachelorsthesis{Thesis2004,
	author = {First Author},
	title = {The Tome of Computing},
	school = {University of Fine Studies},
	year = {2004},
	month = {sep},
	address = {City, State, Country},
	doi = {dx.doi.org/2004.09/355518838}
}
</pre></div>
    <li><b>@poster</b><br /><div><pre>
@poster{Poster2002,
   author = {First Author and Second Author and Third Author},
   title = {An Incredible Research Poster},
   booktitle = {Conference Where It Was Presented},
   publisher = {Conference Sponsor},
   year = {2002}
}
</pre></div>
	<li><b>@techreport</b><br /><div><pre>
@techreport{Thesis2004,
	author = {First Author},
	title = {The Tome of Computing},
	school = {University of Fine Studies},
	year = {2004},
	month = {sep},
	address = {City, State, Country},
	doi = {dx.doi.org/2004.09/355518838}
}
</pre></div>
	<li><b>@manual</b><br /><div><pre>
@manual{Manual2005,
	author = {First Author and Second Author and Third Author},
	title = {Manual of Computing},
	volume = {1},
	edition = {2nd},
	series = {Computing Explained},
	pages = {1090},
	publisher = {Finest Publisher},
	address = {City, State, Country},
	year = {2005},
	month = {apr},
}
</pre></div>
	<li><b>@misc</b><br /><div><pre>
@misc{Misc1990,
	author = {First Author and Second Author},
	title = {Miscellaneous Studies}
}
</pre></div>
</ul>");
	}
	
	function getBibTexVariable($line, $variable){
	    if(preg_match("/^\s*$variable\s*=\s*/i", $line) > 0){
	        $var = preg_replace('/^\s*'.$variable.'\s*=\s*/i', "", $line);
			return ImportBibTex::removeSpecialCharacters(preg_replace('/,$/', "", $var));
	    }
	    return false;
	}
	
	function removeSpecialCharacters($str){
	    $str = str_replace("{", "", $str);
	    $str = str_replace("}", "", $str);
	    $str = str_replace("\\url", "", $str);
	    $str = str_replace("\\it", "", $str);
	    $str = str_replace("\"", "", $str);
	    $str = str_replace("\\texttt", "", $str);
	    $str = str_replace("  ", " ", $str);
	    $str = str_replace("\t\t", "\t", $str);
	    $str = str_replace("\n", "", $str);
	    $str = preg_replace('/\s+/', ' ', $str);
	    return $str;
	}
}

?>

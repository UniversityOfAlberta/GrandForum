<?php

class ImportBibTeXAPI extends API{

    static $bibtexHash = array('proceedings' => array('Proceedings Paper', 'Conference Proceedings'),
                               'inproceedings' => array('Proceedings Paper', 'Conference Proceedings'),
                               'inbook' => array('Proceedings Paper', 'Conference Proceedings'),
                               'conference' => array('Conference Paper', 'Conference Proceedings'),
                               'book' => 'Book',
                               'article' => array('Journal Paper', 'Scholarly Refereed Journal'),
                               'collection' => array('Collections Paper', 'Journal Paper', 'Scholarly Refereed Journal'),
                               'incollection' => array('Collections Paper', 'Journal Paper', 'Scholarly Refereed Journal'),
                               'manual' => 'Manual',
                               'mastersthesis' => array('Masters Thesis', 'Master Thesis'),
                               'bachelorsthesis' => array('Bachelors Thesis', 'Bachelor Thesis'),
                               'phdthesis' => array('PHD Thesis', 'PhD Thesis', 'Doctoral Thesis/Dissertation'),
                               'thesis' => array('PHD Thesis', 'PhD Thesis', 'Doctoral Thesis/Dissertation'),
                               'poster' => 'Poster',
                               'techreport' => 'Tech Report',
                               'inbook' => 'Book Chapter',
                               'misc' => 'Misc',
                               'patent' => array('Patent', 'Patent Issued'));

    var $structure = null;

    function __construct(){
        $this->addPost("bibtex", true, "The BibTeX reference(s)", "");
    }

    function processParams($params){
        // Add new line since parsing will most likely fail otherwise
        $_POST['bibtex'] = $_POST['bibtex']."\n";
    }
    
    function getMonth($month){
        if(strlen($month) != 3){
            return "01";
        }
        $month = substr(strtolower($month), 0, 3);
        $month = str_replace("jan", "01", $month);
        $month = str_replace("feb", "02", $month);
        $month = str_replace("mar", "03", $month);
        $month = str_replace("apr", "04", $month);
        $month = str_replace("may", "05", $month);
        $month = str_replace("feb", "06", $month);
        $month = str_replace("jun", "06", $month);
        $month = str_replace("jul", "07", $month);
        $month = str_replace("aug", "08", $month);
        $month = str_replace("sep", "09", $month);
        $month = str_replace("oct", "10", $month);
        $month = str_replace("nov", "11", $month);
        $month = str_replace("dec", "12", $month);
        return $month;
    }
    
    function createProduct($paper, $category, $type, $overwrite=false, $private=true){
        if(!isset($paper['title']) ||
           !isset($paper['author'])){
            return null;  
        }
        $checkBibProduct = Product::newFromBibTeXId(@$paper['doi'], $paper['title']);
        $checkProduct = Product::newFromTitle($paper['title']);
        if((!$overwrite && $checkProduct->exists()) ||
           (!$overwrite && $checkBibProduct->exists())){
            return null;
        }
        if(@trim($paper['doi']) != "" && $checkBibProduct->getId() != 0){
            // Make sure that this entry was not already entered
            $product = $checkBibProduct;
        }
        else if($checkProduct->getId() != 0 && 
           ($checkProduct->getCategory() == $category || $category == null) &&
           $checkProduct->getType() == $type){
            // Make sure that a product with the same title/category/type does not already exist
            $product = $checkProduct;
        }
        else{
            $product = new Product(array());
            $product->title = str_replace("&#39;", "'", $paper['title']);
            $product->category = $category;
            $product->type = $type;
        }
        if(isset($this->structure['categories'][$category]['types'][$type])){
            // Make sure that the type actually exists
            $structure = $this->structure['categories'][$category]['types'][$type];
        }
        else{
            $found = false;
            foreach($this->structure['categories'] as $cat => $cats){
                if(isset($cats['types'][$type])){
                    // Then check if the type might exist in a different category
                    $found = true;
                    $product->category = $cat;
                    $product->type = $type;
                    $structure = $this->structure['categories'][$cat]['types'][$type];
                    break;
                }
            }
            if(!$found){
                return false;
            }
        }
        $me = Person::newFromWgUser();

        if(isset($paper['abstract'])){ $product->description = @$paper['abstract']; }
        if($product->status == ""){ $product->status = "Published"; }
        $product->date = @"{$paper['year']}-{$this->getMonth($paper['month'])}-01";
        $product->data = array();
        if(!is_array($product->projects)){ $product->projects = array(); }
        $product->authors = array();
        if(!$product->exists()){
            if ($private) { $product->access_id = $me->getId(); }
        }
        $product->access = "Public";
        $product->authors = array();
        if(strstr($paper['author'], " and ") === false && substr_count($paper['author'], ",") > 1){
            // Must be using ',' as a delimiter...
            $count = null;
            $paper['author'] = str_replace_every_other(",", " and ", $paper['author'], $count, false);
        }
        
        $authors = explode(" and ", $paper['author']);
        foreach($authors as $author){
            $obj = new stdClass;
            $names = explode(",", $author);
            if(count($names) >= 2){
                $firstName = trim($names[1]);
                $lastName = trim($names[0]);
                $obj->name = trim("$firstName $lastName");
                $obj->fullname = trim("$firstName $lastName");
            }
            else{
                $obj->name = trim($author);
                $obj->fullname = trim($author);
            }
            $product->authors[] = $obj;
        }
        
        $product->data = array();
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['bibtex'] == $key){
                        $product->data[$dkey] = $field;
                        if($dkey == "issn"){
                            $journals = Journal::newFromIssn($field);
                            $nJournals = count($journals);
                            if($nJournals >= 1){
                                $journal = $journals[0];
                                if($nJournals == 1 && $journal->ranking_numerator > 0 && $journal->ranking_denominator > 0){
                                    // Only one for this category found, also include ranking
                                    $product->data['category_ranking'] = "{$journal->ranking_numerator}/{$journal->ranking_denominator}";
                                }
                                $product->data['impact_factor'] = $journal->impact_factor;
                                $product->data['eigen_factor'] = $journal->eigenfactor;
                            }
                        }
                        break;
                    }
                }
            }
        }
        
        if(!$product->exists()){
            $status = $product->create();
        }
        else{
            $product->deleted = 0;
            $status = $product->update();
        }
        if($status){
            $product = Product::newFromId($product->getId());
            return $product;
        }
        else{
            return null;
        }
    }

	function doAction($noEcho=false){
	    global $wgMessage;
	    $me = Person::newFromWgUser();
        if(isset($_POST['bibtex'])){
            $this->structure = Product::structure();
            $dir = dirname(__FILE__);
            $error = "";
            require_once($dir."/../../Classes/CCCVTK/bibtex-bib.lib.php");
            $md5 = md5($_POST['bibtex']);
            $fileName = "/tmp/".$md5;
            $_POST['bibtex'] = preg_replace("/((\\w+?)\\s*=\\s*\\{(.*?)\\},*)(\\s)*/ms", "\n$1\n", $_POST['bibtex']);
            file_put_contents($fileName, $_POST['bibtex']);
            $bib = new Bibliography($fileName);
            unlink($fileName);
            $createdProducts = array();

            $errorProducts = array();
            $overwrite = (isset($_POST['overwrite']) && strtolower($_POST['overwrite']) == "yes") ? true : false;
            if(is_array($bib->m_entries) && count($bib->m_entries) > 0){
                foreach($bib->m_entries as $paper){
                    $type = (isset(self::$bibtexHash[strtolower($paper['bibtex_type'])])) ? self::$bibtexHash[strtolower($paper['bibtex_type'])] : "Misc";
                    if(is_array($type)){
                        // Could map to different types
                        foreach($type as $t){
                            $product = $this->createProduct($paper, null, $t, $overwrite);
                            if($product !== false){
                                break;
                            }
                        }
                    }
                    else{
                        $product = $this->createProduct($paper, null, $type, $overwrite);
                    }
                    if($product != null && $product !== false){
                        $createdProducts[] = $product;
                    }
                    else{
                        $errorProducts[] = $paper;
                    }
                }
            }
            else{
                // Error
                $this->addError("No BibTeX references were found");
                return false;
            }
            $json = array('created' => array(),
                          'duplicates' => array(),
                          'errors' => array());
            foreach($createdProducts as $product){
                $json['created'][] = $product->toArray();
            }
            foreach($errorProducts as $product){
                if(!isset($product['title'])){
                    $this->addError("A publication was missing a title");
                }
                else if(!isset($product['author'])){
                    $this->addError("A publication was missing an authors list");
                }
                else{
                    $checkProduct = Product::newFromTitle($product['title']);
                    if($checkProduct->exists()){
                         $json['duplicates'][] = $checkProduct->toArray();
                    }
                    $this->addMessage("{$product['title']}");
                }
            }
            $this->data = $json;
            return $json;
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>

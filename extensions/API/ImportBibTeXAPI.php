<?php

class ImportBibTeXAPI extends API{

    static $bibtexHash = array('inproceedings' => 'Conference Paper',
                               'proceedings' => 'Conference Paper',
                               'inbook' => 'Conference Paper',
                               'conference' => 'Conference Paper',
                               'book' => 'Book',
                               'article' => 'Journal Paper',
                               'collection' => 'Collections Paper',
                               'incollection' => 'Collections Paper',
                               'manual' => 'Manual',
                               'mastersthesis' => 'Masters Thesis',
                               'bachelorsthesis' => 'Bachelors Thesis',
                               'phdthesis' => 'PHD Thesis',
                               'thesis' => 'PHD Thesis',
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
        if(is_numeric($month)){
            return $month;
        }
        else if(strlen($month) != 3){
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
    
    function createProduct($paper, $category, $type, $overwrite=false){
        global $config;
        if(!isset($paper['title']) ||
           !isset($paper['author'])){
            return null;  
        }
        $checkBibProduct = Product::newFromBibTeXId(@$paper['doi']);
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
            $product->contributors = array();
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
                // If not, then use the Misc type
                $structure = $this->structure['categories'][$category]['types']['Misc'];
                $product->type = "Misc: {$type}";
            }
        }
        $me = Person::newFromWgUser();

        $paper['author'] = str_replace("‐", "-", $paper['author']); // Fix special dash
        $paper['day'] = (isset($paper['day'])) ? $paper['day'] : "01";

        $product->getAuthors();
        $product->getContributors();

        if($product->description == ""){ $product->description = @$paper['abstract']; }
        if($product->status == ""){ $product->status = "Published"; }
        if($product->date == ""){ $product->date = @"{$paper['year']}-{$this->getMonth($paper['month'])}-{$paper['day']}"; }
        if(str_replace(ZOT, "", $product->acceptance_date) == ""){
            if(@$paper['acceptance_date'] != ""){
                $product->acceptance_date = @"{$paper['acceptance_date']}";
            }
            else{
                $product->acceptance_date = $product->date;
            }
        }
        if(!is_array($product->data)){ $product->data = array(); }
        if(!is_array($product->authors)){ $product->authors = array(); }
        if(!$product->exists()){
            $product->access_id = $me->getId();
        }
        $product->access = "Public";
        if(empty($product->authors)){
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
                }
                else{
                    $obj->name = trim($author);
                }
                $product->authors[] = $obj;
            }
        }
        
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['bibtex'] == $key){
                        if(!isset($product->data[$dkey]) || $product->data[$dkey] == ""){
                            $product->data[$dkey] = $field;
                            if($dkey == "issn"){
                                if($config->getValue('elsevierApi') != ""){
                                    $journals = ElsevierJournal::newFromIssn($field);
                                }
                                else{
                                    $journals = Journal::newFromIssn($field);
                                }
                                $nJournals = count($journals);
                                if($nJournals >= 1){
                                    $journal = $journals[0];
                                    if($nJournals == 1 && $journal->ranking_numerator > 0 && $journal->ranking_denominator > 0){
                                        // Only one for this category found, also include ranking
                                        $product->data['category_ranking'] = "{$journal->ranking_numerator}/{$journal->ranking_denominator}";
                                    }
                                    $product->data['impact_factor'] = $journal->impact_factor;
                                    $product->data['eigen_factor'] = $journal->eigenfactor;
                                    $product->data['snip'] = $journal->snip;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
        if(!$product->exists()){
            $status = $product->create(false);
        }
        else{
            $product->deleted = 0;
            $status = $product->update(false);
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
            if(isset($_POST['fec'])){
                $bib = new stdClass();
                $bib->m_entries = $_POST['fec'];
            }
            else{
                $dir = dirname(__FILE__);
                $error = "";
                require_once($dir."/../../Classes/CCCVTK/bibtex-bib.lib.php");
                $md5 = md5($_POST['bibtex']);
                $fileName = "/tmp/".$md5;
                $_POST['bibtex'] = preg_replace('/((\w+?)\s*=\s*\{(.*?)\},*)([\s|}])/ms', "\n$1\n$4", $_POST['bibtex']);
                file_put_contents($fileName, $_POST['bibtex']);
                $bib = new Bibliography($fileName);
                unlink($fileName);
            }
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
                $syncInserts = array();
                $syncDeletes = array();
                foreach($createdProducts as $product){
                    $sqls = $product->syncAuthors(true);
                    foreach($sqls[1] as $s){
                        $syncInserts[$product->getId()] = $s;
                    }
                    $syncDeletes[$product->getId()] = $product->getId();
                }
                
                if(count($syncInserts) > 0){
                    DBFunctions::begin();
                    DBFunctions::execSQL("DELETE FROM `grand_product_authors` WHERE product_id IN (".implode(",", $syncDeletes).")", true, true);
                    DBFunctions::execSQL("INSERT INTO `grand_product_authors` (`author`, `product_id`, `order`)
	                                      VALUES\n".implode(",\n",$syncInserts), true, true);
	                DBFunctions::commit();
	            }
            }
            else{
                // Error
                $this->addError("No BibTeX references were found");
                return false;
            }
            $json = array('created' => array(),
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

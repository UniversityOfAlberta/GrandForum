<?php

class ImportBibTeXAPI extends API{

    static $bibtexHash = array('inproceedings' => 'Proceedings Paper',
                               'proceedings' => 'Proceedings Paper',
                               'inbook' => 'Proceedings Paper',
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
                               'misc' => 'Misc');

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
    
    function createProduct($paper, $category, $type, $bibtex_id){
        if(!isset($paper['title']) ||
           !isset($paper['author'])){
            return null;  
        }
        $checkBibProduct = Product::newFromBibTeXId($bibtex_id, $paper['title']);
        $checkProduct = Product::newFromTitle($paper['title']);
        if($checkBibProduct->getId() != 0){
            // Make sure that this entry was not already entered
            $product = $checkBibProduct;
        }
        else if($checkProduct->getId() != 0 && 
           $checkProduct->getCategory() == $category &&
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
                // If not, then use the Misc type
                $structure = $this->structure['categories'][$category]['types']['Misc'];
                $product->type = "Misc: {$type}";
            }
        }
        $me = Person::newFromWgUser();

        if($product->description == ""){ $product->description = @$paper['abstract']; }
        if($product->status == ""){ $product->status = "Published"; }
        if($product->date == ""){ $product->date = @"{$paper['year']}-{$this->getMonth($paper['month'])}-01"; }
        if(!is_array($product->data)){ $product->data = array(); }
        if(!is_array($product->projects)){ $product->projects = array(); }
        if(!is_array($product->authors)){ $product->authors = array(); }
        if(!$product->exists()){
            $product->access_id = $me->getId();
            $product->bibtex_id = $bibtex_id;
        }
        if(count($product->authors) == 0){
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
        }
        
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['bibtex'] == $key){
                        if(!isset($product->data[$dkey]) || $product->data[$dkey] == ""){
                            $product->data[$dkey] = $field;
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
            if(is_array($bib->m_entries) && count($bib->m_entries) > 0){
                foreach($bib->m_entries as $bibtex_id => $paper){
                    $type = (isset(self::$bibtexHash[strtolower($paper['bibtex_type'])])) ? self::$bibtexHash[strtolower($paper['bibtex_type'])] : "Misc";
                    $product = $this->createProduct($paper, "Publication", $type, $bibtex_id);
                    if($product != null){
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
                    $this->addMessage("Duplicate");
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

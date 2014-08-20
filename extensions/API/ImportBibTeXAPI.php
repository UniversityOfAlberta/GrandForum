<?php

class ImportBibTeXAPI extends API{

    static $bibtexHash = array('inproceedings' => 'Proceedings Paper',
                               'proceedings' => 'Proceedings Paper',
                               'inbook' => 'Proceedings Paper',
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
                               'misc' => 'Misc');

    var $structure = null;

    function ImportBibTeXAPI(){
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
        $checkProduct = Product::newFromBibTeXId($bibtex_id);
        if($checkProduct->getId() != 0){
            // Make sure that this entry was not already entered
            return null;
        }
        $checkProduct = Product::newFromTitle($paper['title']);
        if($checkProduct->getId() != 0 && 
           $checkProduct->getCategory() == $category &&
           $checkProduct->getType() == $type){
            // Make sure that a product with the same title/category/type does not already exist
            return null;
        }
        $me = Person::newFromWgUser();
        $structure = $this->structure['categories'][$category]['types'][$type];
        $product = new Product(array());
        $product->title = str_replace("&#39;", "'", $paper['title']);
        $product->category = $category;
        $product->type = $type;
        $product->status = "Published";
        $product->date = @"{$paper['year']}-{$this->getMonth($paper['month'])}-01";
        $product->data = array();
        $product->projects = array();
        $product->authors = array();
        $product->access_id = $me->getId();
        $product->bibtex_id = $bibtex_id;
        $authors = explode(" and ", $paper['author']);
        foreach($authors as $author){
            $obj = new stdClass;
            $names = explode(",", $author);
            $firstName = trim($names[1]);
            $lastName = trim($names[0]);
            $obj->name = trim("$firstName $lastName");
            $product->authors[] = $obj;
        }
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['bibtex'] == $key){
                        $product->data[$dkey] = $field;
                        break;
                    }
                }
            }
        }
        $status = $product->create();
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
                    $type = (isset(self::$bibtexHash[$paper['bibtex_type']])) ? self::$bibtexHash[$paper['bibtex_type']] : "Misc";
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
                $this->addMessage($product->getId());
            }
            return $json;
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>

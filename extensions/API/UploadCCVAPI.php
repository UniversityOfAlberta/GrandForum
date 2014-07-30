<?php

class UploadCCVAPI extends API{

    var $structure = null;

    function UploadCCVAPI(){
        
    }

    function processParams($params){
        
    }
    
    function createProduct($paper, $category, $type, $ccv_id){
        $checkProduct = Product::newFromCCVId($ccv_id);
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
        $product->status = (isset($structure['ccv_status'][$paper['status']])) ? $structure['ccv_status'][$paper['status']] : "Rejected";
        $product->date = "{$paper['date_year']}-{$paper['date_month']}-01";
        $product->data = array();
        $product->projects = array();
        $product->authors = array();
        $product->access_id = $me->getId();
        $product->ccv_id = $ccv_id;
        $authors = explode(",", $paper['authors']);
        foreach($authors as $author){
            $obj = new stdClass;
            $obj->name = trim($author);
            $product->authors[] = $obj;
        }
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['ccvtk'] == $key){
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
        $ccv = $_FILES['ccv'];
        if($ccv['type'] == "text/xml" && $ccv['size'] > 0){
            $this->structure = Product::structure();
            $dir = dirname(__FILE__);
            $error = "";
            require_once($dir."/../../Classes/CCCVTK/common-cv.lib.php");
            $file_contents = file_get_contents($ccv['tmp_name']);
            $dom = new DOMDocument();
            $valid = $dom->loadXML($file_contents);
            if($valid){
                $cv = new CommonCV($ccv['tmp_name']);
                $conferencePapers = $cv->getConferencePapers();
                $journalPapers = $cv->getJournalPapers();
                $bookChapters = $cv->getBookChapters();
                $reviewedConferencePapers = $cv->getReviewedConferencePapers();
                $reviewedJournalPapers = $cv->getReviewedJournalPapers();
                $createdProducts = array();
                $errorProducts = array();
                foreach($conferencePapers as $ccv_id => $paper){
                    $product = $this->createProduct($paper, "Publication", "Conference Paper", $ccv_id);
                    if($product != null){
                        $createdProducts[] = $product;
                    }
                    else{
                        $errorProducts[] = $paper;
                    }
                }
                foreach($journalPapers as $ccv_id => $paper){
                    $product = $this->createProduct($paper, "Publication", "Journal Paper", $ccv_id);
                    if($product != null){
                        $createdProducts[] = $product;
                    }
                    else{
                        $errorProducts[] = $paper;
                    }
                }
                foreach($bookChapters as $ccv_id => $paper){
                    $product = $this->createProduct($paper, "Publication", "Book Chapter", $ccv_id);
                    if($product != null){
                        $createdProducts[] = $product;
                    }
                    else{
                        $errorProducts[] = $paper;
                    }
                }
            }
            else{
                $error = "There was an error reading the CCV file";
            }
            $json = array('created' => array(),
                          'error' => array());
            foreach($createdProducts as $product){
                $json['created'][] = $product->toArray();
            }
            foreach($errorProducts as $product){
                $json['error'][] = $product;
            }
            $obj = json_encode($json);
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.ccvUploaded($obj, "$error");
                    </script>
                </head>
            </html>
EOF;
            exit;
        }
        else{
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.ccvUploaded([], "The uploaded file was not in XML format");
                    </script>
                </head>
            </html>
EOF;
            exit;
        }
	}
	
	function isLoginRequired(){
		return true;
	}
}
?>

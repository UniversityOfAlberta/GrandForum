<?php

class UploadCCVAPI extends API{

    var $structure = null;

    function UploadCCVAPI(){
        
    }

    function processParams($params){
        
    }
    
    function createProduct($paper, $category, $type){
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
            require_once($dir."/../../Classes/CCCVTK/common-cv.lib.php");
            $cv = new CommonCV($ccv['tmp_name']);
            $conferencePapers = $cv->getConferencePapers();
            $journalPapers = $cv->getJournalPapers();
            $bookChapters = $cv->getBookChapters();
            $reviewedConferencePapers = $cv->getReviewedConferencePapers();
            $reviewedJournalPapers = $cv->getReviewedJournalPapers();
            $createdProducts = array();
            foreach($conferencePapers as $paper){
                $product = $this->createProduct($paper, "Publication", "Conference Paper");
                if($product != null){
                    $createdProducts[] = $product;
                }
            }
            foreach($journalPapers as $paper){
                $product = $this->createProduct($paper, "Publication", "Journal Paper");
                if($product != null){
                    $createdProducts[] = $product;
                }
            }
            foreach($bookChapters as $paper){
                $product = $this->createProduct($paper, "Publication", "Book Chapter");
                if($product != null){
                    $createdProducts[] = $product;
                }
            }
            foreach($createdProducts as $product){
                $json[] = $product->toArray();
            }
            $obj = json_encode($json);
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.ccvUploaded($obj);
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

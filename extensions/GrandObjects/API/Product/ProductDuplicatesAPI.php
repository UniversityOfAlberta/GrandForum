<?php

class ProductDuplicatesAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('category') != "" && 
           $this->getParam('title') != "" &&
           $this->getParam('id') != ""){
            $category = $this->getParam('category');
            $title = strtolower($this->getParam('title'));
            $id = $this->getParam('id');
            $allProducts = Product::getAllPapers('all', $category, 'both', false);
            $duplicates = array();
            $data = DBFunctions::execSQL("SELECT `id1`, `id2`
                                          FROM `grand_ignored_duplicates`
                                          WHERE `type` = 'my{$category}'");
            $ignores = array();
            foreach($data as $row){
                $ignores[$row['id1'].'_'.$row['id2']] = true;
                $ignores[$row['id2'].'_'.$row['id1']] = true;
            }
            foreach($allProducts as $product){
                if($product->getId() != $id){
                    $percent = 0;
                    similar_text(strtolower($product->getTitle()), $title, $percent);
                    if($percent >= 85){
	                    if(!isset($ignores[$id.'_'.$product->getId()])){
                            $duplicates[] = $product->toArray();
                        }
                    }
                }
            }
            echo json_encode($duplicates);
            exit;
        }
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){
        
    }
    
    function doDELETE(){
        
    }
	
}

?>

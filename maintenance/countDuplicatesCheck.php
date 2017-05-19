<?php
    /**
     *Used to count duplicate publications in the forum db using DOI check
    **/
    require_once( "commandLine.inc" );
    $count = 0;
    $products = DBFunctions::execSQL("SELECT * FROM grand_products");
    $doiarray = array();
    $duplicates = array();
    foreach($products as $product){
	$data = unserialize($product['data']);
	if(isset($data['doi'])){
	    $array = explode("org/" , $data['doi']);
	    $doi = end($array);
	    if(in_array($doi, $doiarray)){
	        if(in_array($doi, $duplicates)){
		    continue;
		}
		else{
		    $count++;
		    $duplicates[] = $doi;
		    //print_r($doi."\n\n");
                    $sql = "SELECT * FROM `grand_products` WHERE data LIKE '%\"doi\";s:%:\"%$doi\"%'";
                    $rows = DBFunctions::execSQL($sql);
		    $id = $rows[0]['id'];
		    DBFunctions::execSQL("DELETE FROM grand_products WHERE id = $id", true);
		}
	    }
	    else{
		$doiarray[] = $doi;
	    }
    	}
    }
    print_r($count);








?>

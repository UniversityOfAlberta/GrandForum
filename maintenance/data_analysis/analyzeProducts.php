<?php 

require_once('../commandLine.inc');

$xml_location = dirname(__FILE__)."/Products.xml";
if(file_exists($xml_location)){
	$xml = file_get_contents($xml_location);
	$simplexml = simplexml_load_string($xml);

	
    //$attributes = $simplexml->attributes();
    $product_cats = $simplexml->children();

    foreach($product_cats as $product_cat){
    	$cats = $product_cat->getName();
    	echo $cats ."\n";
    	
    	foreach ($product_cat as $cat){
    		$cat_name = $cat->getName();
    		$type = $cat['type'];
    		echo "--".$type."\n";

            $data_fields = array();
            foreach($cat->data->children() as $field){
                $data_fields[(string)$field] = "";
            }
            
    		$query = "SELECT * FROM grand_products p WHERE p.category='{$cat_name}' AND p.type='{$type}'";
    		$products = execSQLStatement($query);
    		
            $unmatched = 0;
    		foreach ($products as $product){
    			$product_data = unserialize($product['data']);

                $diff_keys = array_diff_key($product_data, $data_fields);
                if(!empty($diff_keys)){
                    echo "=====ID: [".$product['id']."]   UNMATCHED KEYS: ".implode(', ', array_keys($diff_keys))."\n";
                    $unmatched++;
                }  
    		}
            echo "Total Unmatched = ".$unmatched."\n\n";
    	}
        
    }
}
else{
	echo 'file not found'."\n";
}

function execSQLStatement($sql, $update=false){
	if($update == false){
		$dbr = wfGetDB(DB_SLAVE);
	}
	else {
		$dbr = wfGetDB(DB_MASTER);
		return $dbr->query($sql);
	}
	$result = $dbr->query($sql);
	$rows = null;
	if($update == false){
		$rows = array();
		while ($row = $dbr->fetchRow($result)) {
			$rows[] = $row;
		}
	}
	return $rows;
}
?>
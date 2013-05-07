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
    	//echo $cats ."\n";
    	
    	foreach ($product_cat as $cat){
    		$cat_name = $cat->getName();
    		$type = $cat['type'];
    		echo $cats ." / ".$type."\n";

            $data_fields = array();
            foreach($cat->data->children() as $field){
                $data_fields[(string)$field] = 0;
            }
            
    		$query = "SELECT * FROM grand_products p WHERE p.category='{$cat_name}' AND p.type='{$type}' AND p.deleted=0";
    		$products = execSQLStatement($query);
    		
            $total = count($products);
            echo "Total records: ".$total."\n";
            $complete_count = 0;

            $no_description = 0;
            $no_projects = 0;
            $no_authors = 0;

    		foreach ($products as $product){
    		    $complete = true;

                $projects = unserialize($product['projects']);
                $authors = unserialize($product['authors']);

                if(empty($product['description'])){
                    $no_description++;
                    $complete = false;
                }

                if(empty($projects)){
                    $no_projects++;
                    $complete = false;
                }

                if(empty($authors)){
                    $no_authors++;
                    $complete = false;
                }


            	$product_data = unserialize($product['data']);

                foreach($data_fields as $df_key => $df_count){
                    if(isset($product_data[$df_key]) && !empty($product_data[$df_key])){
                        $data_fields[$df_key]++;
                    }
                    else{
                        $complete = false;
                    }
                }   
                if($complete){
                    $complete_count++;
                }
    		}
            echo "Total Complete (all fields filled-in): ". round(($complete_count/$total)*100, 1) . "% \n";
            echo "Total with no Description: ". round(($no_description/$total)*100, 1) ."% \n";
            echo "Total with no Project associations: ". round(($no_projects/$total)*100, 1) ."% \n";
            echo "Total with no Author associations: ". round(($no_authors/$total)*100, 1) ."% \n";
            echo "Per each data field (filled-in / total): \n";
            foreach($data_fields as $key=>$val){
                echo "  --{$key} : ". round(($val/$total)*100, 1) ."% \n";
            }
            echo "\n\n";
            //exit;
           
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
<?php
require_once('../commandLine.inc');

$map_file = "Products.xml";
$ccv_tmpl = "ccv_template.xml";
$map = simplexml_load_file($map_file);

$ccv = simplexml_load_file($ccv_tmpl);


$user_id = 21;
$person = Person::newFromId($user_id);

$all_products = $person->getPapers("Publication");

$prod_sorted = array();

foreach($all_products as $p){
	$t = $p->getType();
	if(isset($prod_sorted[$t])){
		$prod_sorted[$t][] = $p;
	}
	else{
		$prod_sorted[$t] = array();
		$prod_sorted[$t][] = $p;
	}
}

$counter = 0;
foreach($prod_sorted as $type => $products){
	foreach($products as $product){
//	$type = $product->getType();
	//if($type == "Review Article" || $type == "Book Review"){
		//$ccv_pub = $ccv->section->section[0]->addChild("section");
		$res = mapItem($map->Publications->Publication, $product, $ccv->section->section[0]);

		if($res == 0){
			echo "NOT EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
		}else{
			echo ":-) EXPORTED========". $product->getType() ."  ||||  ". $product->getId() ."\n";
		}
		$counter += $res;
		
	//}
	}
}


echo $ccv->asXML();

$all_papers = count($all_products);
echo "\n\n";
echo "All Papers={$all_papers}; Exported={$counter}\n";

function mapItem($section, $product, $ccv){
	global $person;
	$type = $product->getType();
	$success = 0;
	foreach($section as $item){
		if(
			(($type == "Masters Thesis" || $type == "PHD Thesis") && ($type == $item['type']) && $person->isAuthorOf($product) 
			&& isset($item['supervised']) && $item['supervised']=="false" 
			&& isset($item['ccv_id']) && isset($item['ccv_name'])) 
			|| 
			(($type == "Masters Thesis" || $type == "PHD Thesis") && ($type == $item['type']) && !$person->isAuthorOf($product) 
			&& isset($item['supervised']) && $item['supervised']=="true" 
			&& isset($item['ccv_id']) && isset($item['ccv_name']))
			||
			(($type != "Masters Thesis" && $type != "PHD Thesis") && ($type == $item['type'])
			&& isset($item['ccv_id']) && isset($item['ccv_name']))
		){ 
			//if(($type == $item['type']) && isset($item['ccv_id']) && isset($item['ccv_name'])){
			$ccv_item = $ccv->addChild("section");
			$ccv_id = $item['ccv_id'];
			$ccv_name = $item['ccv_name'];

			$ccv_item->addAttribute('id', $ccv_id);
			$ccv_item->addAttribute('label', $ccv_name);
			//$ccv_item->addAttribute('recordId', "a8c67f9d407c4eda9cc7818ab89fa1ba");

			//Title
			$title = $product->getTitle();
			$status_field = $ccv_item->addChild("field");
			$status_field->addAttribute('id', $item->title['ccv_id']);
			$status_field->addAttribute('label', $item->title['ccv_name']);
			$val = $status_field->addChild('value');
			$val->addAttribute('type', "String");
			$status_field->value = $title;

			//ADD Status = Publishing Status
			$status = $product->getStatus();
			if($item->statuses){
				foreach($item->statuses->status as $s){
					if($s == $status && isset($s['lov_id']) && isset($s['lov_name'])){
						$status_field = $ccv_item->addChild("field");
						$status_field->addAttribute('id', $item->statuses['ccv_id']);
						$status_field->addAttribute('label', $item->statuses['ccv_name']);
						
						$lov = $status_field->addChild('lov');
						$lov->addAttribute('id', $s['lov_id']);
						$status_field->lov = $s['lov_name'];
					}
				}
			}

			//Add Fields
			$product_data = $product->getData();
			foreach($item->data->field as $field){
				if(isset($field['ccv_id'])){
					$status_field = $ccv_item->addChild("field");
					$status_field->addAttribute('id', $field['ccv_id']);
					$status_field->addAttribute('label', $field['ccv_name']);

					$val = $status_field->addChild('value');
					$val->addAttribute('type', "String");
					$key = (string) $field;
					//echo $product_data[$key] ."\n";
					$status_field->value = (isset($product_data[$key]))? $product_data[$key] : "";
				}
			}

			//Date
			$status_field = $ccv_item->addChild("field");
			$status_field->addAttribute('id', $item->date['ccv_id']);
			$status_field->addAttribute('label', $item->date['ccv_name']);
			$val = $status_field->addChild('value');
			$val->addAttribute('type', "YearMonth");
			$val->addAttribute('format', "yyyy/MM");
			$product_date = preg_split('/\-/', $product->getDate());
			$status_field->value = $product_date[0].'/'.$product_date[1];
			
			//Authors
			$status_field = $ccv_item->addChild("field");
			$status_field->addAttribute('id', $item->authors['ccv_id']);
			$status_field->addAttribute('label', $item->authors['ccv_name']);
			$val = $status_field->addChild('value');
			$val->addAttribute('type', "String");
			
			$product_authors = $product->getAuthors();
			$auth_arr = array();
			foreach($product_authors as $a){
				$auth_arr[] = $a->getNameForForms();
			}
			$status_field->value = implode(', ', $auth_arr);

			// //Description
			$status_field = $ccv_item->addChild("field");
			$status_field->addAttribute('id', $item->description['ccv_id']);
			$status_field->addAttribute('label', $item->description['ccv_name']);
			$val = $status_field->addChild('value');
			$val->addAttribute('type', "Bilingual");
			$bilin = $status_field->addChild("bilingual");
			$bilin->addChild("english");
			$bilin->english = substr($product->getDescription(), 0, 1000);

			$success = 1;
		}
	}

	return $success;

}



?>
<?php

require_once('commandLine.inc');

$string = file_get_contents("avoidcategories.json");
$json_a = json_decode($string, true);
$testing = json_encode($json_a, JSON_PRETTY_PRINT);

//print($testing);

foreach ($json_a as $category) {
	$sql =  "INSERT INTO `grand_avoid_categories` (`name`, `description`, `parent`, `level`, `alias_database_name`)
		VALUES
		(
    		'".$category["text"]."',
    		'".$category["description"]."',
    		0,
    		0,
    		''
		)";
	$testid = DBFunctions::execSQL($sql, true);
        $sql = "SELECT id FROM `grand_avoid_categories` WHERE `name` ='".$category["text"]."'";
        $data = DBFunctions::execSQL($sql);
	$parent_id = $data[0]["id"];
	if(array_key_exists('children', $category)){
		foreach($category["children"] as $subcategory){
		$sql =  "INSERT INTO `grand_avoid_categories` (`name`, `description`, `parent`, `level`, `alias_database_name`)
                VALUES
                (
                '".$subcategory["text"]."',
                '".$subcategory["description"]."',
                $parent_id,
                1,
                ''
                )";
        $testid = DBFunctions::execSQL($sql, true);
        $sql = "SELECT id FROM `grand_avoid_categories` WHERE `name` ='".$subcategory["text"]."'";
        $data = DBFunctions::execSQL($sql);
        $subparent_id = $data[0]["id"];
			if(array_key_exists('children', $subcategory)){
				foreach($subcategory["children"] as $subsubcategory){
				$sql =  "INSERT INTO `grand_avoid_categories` (`name`, `description`, `parent`, `level`, `alias_database_name`)
                		VALUES
                		(
                		'".$subsubcategory["text"]."',
                		'".$subsubcategory["description"]."',
                		$subparent_id,
                		2,
                		''
                		)";
        			$testid = DBFunctions::execSQL($sql, true);
				}
			}

		}
	}
}
?>

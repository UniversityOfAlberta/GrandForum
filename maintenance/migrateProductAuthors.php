<?php

require_once('commandLine.inc');

$products = Paper::getAllPapers('all', 'all', 'both');
$nProducts = count($products);
$i = 1;
foreach($products as $key => $product){
    $authors = $product->getAuthors();
    $newAuthors = array();
    foreach($authors as $author){
        if($author->getId() != 0){
            $newAuthors[] = $author->getId();
        }
        else{
            $newAuthors[] = $author->getName();
        }
    }
    $newAuthors = mysql_real_escape_string(serialize($newAuthors));
    $sql = "UPDATE `grand_products`
            SET `authors` = '$newAuthors',
                `last_modified` = '{$product->lastModified}'
            WHERE `id` = '{$product->getId()}'";
    DBFunctions::execSQL($sql, true);
    show_status($i, $nProducts);
    
    $i++;
}

?>

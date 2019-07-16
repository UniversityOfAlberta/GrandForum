<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $products = Product::getAllPapersDuring("all", "Publication", "grand", "2018-07-01", "2100-01-01");
    
    foreach($products as $product){
        if($product->getType() == "Journal Paper" || 
           $product->getType() == "Journal Abstract"){
            $issn = $product->getData('issn');
            $if = $product->getData('impact_factor');
            $ranking = $product->getData('category_ranking');
            if($issn != ""){
                $journal = Journal::newFromIssn($issn);
                if(isset($journal[0])){
                    echo $product->getId().": {$if} ({$ranking}) -> {$journal[0]->getImpactFactor()} ({$journal[0]->getRank()})\n";
                    $product->data['impact_factor'] = $journal[0]->getImpactFactor();
                    $product->data['category_ranking'] = $journal[0]->getRank();
                    $product->update();
                }
            }
        }
    }

?>


<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $products = Product::getAllPapersDuring("all", "Publication", "grand", "2018-07-01", "2100-01-01");
    
    foreach($products as $product){
        if(($product->getType() == "Journal Paper" || 
            $product->getType() == "Journal Abstract") &&
           $product->dateCreated >= "2019-01-01"){
            $issn = $product->getData('issn');
            $if = $product->getData('impact_factor');
            $ranking = $product->getData('category_ranking');
            if($issn != ""){
                $journal = null;
                $journals = Journal::getAllJournalsBySearch($issn);
                if(count($journals) == 1){
                    $journal = $journals[0];
                }
                else if(count($journals) > 1){
                    // Compare with 2017 journals, try to find a match
                    $data = DBFunctions::execSQL("SELECT description, ranking_numerator, ranking_denominator 
                                                  FROM grand_journals
                                                  WHERE year = '2017'
                                                  AND issn = '$issn'");
                    $category = "";
                    foreach($data as $row){
                        if("{$row['ranking_numerator']}/{$row['ranking_denominator']}" == $ranking){
                            // Match found, use this category
                            $category = $row['description'];
                            break;
                        }
                    }
                    if($category != ""){
                        foreach($journals as $journal){
                            if(strtoupper($journal->description) == strtoupper($category)){
                                // Match found, stick with this journal and break out of loop
                                break;
                            }
                        }
                    }
                    else{
                        // Didn't find a match, just choose the first one
                        $journal = $journals[0];
                    }
                }
                
                if($journal != null){
                    echo $product->getId().": {$if} ({$ranking}) -> {$journal->getImpactFactor()} ({$journal->getRank()})\n";
                    $product->data['impact_factor'] = $journal->getImpactFactor();
                    $product->data['category_ranking'] = $journal->getRank();
                    $product->update();
                }
            }
        }
    }

?>


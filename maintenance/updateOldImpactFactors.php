<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);

    $publications = DBFunctions::execSQL("SELECT * FROM bddEfec2_production.publications p, 
                                                        bddEfec2_production.jcr_current_journals j, 
                                                        bddEfec2_production.jcr_current_categories c
                                          WHERE p.venue = j.name
                                          AND j.id = c.jcr_current_journal_id
                                          AND p.type = 'Journal'");
    
    $processed = array();
    foreach($publications as $publication){
        $product = Product::newFromTitle($publication['title'], "Publication", "Journal Paper");
        if($product != null && $product->getId() != 0 && $product->getType() == "Journal Paper" && !isset($processed[$product->getId()])){
            $if = $product->getData('impact_factor');
            if($if == ""){
                $impact_factor = $publication['impact_factor']; // IF
                $rank = $publication['rank']; // Numerator
                $num = $publication['num_journals']; // Denominator
                $year = $publication['year']; // JCR Year
                if($year == null){
                    $year = 2011;
                }
                $date = date('Y', strtotime($product->getDate()) + 3600*24*6) - 1;
                if($year == $date && $impact_factor != ""){
                    echo $product->getTitle()." ($date) {$publication['impact_factor']}\n";
                    $product->data['impact_factor'] = $impact_factor;
                    $product->data['category_ranking'] = "{$rank}/{$num}";
                    $product->getAuthors();
                    $product->getContributors();
                    $product->update();
                    $processed[$product->getId()] = true;
                }
            }
        }
    }

?>


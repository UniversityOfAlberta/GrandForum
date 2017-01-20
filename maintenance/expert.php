<?php

    require_once('commandLine.inc');
    
    function writeText($fileName, $content, $id){
        $doc = html_entity_decode(strip_tags($content), ENT_QUOTES);
        file_put_contents($fileName, $doc);
    }
    
    function writeTrecText($fileName, $content, $id){
        $doc  = "<DOC>\n";
        $doc .= "<DOCNO>{$id}</DOCNO>\n";
        $doc .= "<TEXT>\n";
        $doc .= html_entity_decode(strip_tags($content), ENT_QUOTES);
        $doc .= "</TEXT>\n";
        $doc .= "</DOC>";
        file_put_contents($fileName, $doc);
    }
    
    $wgUser = User::newFromId(1);
    
    @mkdir("expert");
    @mkdir("expert/publications");
    @mkdir("expert/profiles");
    
    $products = Product::getAllPapers('all', 'all', 'grand', false);
    $people = Person::getAllPeople(NI);
    $productIds = array();
    foreach($products as $product){
        $productIds[$product->getId()] = $product;
        $content = "{$product->getTitle()}\n{$product->getDescription()}";
        writeText("expert/publications/{$product->getId()}.txt", $content, $product->getId());
    }
    
    $lines = array();
    foreach($people as $person){
        $myProducts = $person->getPapers('all');
        $profile = array();
        if($person->getProfile() != ""){
            $profile[] = "{$person->getProfile()}";
        }
        foreach($myProducts as $product){
            if(isset($productIds[$product->getId()])){
                $lines[] = "{$person->getId()} {$product->getId()}";
                $profile[] = "{$product->getTitle()}\n{$product->getDescription()}";
            }
        }
        if(count($myProducts) > 0){
            writeText("expert/profiles/{$person->getId()}.txt", implode("\n", $profile), $person->getId());
        }
    }
    file_put_contents("expert/experts.txt", implode("\n", $lines));
    
    
?>

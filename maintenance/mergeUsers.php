<?php

require_once('commandLine.inc');
$wgUser = User::newFromId(1);

if(count($argv) != 3){
    echo "Must provide two ids: php mergeUsers.php <duplicateUser> <goodUser>\n";
    exit;
}

$hqp = Person::newFromId($argv[1]);
$dupHQP = Person::newFromId($argv[2]);
if($hqp != null && $dupHQP != null &&
   $hqp->getId() != 0 && $dupHQP->getId() != 0){
   echo "{$hqp->getName()} -> {$dupHQP->getName()}\n";
    if($dupHQP->getEmail() == ""){
        DBFunctions::update('mw_user',
                            array('user_email' => $hqp->getEmail()),
                            array('user_id' => $dupHQP->getId()));
    }
    
    DBFunctions::update('grand_relations',
                        array('user2' => $dupHQP->getId()),
                        array('user2' => $hqp->getId()));
                        
    DBFunctions::update('grand_roles',
                        array('user_id' => $dupHQP->getId()),
                        array('user_id' => $hqp->getId()));
                        
    DBFunctions::update('grand_user_university',
                        array('user_id' => $dupHQP->getId()),
                        array('user_id' => $hqp->getId()));
                        
    DBFunctions::update('mw_user',
                        array('deleted' => 1),
                        array('user_id' => $hqp->getId()));
    $products = $hqp->getPapers("all", true, 'both', false, 'Public');
    foreach($products as $product){
        $changed = false;
        $authors = unserialize($product->authors);
        foreach($authors as $key => $author){
            if($author == $hqp->getId()){
                $authors[$key] = $dupHQP->getId();
                $changed = true;
            }
        }
        if($changed){
            echo "\tUPDATE Product: {$product->getId()}\n";
            DBFunctions::update('grand_products',
                                array('authors' => serialize($authors)),
                                array('id' => $product->getId()));
        }
    }
}

?>

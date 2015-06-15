<?php

require_once('Feed.php');
require_once('Title.php');

class ProtectedAtomFeed extends AtomFeed {
  function outitem($item){
    $title = Title::newFromText($item->getTitle());
    
    if ($title->userCanRead())
      parent::outItem($item);
  }   
}

?>

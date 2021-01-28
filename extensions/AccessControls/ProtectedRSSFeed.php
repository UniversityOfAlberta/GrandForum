<?php

class ProtectedRSSFeed extends RSSFeed {
  function outItem($item){
    $title = Title::newFromText($item->getTitle());
    
    if ($title->userCanRead()){
      parent::outItem($item);
    }
  }
}

?>

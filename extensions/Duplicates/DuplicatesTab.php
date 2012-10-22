<?php

class DuplicatesTab extends AbstractTab {

    var $handler;

    function DuplicatesTab($name, $handler){
        $this->handler = $handler;
        $this->AbstractTab($name);
    }

    function generateBody(){
        $this->html = $this->handler->addHTML();
        $this->handler->addScripts();
    }
}
?>

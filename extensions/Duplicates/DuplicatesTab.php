<?php

class DuplicatesTab extends AbstractTab {

    var $handler;

    function __construct($name, $handler){
        $this->handler = $handler;
        $this->AbstractTab($name);
    }

    function generateBody(){
        $this->html = $this->handler->addHTML();
        $this->handler->addScripts();
    }
}
?>

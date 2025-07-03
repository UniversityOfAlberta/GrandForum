<?php

class DuplicatesTab extends AbstractTab {

    var $handler;

    function __construct($name, $handler){
        $this->handler = $handler;
        parent::__construct($name);
    }

    function generateBody(){
        $this->html = $this->handler->addHTML();
        $this->handler->addScripts();
    }
}
?>

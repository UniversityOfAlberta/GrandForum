<?php

    class IntegerReportItem extends TextReportItem {

    function render(){
        global $wgOut;
        $min = $this->getAttr('min', 0);
		$max = $this->getAttr('max', 1000000000);
        $default = $this->getAttr('default', "");
        $value = $this->getBlobValue();
        if($value == ""){
            $value = $default;
        }
        $decimals = $this->getAttr('decimals', 0);
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $item = "<input type='text' name='{$this->getPostId()}' style='width:{$width};text-align:right;' value='{$value}' />";
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
        $wgOut->addHTML("<script type='text/javascript'>
            $('input[name={$this->getPostId()}]').forceNumeric({min: $min, max: $max, decimals: $decimals});
        </script>");
    }
}

?>

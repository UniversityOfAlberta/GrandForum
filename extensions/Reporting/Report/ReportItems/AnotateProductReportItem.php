<?php

class AnotateProductReportItem extends AbstractReportItem {
    
    function render(){
        global $wgOut;
        $product = Product::newFromId($this->productId);
        $item = $this->processCData($product->getCitation(true, false, false));
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $product = Product::newFromId($this->productId);
        $item = $this->processCData($product->getCitation(true, false, false));
        $wgOut->addHTML($item);
    }

}

?>

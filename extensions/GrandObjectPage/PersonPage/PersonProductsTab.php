<?php

class PersonProductsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonProductsTab($person, $visibility){
        parent::AbstractTab("Outputs");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = null;
            $prods = $this->person->getPapers('all', true, 'both');
            $categories = array();
            foreach($prods as $product){
                $categories[$product->getCategory()][$product->getTitle()] = $product;
            }
            
            foreach($categories as $cat => $products){
                $this->html .= "<h3>$cat</h3><ul>";
                ksort($products);
                foreach($products as $product){
                    $this->html .= "<li><a href='{$product->getUrl()}'>{$product->getTitle()}</a></li>";
                }
                $this->html .= "</ul>";
            }
        }
        return $this->html;
    }
}
?>

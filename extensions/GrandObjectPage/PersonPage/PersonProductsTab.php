<?php

class PersonProductsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $category;

    function __construct($person, $visibility, $category='all'){
        global $config;
        if($category == "all" || is_array($category)){
            parent::__construct(Inflect::pluralize($config->getValue("productsTerm")));
        }
        else{
            parent::__construct(Inflect::pluralize($category));
        }
        $this->person = $person;
        $this->visibility = $visibility;
        $this->category = $category;
    }

    function generateBody(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = null;
            if(is_array($this->category)){
                $prods = array();
                foreach($this->category as $category){
                    $prods = array_merge($prods, $this->person->getPapers($category, true, 'both'));
                }
            }
            else{
                $prods = $this->person->getPapers($this->category, true, 'both');
            }
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

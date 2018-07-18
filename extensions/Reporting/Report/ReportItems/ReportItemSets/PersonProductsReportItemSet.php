<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $productType = explode("|", $this->getAttr("productType", ""));
        $peerReviewed = $this->getAttr("peerReviewed", "");
        $status = $this->getAttr("status", "");
        $submitProductYear = (strtolower($this->getAttr("submitProductYear", "false")) == "true");
        $useProductYear = (strtolower($this->getAttr("useProductYear", "false")) == "true");
        $onlyUseStartDate = (strtolower($this->getAttr("onlyUseStartDate", "false")) == "true");
        $start_date = $this->getAttr("start", REPORTING_CYCLE_START);
        $end_date = $this->getAttr("end", REPORTING_CYCLE_END_ACTUAL);
        $includeHQP = (strtolower($this->getAttr("includeHQP", "true")) == "true");
        $onlyHQP = (strtolower($this->getAttr("onlyHQP", "false")) == "true");
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->personId);
        $categories = explode("|", $category);
        $products = array();
        foreach($categories as $cat){
            if($submitProductYear && isset($_GET['generatePDF']) && !isset($_GET['preview'])){
                $cat = DBFunctions::escape($cat);
                $year = DBFunctions::escape($this->getReport()->year-1);
                DBFunctions::execSQL("DELETE FROM grand_products_reported
                                      WHERE product_id IN (SELECT id FROM grand_products WHERE category = '{$cat}')
                                      AND user_id = '{$person->getId()}'
                                      AND year = '{$year}'", true);
            }
            $products = array_merge($products, $person->getPapersAuthored($cat, $start_date, $end_date, $includeHQP, true, false, $onlyUseStartDate));
            if($onlyHQP){
                foreach($products as $key => $product){
                    if($person->isAuthorOf($product)){
                        unset($products[$key]);
                    }
                }
                $hqps = $person->getHQPDuring($start_date, $end_date);
                foreach($products as $key => $product){
                    $found = false;
                    foreach($hqps as $hqp){
                        if($hqp->isAuthorOf($product)){
                            $found = true;
                            break;
                        }
                    }
                    if(!$found){
                        unset($products[$key]);
                    }
                }
            }
        }
        
        usort($products, function($a, $b){
            return (str_replace("0000-00-00", "9999-99-99", $a->getDate()) < str_replace("0000-00-00", "9999-99-99", $b->getDate())) ? 1 : -1;
        });
        
        if(is_array($products)){
            foreach($products as $prod){
                $type = explode(":", $prod->getType());
                if((implode("", $productType) == "" || in_array($type[0], $productType)) &&
                   ($peerReviewed == "" || ($prod->getData('peer_reviewed') == $peerReviewed)) &&
                   ($status == "" || ($prod->getStatus() == $status))){
                    $reportedYear = $prod->getReportedForPerson($this->personId);
                    if(!$useProductYear || $reportedYear == "" || $reportedYear == $this->getReport()->year-1){
                        if($submitProductYear && isset($_GET['generatePDF']) && !isset($_GET['preview'])){
                            DBFunctions::insert('grand_products_reported',
                                                array('product_id' => $prod->getId(),
                                                      'user_id' => $person->getId(),
                                                      'year' => $this->getReport()->year-1));
                        }
                        $tuple = self::createTuple();
                        $tuple['product_id'] = $prod->id;
                        $data[] = $tuple;
                    }
                }
            }
        }
        return $data;
    }

}

?>

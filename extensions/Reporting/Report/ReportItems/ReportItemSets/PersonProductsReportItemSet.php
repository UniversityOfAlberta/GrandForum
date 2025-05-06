<?php

class PersonProductsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $category = $this->getAttr("category", "all");
        $productType = explode("|", $this->getAttr("productType", ""));
        $peerReviewed = $this->getAttr("peerReviewed", "");
        $status = $this->getAttr("status", "");
        $limit = $this->getAttr("limit", "");
        $submitProductYear = (strtolower($this->getAttr("submitProductYear", "false")) == "true");
        $useProductYear = (strtolower($this->getAttr("useProductYear", "false")) == "true");
        $onlyUseStartDate = (strtolower($this->getAttr("onlyUseStartDate", "false")) == "true");
        $start_date = $this->getAttr("start", CYCLE_START);
        $end_date = $this->getAttr("end", CYCLE_END);
        $includeHQP = (strtolower($this->getAttr("includeHQP", "true")) == "true");
        $onlyHQP = (strtolower($this->getAttr("onlyHQP", "false")) == "true");
        $me = Person::newFromWgUser();
        $sort = strtolower($this->getAttr("sort", "normal")); // Can also be 'adaptive'
        $person = Person::newFromId($this->personId);
        $categories = explode("|", $category);
        $products = array();
        foreach($categories as $cat){
            if($submitProductYear && isset($_GET['generatePDF']) && !isset($_GET['preview'])){
                $cat = DBFunctions::escape($cat);
                $year = DBFunctions::escape($this->getReport()->year-1);
                if(count($productType) == 0){
                    DBFunctions::execSQL("DELETE FROM grand_products_reported
                                          WHERE product_id IN (SELECT id FROM grand_products WHERE category = '{$cat}')
                                          AND user_id = '{$person->getId()}'
                                          AND year = '{$year}'", true);
                }
                else{
                    foreach($productType as $type){
                        $type = DBFunctions::escape($type);
                        DBFunctions::execSQL("DELETE FROM grand_products_reported
                                              WHERE product_id IN (SELECT id FROM grand_products WHERE category = '{$cat}' AND type LIKE '{$type}%')
                                              AND user_id = '{$person->getId()}'
                                              AND year = '{$year}'", true);
                    }
                }
            }
            $products = array_merge($products, $person->getPapersAuthored($cat, $start_date, $end_date, $includeHQP, true, false, $onlyUseStartDate));
            if($onlyHQP){
                foreach($products as $key => $product){
                    if($person->isAuthorOf($product)){
                        unset($products[$key]);
                    }
                }
                $yearAgo = strtotime("{$start_date} -2 year"); // Extend the year to 2 years ago so that publications after graduation are still counted
                $yearAgo = date('Y-m-d', $yearAgo);
                $hqps = $person->getHQPDuring($yearAgo, $end_date);
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
        
        if($sort == "normal"){
            usort($products, function($a, $b){
                return (str_replace(ZOT, EOT, $a->getDate()).str_replace(ZOT, EOT, $a->getAcceptanceDate()) < 
                        str_replace(ZOT, EOT, $b->getDate()).str_replace(ZOT, EOT, $b->getAcceptanceDate())) ? 1 : -1;
            });
        }
        else if($sort == "adaptive"){
            usort($products, function($a, $b){
                $aDate = ($a->getDate() == ZOT) ? $a->getAcceptanceDate() : $a->getDate();
                $bDate = ($b->getDate() == ZOT) ? $b->getAcceptanceDate() : $b->getDate();
                return ($aDate < $bDate) ? 1 : -1;
            });
        }
        
        if(is_array($products)){
            foreach($products as $prod){
                $type = explode(":", $prod->getType());
                if((implode("", $productType) == "" || in_array($type[0], $productType)) &&
                   ($peerReviewed == "" || ($prod->getData('peer_reviewed') == $peerReviewed || ($prod->getData('peer_reviewed') == "" && $peerReviewed == "No"))) &&
                   ($status == "" || ($prod->getStatus() == $status))){
                    $reportedYear = $prod->getReportedForPerson($this->personId);
                    if(!$useProductYear || $reportedYear == "" || $reportedYear == $this->getReport()->year-1){
                        if($submitProductYear && isset($_GET['generatePDF']) && !isset($_GET['preview'])){
                            DBFunctions::insert('grand_products_reported',
                                                array('product_id' => $prod->getId(),
                                                      'user_id' => $person->getId(),
                                                      'year' => $this->getReport()->year-1));
                            DBCache::delete("reported_year_{$person->getId()}_{$prod->getId()}");
                        }
                        $tuple = self::createTuple();
                        $tuple['product_id'] = $prod->id;
                        $data[] = $tuple;
                    }
                }
            }
        }
        if($limit > 0){
            $data = array_slice($data,0,$limit);
        }
        return $data;
    }

}

?>

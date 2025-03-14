<?php

class ImportORCIDAPI extends API{
    
    static $orcidHash = array('book' => 'Book',
                              'book-chapter' => 'Book Chapter',
                              'conference-paper' => 'Conference Paper',
                              'conference-output' => 'Conference Paper',
                              'conference-presentation' => 'Conference Presentation',
                              'conference-poster' => 'Poster',
                              'conference-proceedings' => 'Conference Paper',
                              'journal-article' => 'Journal Paper',
                              'preprint' => 'Preprint',
                              'dissertation-thesis' => 'Masters Thesis',
                              'working-paper' => 'Working Copy',
                              'other' => 'Misc',
                              'annotation' => 'Annotation',
                              'book-review' => 'Book Review',
                              'journal-issue' => 'Journal issue or edition',
                              'review' => 'Review',
                              'transcription' => 'Transcription',
                              'translation' => 'Translation',
                              'blog-post' => 'Blog post',
                              'dictionary-entry' => 'Dictionary entry',
                              'encyclopedia-entry' => 'Encyclopedia entry',
                              'magazine-article' => 'Magazine/Newspaper Article',
                              'newspaper-article' => 'Magazine/Newspaper Article',
                              'report' => 'Report',
                              'public-speech' => 'Talk, interview, podcast or speech',
                              'website' => 'Website',
                              'artistic-performance' => 'Artistic output or performance',
                              'design' => 'Design',
                              'image' => 'Image',
                              'online-resource' => 'Interactive resource',
                              'moving-image' => 'Moving image or video',
                              'musical-composition' => 'Musical composition',
                              'sound' => 'Sound',
                              'cartographic-material' => 'Cartographic material',
                              'clinical-study' => 'Clinical study',
                              'data-set' => 'Dataset',
                              'data-management-plan' => 'Data management plan',
                              'physical-object' => 'Physical object',
                              'research-technique' => 'Research protocol or technique',
                              'research-tool' => 'Research tool',
                              'software' => 'Software',
                              'invention' => 'Invention',
                              'license' => 'License',
                              'patent' => 'Patent',
                              'registered-copyright' => 'Registered copyright',
                              'standards-and-policy' => 'Standards or policy',
                              'trademark' => 'Trademark'
                              );

    var $structure = null;

    function __construct(){
        
    }

    function processParams($params){
        
    }
    
    function createProduct($work, $category, $type, $overwrite=false){
        global $config;
        $title = $work->title->title->value;
        $field = @$work->{'journal-title'}->value;
        $orcid = $work->{'put-code'};
        
        $checkBibProduct = Product::newFromOrcid($orcid);
        $checkProduct = Product::newFromTitle($title);
        if((!$overwrite && $checkProduct->exists()) ||
           (!$overwrite && $checkBibProduct->exists())){
            return null;
        }
        if(@trim($orcid) != "" && $checkBibProduct->getId() != 0){
            // Make sure that this entry was not already entered
            $product = $checkBibProduct;
        }
        else if($checkProduct->getId() != 0 && 
           ($checkProduct->getCategory() == $category || $category == null) &&
           $checkProduct->getType() == $type){
            // Make sure that a product with the same title/category/type does not already exist
            $product = $checkProduct;
        }
        else{
            $product = new Product(array());
            $product->title = str_replace("&#39;", "'", $title);
            $product->category = $category;
            $product->type = $type;
            $product->contributors = array();
        }
        if(isset($this->structure['categories'][$category]['types'][$type])){
            // Make sure that the type actually exists
            $structure = $this->structure['categories'][$category]['types'][$type];
        }
        else{
            $found = false;
            foreach($this->structure['categories'] as $cat => $cats){
                if(isset($cats['types'][$type])){
                    // Then check if the type might exist in a different category
                    $found = true;
                    $product->category = $cat;
                    $product->type = $type;
                    $structure = $this->structure['categories'][$cat]['types'][$type];
                    break;
                }
            }
            if(!$found){
                // If not, then use the Misc type
                $structure = $this->structure['categories'][$category]['types']['Misc'];
                $product->type = "Misc: {$type}";
            }
        }
        $me = Person::newFromWgUser();
        $year = (isset($work->{'publication-date'}) && $work->{'publication-date'}->year != null) ? $work->{'publication-date'}->year->value : YEAR;
        $month = (isset($work->{'publication-date'}) && $work->{'publication-date'}->month != null) ? $work->{'publication-date'}->month->value : "01";
        $day = (isset($work->{'publication-date'}) && $work->{'publication-date'}->day != null) ? $work->{'publication-date'}->day->value : "01";
        $date = "{$year}-{$month}-{$day}";

        $product->getAuthors();
        $product->getContributors();

        if($product->description == ""){ $product->description = $work->{'short-description'}; }
        if($product->status == ""){ $product->status = "Published"; }
        if($product->date == ""){ $product->date = @"{$date}"; }
        if(!is_array($product->data)){ $product->data = array(); }
        if(!is_array($product->authors)){ $product->authors = array(); }
        if(!$product->exists()){
            $product->access_id = $me->getId();
        }
        $product->access = "Public";
        if(empty($product->authors)){
            $authors = $work->contributors->contributor;
            foreach($authors as $author){
                $obj = Person::newFromOrcid(@$author->{'contributor-orcid'}->path);
                if($obj == null || $obj->getId() == 0){
                    $obj = new stdClass;
                    $obj->name = $author->{'credit-name'}->value;
                }
                $product->authors[] = $obj;
            }
        }

        foreach($structure['data'] as $dkey => $dfield){
            if($dkey == "journal_title" || 
               $dkey == "published_in" ||
               $dkey == "publisher" ||
               $dkey == "event_title" ||
               $dkey == "department"){
                if(!isset($product->data[$dkey]) || $product->data[$dkey] == ""){
                    $product->data[$dkey] = $field;
                }
                break;
            }
        }
        if($work->url != null && isset($work->url->value) && $work->url->value != ""){
            if(!isset($product->data['url']) || $product->data['url'] == ""){
                $product->data['url'] = $work->url->value;
            }
        }

        if(!$product->exists()){
            $status = $product->create(false);
        }
        else{
            $product->deleted = 0;
            $status = $product->update(false);
        }
        if($status){
            $product = Product::newFromId($product->getId());
            return $product;
        }
        else{
            return null;
        }
    }
    
    function doAction($noEcho=false){
        global $wgMessage;
        if(isset($_COOKIE['access_token']) && isset($_COOKIE['orcid'])){
            $this->structure = Product::structure();
            $overwrite = (isset($_POST['overwrite']) && strtolower($_POST['overwrite']) == "yes") ? true : false;
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, "https://api.orcid.org/v3.0/{$_COOKIE['orcid']}/works");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                        
                "Authorization: Bearer {$_COOKIE['access_token']}",
                "Accept: application/json"
            ));
            //execute post
            $result = json_decode(curl_exec($ch));
            
            //close connection
            curl_close($ch);
            $putcodes = array();
            $bibtex = "";
            if(isset($result->error) && $result->error == "invalid_token"){
                $this->addError("Invalid Access Token");
                return;
            }
            $createdProducts = array();
            $errorProducts = array();
            foreach($result->group as $key => $work){
                $putcodes[] = $work->{'work-summary'}[0]->{'put-code'};
                if(count($putcodes) == 100 || $key + 1 == count($result->group)){
                    $ch = curl_init();

                    //set the url, number of POST vars, POST data
                    curl_setopt($ch, CURLOPT_URL, "https://api.orcid.org/v3.0/{$_COOKIE['orcid']}/works/".implode(",", $putcodes));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                    
                        "Authorization: Bearer {$_COOKIE['access_token']}",
                        "Accept: application/json"
                    ));
                    //execute post
                    $workResults = json_decode(curl_exec($ch));
                    //echo curl_exec($ch); exit;
                    
                    //close connection
                    curl_close($ch);
                    foreach($workResults->bulk as $workResult){
                        if(isset($workResult->work->citation) && strtolower($workResult->work->citation->{'citation-type'}) == "bibtex"){
                            // BibTeX citation found, use that
                            $bibtex .= $workResult->work->citation->{'citation-value'}."\n";
                        }
                        else{
                            // No BibTeX found, use ORCID object data
                            $type = (isset(self::$orcidHash[strtolower($workResult->work->type)])) ? self::$orcidHash[strtolower($workResult->work->type)] : "";
                            if($type != ""){
                                $product = $this->createProduct($workResult->work, null, $type, $overwrite);
                                if($product != null && $product !== false){
                                    $createdProducts[] = $product;
                                }
                                else{
                                    $errorProducts[] = $workResult->work;
                                }
                            }
                        }
                    }
                    
                    // Clear the array
                    $putcodes = array();
                }
                $syncInserts = array();
                $syncDeletes = array();
                foreach($createdProducts as $product){
                    $sqls = $product->syncAuthors(true);
                    foreach($sqls[1] as $s){
                        $syncInserts[$product->getId()] = $s;
                    }
                    $syncDeletes[$product->getId()] = $product->getId();
                }
                
                if(count($syncInserts) > 0){
                    DBFunctions::begin();
                    DBFunctions::execSQL("DELETE FROM `grand_product_authors` WHERE product_id IN (".implode(",", $syncDeletes).")", true, true);
                    DBFunctions::execSQL("INSERT INTO `grand_product_authors` (`author`, `product_id`, `order`)
                                          VALUES\n".implode(",\n",$syncInserts), true, true);
                    DBFunctions::commit();
                }
            }
            $_POST['bibtex'] = $bibtex."\n";
            $api = new ImportBibTeXAPI();
            $res = $api->doAction(true);
            if($res === false){
                $res = array('created' => array(),
                             'errors' => array());
            }
            foreach($createdProducts as $product){
                $res['created'][] = $product->toArray();
            }
            foreach($errorProducts as $product){
                $this->addMessage("{$product->title->title->value}");
            }
            $this->data = $res;
            return $res;
        }
        else {
            $this->addError("You have not yet authorized your ORCID account.\n");
        }
    }

    function isLoginRequired(){
        return true;
    }
}
?>

<?php

class PersonDataQualityTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonDataQualityTab($person, $visibility){
        parent::AbstractTab("Data Quality Checks");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    
    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($this->visibility['isMe'] || $me->isRoleAtLeast(MANAGER)){
            $wgOut->addScript(
                "<script type='text/javascript'>
                $(document).ready(function(){
                    $('#dataQualityAccordion').accordion({autoHeight: false, collapsible: true});
                    $('#citationAccordion').accordion({autoHeight: false, collapsible: true});
                    $('#duplicateProductsAccordion').accordion({autoHeight: false, collapsible: true, header: 'h4'});
                    $('#hqpErrorsAccordion').accordion({autoHeight: false, collapsible: true, header: 'h4'});
                    $('#productErrorsAccordion').accordion({autoHeight: false, collapsible: true, header: 'h4'});
                    $('.ui-accordion .ui-accordion-header a.accordion_hdr_lnk').click(function() {
                      window.location = $(this).attr('href');
                      return false;
                   });
                });


                </script>"
            );
            $wgOut->addHTML(
                "<style type='text/css'>
                    .ui-accordion .ui-accordion-header a{
                        display: inline !important;
                    }
                    .ui-accordion .ui-accordion-header a.accordion_hdr_lnk{
                        color: blue !important;
                        padding-left: 0 !important;
                    }
                    .ui-accordion .ui-accordion-header a.accordion_hdr_lnk:hover{
                        text-decoration: underline;
                    }
                </style>"
            );

            $errors = PersonDataQualityTab::getErrors($this->person->getId());

            $profile_checks = $this->getProfileChecks($errors);
            $hqp_checks = $this->getHqpChecks($errors);
            $product_checks = $this->getProductChecks($errors);
            $duplicates = $this->getMyProductDuplicates();

            $productTerm = $config->getValue('productsTerm');

            $this->html .=<<<EOF
            <div id='dataQualityAccordion'>
                <h3><a href='#'>Profile Errors</a></h3>
                <div>
                {$profile_checks}
                </div>
                <h3><a href='#'>HQP Errors</a></h3>
                <div>
                {$hqp_checks}
                </div>
                <h3><a href='#'>{$productTerm} Errors</a></h3>
                <div>
                {$product_checks}
                </div>
                <h3><a href='#'>{$productTerm} Duplicates</a></h3>
                <div>
                {$duplicates}
                </div>
            </div>
EOF;
        }
        

        return $this->html;
    }
    
    
    
    /**
     * Displays the profile checks for this user
     */
    function getProfileChecks($errors){
        global $wgOut, $wgUser;
        $me = Person::newFromId($this->person->getId());
        $html = "";// "<h3>Profile Errors:</h3>";
        if(!empty($errors['profile_errors'])){
            $html .= "<ul>";
            foreach ($errors['profile_errors'] as $es){
                $html .= "<li><strong>{$es}</strong></li>";
            }
            $html .= "</ul>";
        }
        else{
            $html .= "<strong>No Errors</strong>";
        }
        
        return $html;

    }

    /**
     * Displays the profile checks for this user
     */
    function getHqpChecks($errors){
        global $wgOut, $wgUser;
        $me = Person::newFromId($this->person->getId());
        
        $html = "";//"";//"<h3>Student Errors:</h3>";
        if(!empty($errors['student_errors'])){
            $error_students = array();
            $html = "<div id='hqpErrorsAccordion'>";
            foreach ($errors['student_errors'] as $name => $es){
                $student = Person::newFromName($name);
                $name_normal = $student->getNameForForms();
            
                $view_link = "<a class='accordion_hdr_lnk' href='".$student->getUrl()."'>[View]</a>";
                $html .= "<h4><a href='#'>{$name_normal}</a> {$view_link}</h4>";
                $html .= "<div><ul>";
                foreach ($es as $e){
                    //$error_students["{$e}"][] = $name_link;
                    $html .= "<li>{$e}</li>";
                }
                $html .= "</ul></div>";
            }
            $html .= "</div>";
            // $html .= "<ul>";
            // foreach($error_students as $e=>$s){
            //  $html .= "<li><b>{$e}:</b> ". implode(', ', $s) ."</li>";
            // }
            // $html .= "</ul>";
        }
        else{
            $html .= "<strong>No Errors</strong>";
        }
        return $html;

    }

    /**
     * Displays the profile checks for this user
     */
    function getProductChecks($errors){
        global $wgOut, $wgUser;
        $me = Person::newFromId($this->person->getId());
        
        $html = "";//"<h3>Product Errors:</h3>";
        if(!empty($errors['paper_errors'])){
            $html = "<div id='productErrorsAccordion'>";
            //$html .= "<strong>Products with incomplete information:</strong><ul>";
            foreach ($errors['paper_errors'] as $id => $es){
                $paper = Paper::newFromId($id);
                $name = $paper->getTitle();
                $paper_link = $paper->getUrl();

                $view_link = "<a class='accordion_hdr_lnk' href='{$paper_link}'>[View]</a>";
                $html .= "<h4><a href='#'>{$name}</a> {$view_link}</h4>";
                $html .= "<div><ul>";
                //$html .= "<li><a href='{$paper_link}'>{$name}</a></li>";
                //$html .= "{$name}:<ul>";
                foreach ($es as $e){
                  $html .= "<li>{$e}</li>";
                }
                $html .= "</ul></div>";
            }
            $html .= "</div>";
        }
        else{
            $html .= "<strong>No Errors</strong>";
        }
        return $html;

    }

    function getMyProductDuplicates(){
        ProductHandler::init();
	    MyProductHandler::init();
	    PersonHandler::init();
        $handlers = AbstractDuplicatesHandler::$handlers;
        $structure = Product::structure();
        
        $html = "<div id='duplicateProductsAccordion'>";
        foreach($structure['categories'] as $key => $cat){
            $key = str_replace(" ", "", $key);
            $dup_pub = new DuplicatesTab(Inflect::pluralize($key), $handlers["my$key"]);
            $dup_pub->generateBody();
            $html .= "<h4><a href='#'>".Inflect::pluralize($key)."</a></h4>
                      <div>
                        {$dup_pub->html}<br />
                      </div>";
        }
        $html .= "</div>";
        return $html;
    }

    static function getErrors($ni_id = null){
        global $config;
        if(!is_null($ni_id)){
            $person = Person::newFromId($ni_id);
        }
        else{
            return array();
        }

        $ni_errors = array();

        $name = $person->getName();
        $name_normal = $person->getNameForForms();
        
        if($person->isActive() ){
            $email = $person->getEmail();
            $email = ($email == "{$config->getValue('supportEmail')}")? "" : $email;
            $profile_pub = $person->getProfile();
            $profile_pri = $person->getProfile(true);
            $ni_uni = $person->getUniversity();
            $ni_university = $ni_uni['university'];
            $ni_department = $ni_uni['department'];
            $ni_position = $ni_uni['position'];

            if(empty($email)){ $ni_errors['profile_errors'][] = "Missing contact email"; }
            if(empty($ni_university)){ $ni_errors['profile_errors'][] = "Missing university"; }
            if(empty($ni_department)){ $ni_errors['profile_errors'][] = "Missing department"; }
            if(empty($ni_position)){ $ni_errors['profile_errors'][] = "Missing title"; }
            if(empty($ni_position)){ $ni_errors['profile_pub'][] = "Missing public profile"; }
            if(empty($ni_position)){ $ni_errors['profile_pri'][] = "Missing private profile"; }

            //Product completeness
            $papers = $person->getPapersAuthored("all", "2012-01-01 00:00:00", "2013-05-01 00:00:00", false);
            $person_paper_errors = array();

            foreach($papers as $paper){
                $paper_id = $paper->getId();
                $paper_title = $paper->getTitle();

                $errors = array();
                $completeness = $paper->getCompleteness();
                if(!$completeness['venue']){
                    $errors[] = "Does not have a venue.";
                }

                if(!$completeness['pages']){
                    $errors[] = "Does not have page information.";
                }

                if(!$completeness['publisher']){
                    $errors[] = "Does not have a publisher.";
                }

                if(!empty($errors)){
                    $person_paper_errors["{$paper_id}"] = $errors;
                }

            }
        
            $ni_errors['paper_errors'] = $person_paper_errors;
        

            //Students moved on vs thesis
            $student_errors = array();
            $students = $person->getHQP(true);
            foreach($students as $s){
                $student_name = $s->getName();
                $position = $s->getPosition();
                $university = $s->getUni();
                $department = $s->getDepartment();
                $errors = array();
                $ishqp = $s->isRole(HQP);

                if($ishqp && ($university == "" || $department == "" || $position == "")){
                    $errors[] = "Missing University/Department/Position";
                }

                //Only care about Masters and PhDs for thesis errors
                if(($position == "Masters Student" || $position == "PhD Student") && $ishqp){
                    
                    //Check for thesis and no exit data
                    $thesis = $s->getThesis();
                    if(!is_null($thesis)){
                        $moved = $s->getMovedOn();
                        if(empty($moved['studies']) && empty($moved['city']) && empty($moved['works']) && empty($moved['employer']) && empty($moved['country'])){
                            $errors[] = "Thesis but no exit data";
                        }
                    }
                    // else if(is_null($thesis) && !$ishqp){
                    //  $moved = $s->getMovedOn();
                    //  if(empty($moved['studies']) && empty($moved['city']) && empty($moved['works']) && empty($moved['employer']) && empty($moved['country'])){
                    //      $errors[] = "Past student is no longer an HQP, however has no thesis records, and is not marked as moved on.";
                    //  }
                    //  else{
                    //      $errors[] = "Past student is marked as moved on, however has no thesis record.";
                    //  }
                    // }
                }

                if(!empty($errors)){
                    $student_errors["{$student_name}"] = $errors;
                }
            }

            $ni_errors['student_errors'] = $student_errors;

        }
        
        return $ni_errors;
    }
    
}
?>

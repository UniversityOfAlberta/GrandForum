<?php

class ProjectDashboardTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectDashboardTab($project, $visibility){
        parent::AbstractTab("Dashboard");
        $this->project = $project;
        $this->visibility = $visibility;
        if(isset($_GET['showDashboard'])){
            echo $this->showDashboard($this->project, $this->visibility);
            exit;
        }
    }
    
    function tabSelect(){
        return "_.defer(function(){
            $('select.chosen').chosen();
            $('button[value=\"Edit Dashboard\"]').css('display', 'none');
            $('button[value=\"Save Dashboard\"]').css('display', 'none');
        });";
    }
    
    function handleEdit(){
        if($this->canEdit() && isset($_POST['top_products']) && is_array($_POST['top_products'])){
            DBFunctions::delete('grand_top_products',
                                array('type' => EQ('PROJECT'),
                                      'obj_id' => EQ($this->project->getId())));
            foreach($_POST['top_products'] as $product){
                if($product != ""){
                    $exploded = explode("_", $product);
                    $type = $exploded[0];
                    $productId = $exploded[1];
                    DBFunctions::insert('grand_top_products',
                                        array('type' => 'PROJECT',
                                              'obj_id' => $this->project->getId(),
                                              'product_type' => $type,
                                              'product_id' => $productId));
                }
            }
        }
    }
    
    function canEdit(){
        return ($this->project->userCanEdit() && !$this->project->isSubProject());
    }
    
    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || !$me->isSubRole("UofC"))){
            if(!$this->project->isSubProject()){
                $this->showTopProducts($this->project, $this->visibility);
            }
            $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
            $this->html .= "<script type='text/javascript'>
            $.get('{$this->project->getUrl()}?showDashboard', function(response){
                $('#ajax_dashboard').html(response);
            });
            _.defer(function(){
                $('button[value=\"Edit Dashboard\"]').css('display', 'none');
            });</script>";
        }
        return $this->html;
    }
    
    function generateEditBody(){
        if(!$this->project->isSubProject()){
            $this->showEditTopProducts($this->project, $this->visibility);
        }
        $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
        $this->html .= "<script type='text/javascript'>
            $.get('{$this->project->getUrl()}?showDashboard', function(response){
                $('#ajax_dashboard').html(response);
            });
            _.defer(function(){
                $('select.chosen:visible').chosen();
                $('select.chosen').each(function(i, el){
                    var prevVal = $(el).val();
                    if(prevVal != ''){
                        $('option[value=' + prevVal + ']', $('select.chosen').not(el)).prop('disabled', true);
                    }
                    $('select.chosen').trigger('chosen:updated');
                    $(el).change(function(e, p){
                        var id = $(this).val();
                        if(prevVal != ''){
                            $('option[value=' + prevVal + ']', $('select.chosen').not(this)).prop('disabled', false);
                        }
                        if(id != ''){
                            $('option[value=' + id + ']', $('select.chosen').not(this)).prop('disabled', true);
                        }
                        $('select.chosen').trigger('chosen:updated');
                        prevVal = id;
                    });
                });
                $('button[value=\"Save Dashboard\"]').css('display', 'none');
            });
        </script>";
    }
    
    private function optGroup($products, $category, $value){
        $html = "";
        $plural = Inflect::pluralize($category);
        $html .= "<optgroup label='$plural'>";
        $count = 0;
        foreach($products as $product){
            if($product instanceof Contribution && $category == "Contribution"){
                $selected = ($value == $product->getId()) ? "selected='selected'" : "";
                $year = $product->getStartYear();
                $html .= "<option value='CONTRIBUTION_{$product->getId()}' $selected>($year) {$product->getTitle()}</option>";
                $count++;
            }
            else if($product instanceof Paper && $category == $product->getCategory()){
                $selected = ($value == $product->getId()) ? "selected='selected'" : "";
                $year = substr($product->getDate(), 0, 4);
                $html .= "<option value='PRODUCT_{$product->getId()}' $selected>($year) {$product->getType()}: {$product->getTitle()}</option>";
                $count++;
            }
        }
        $html .= "</optgroup>";
        if($count > 0){
            return $html;
        }
        return "";
    }
    
    private function selectList($project, $value){
        $productStructure = Product::structure();
        $categories = @array_keys($productStructure['categories']);
        $allProducts = array_merge($project->getPapers('all', "0000-00-00", "2100-01-01"),
                                   $project->getContributions());
        $products = array();
        foreach($allProducts as $product){
            $date = $product->getDate();
            $products[$date."_{$product->getId()}"] = $product;
        }
        ksort($products);
        $products = array_reverse($products);
        $html = "<select class='chosen' name='top_products[]' style='max-width:800px;'>";
        $html .= "<option value=''>---</option>";
        foreach($categories as $category){
            $html .= $this->optGroup($products, $category, $value);
        }
        $html .= $this->optGroup($products, "Contribution", $value);
        $html .= "</select><br />";
        return $html;
    }
    
    function showEditTopProducts($project, $visibility){
        global $config;
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $this->html .= "<small>Select up to {$config->getValue('nProjectTopProducts')} research outcomes that you believe showcase the productivity of {$project->getName()} the greatest.  The order that you specify them in does not matter.  The ".strtolower(Inflect::pluralize($config->getValue('productsTerm')))." will be sorted in descending order by date.  These top ".strtolower(Inflect::pluralize($config->getValue('productsTerm')))." will be shown in the annual report. ie:
        <ul>
            <li>Publication in a high-impact journal</li>
            <li>Major partnerships or collaborations</li>
            <li>Licensing a product</li>
            <li>High profile awards</li>
            <li>Formation of a start-up company</li>
        </ul>
        </small>";
        $products = $project->getTopProducts();
        $i = 0;
        foreach($products as $product){
            $this->html .= $this->selectList($project, $product->getId());
            $i++;
        }
        for($i; $i < $config->getValue('nProjectTopProducts'); $i++){
            $this->html .= $this->selectList($project, "");
        }
        $this->html .= "<br /><button type='submit' value='Save Dashboard' name='submit'>Save Top Research Outcomes</button>
                        <input type='submit' value='Cancel' name='submit' />";
    }
    
    function showTopProducts($project, $visibility){
        global $config;
        $products = $project->getTopProducts();
        if(!$visibility['isLead'] && count($products) == 0){
            return;
        }
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $date = date('M j, Y', strtotime($project->getTopProductsLastUpdated()));
        if(count($products) > 0){
            $this->html .= "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='max-width: 800px;'>
                                <tr>
                                    <td align='center'><b>Year</b></td>
                                    <td align='center'><b>Category</b></td>
                                    <td align='center'><b>".$config->getValue('productsValue')."</b></td>
                                </th>";
            foreach($products as $product){
                if($product instanceof Contribution){
                    $year = $product->getStartYear();
                    $category = "Contribution";
                    $citation = "<a href='{$product->getUrl()}'>{$product->getTitle()}</a>";
                }
                else{
                    $year = substr($product->getDate(), 0, 4);
                    $category = $product->getCategory();
                    $citation = $product->getCitation();
                }
                if($year == "0000"){
                    $year = "";
                }
                if($year == YEAR){
                    $year = "<b><u>$year</u></b>";
                }
                $this->html .= "<tr>
                                    <td align='center'>{$year}</td>
                                    <td>{$category}</td>
                                    <td>{$citation}</td>
                                </tr>";
            }
            $this->html .= "</table><i>Last updated on: $date</i><br />";
        }
        else{
            $this->html .= "You have not entered any <i>Top Research Outcomes</i> yet<br />";
        }
        if($this->canEdit()){
            $this->html .= "<button type='submit' value='Edit Dashboard' name='submit'>Edit Top Research Outcomes</button>";
        }
    }
    
    function showDashboard($project, $visibility){
        global $wgOut, $config;
        $me = Person::newFromWgUser();
        $html = "";
        if($me->isLoggedIn()){
            $html .= "<h2>Dashboard</h2>";
            $html .= "<div id='dashboardAccordion'>";
            $html .= "<h3><a href='#'>Overall</a></h3>";
            $html .= "<div style='overflow: auto;'>";
            $dashboard = new DashboardTable(PROJECT_PUBLIC_STRUCTURE, $project);
            if(!$visibility['isLead']){
                $dashboard->filterCols(HEAD, array('Contributions'));
            }
            $html .= $dashboard->render(false, false);
            $html .= "</div>";
            $startYear = YEAR;
            if($project->deleted){
                $startYear = substr($project->getDeleted(), 0, 4)-1;
            }
            $phaseDates = $config->getValue("projectPhaseDates");
            for($i=$startYear; $i >= max(substr($phaseDates[1], 0, 4), substr($project->getCreated(), 0, 4)) - 1; $i--){
                $html .= "<h3><a href='#'>$i/".substr($i+1,2,2)."</a></h3>";
                $html .= "<div style='overflow: auto;'>";
                $dashboard = new DashboardTable(PROJECT_PUBLIC_STRUCTURE, $project, "$i-04-01", ($i+1)."-03-31");
                if(!$visibility['isLead']){
                    $dashboard->filterCols(HEAD, array('Contributions'));
                }
                $html .= $dashboard->render(false, false);
                $html .= "</div>";
            }
            $html .="</div>";
            $html .= "<script type='text/javascript'>
                $('#dashboardAccordion').accordion({autoHeight: false,
                                                    collapsible: true});
            </script>";
        }
        return $html;
    }

}    
    
?>

<?php

class ProjectDashboardTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectDashboardTab($project, $visibility){
        parent::AbstractTab("Dashboard");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function tabSelect(){
        return "_.defer(function(){
            $('select.chosen').chosen();
            $('input[value=\"Edit Dashboard\"]').css('display', 'none');
            $('input[value=\"Save Dashboard\"]').css('display', 'none');
        });";
    }
    
    function handleEdit(){
        if($this->canEdit() && isset($_POST['top_products']) && is_array($_POST['top_products'])){
            DBFunctions::delete('grand_top_products',
                                array('type' => EQ('PROJECT'),
                                      'obj_id' => EQ($this->project->getId())));
            foreach($_POST['top_products'] as $productId){
                if($productId != ""){
                    DBFunctions::insert('grand_top_products',
                                        array('type' => 'PROJECT',
                                              'obj_id' => $this->project->getId(),
                                              'product_id' => $productId));
                }
            }
        }
    }
    
    function canEdit(){
        return ($this->visibility['isLead'] && !$this->project->isSubProject());
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return;
        }
        if(!$this->project->isSubProject()){
            $this->showTopProducts($this->project, $this->visibility);
        }
        $this->showDashboard($this->project, $this->visibility);
        $this->html .= "<script type='text/javascript'>
        _.defer(function(){
            $('input[value=\"Edit Dashboard\"]').css('display', 'none');
        });</script>";
        return $this->html;
    }
    
    function generateEditBody(){
        if(!$this->project->isSubProject()){
            $this->showEditTopProducts($this->project, $this->visibility);
        }
        $this->showDashboard($this->project, $this->visibility);
        $this->html .= "<script type='text/javascript'>
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
                $('input[value=\"Save Dashboard\"]').css('display', 'none');
            });
        </script>";
    }
    
    private function optGroup($products, $category, $value){
        $html = "";
        $plural = Inflect::pluralize($category);
        $html .= "<optgroup label='$plural'>";
        $count = 0;
        foreach($products as $product){
            if($category == $product->getCategory()){
                $selected = ($value == $product->getId()) ? "selected='selected'" : "";
                $year = substr($product->getDate(), 0, 4);
                $html .= "<option value='{$product->getId()}' $selected>($year) {$product->getType()}: {$product->getTitle()}</option>";
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
        $allProducts = $project->getPapers('all', "0000-00-00", "2100-01-01");
        $products = array();
        foreach($allProducts as $product){
            $date = $product->getDate();
            $products[$date."_{$product->getId()}"] = $product;
        }
        ksort($products);
        $products = array_reverse($products);
        $html = "<select class='chosen' name='top_products[]' style='max-width:800px;'>";
        $html .= "<option value=''>---</option>";
        $html .= $this->optGroup($products, "Publication", $value);
        $html .= $this->optGroup($products, "Artifact", $value);
        $html .= $this->optGroup($products, "Activity", $value);
        $html .= $this->optGroup($products, "Presentation", $value);
        $html .= $this->optGroup($products, "Press", $value);
        $html .= $this->optGroup($products, "Award", $value);
        $html .= "</select><br />";
        return $html;
    }
    
    function showEditTopProducts($project, $visibility){
        $this->html .= "<h2>Top Products/Outputs</h2>";
        $this->html .= "<small>Select up to 10 products/outputs that you believe showcase the productivity of {$project->getName()} the greatest.  The order that you specify them in does not matter.  The products will be sorted in descending order by date.  These top products will be shown in the annual report.</small><br />";
        $products = $project->getTopProducts();
        $i = 0;
        foreach($products as $product){
            $this->html .= $this->selectList($project, $product->getId());
            $i++;
        }
        for($i; $i < 10; $i++){
            $this->html .= $this->selectList($project, "");
        }
        $this->html .= "<br /><button type='submit' value='Save Dashboard' name='submit'>Save Products/Outputs</button>
                        <input type='submit' value='Cancel' name='submit' />";
    }
    
    function showTopProducts($project, $visibility){
        $products = $project->getTopProducts();
        if(!$visibility['isLead'] && count($products) == 0){
            return;
        }
        $this->html .= "<h2>Top Products/Outputs</h2>";
        $date = date('M j, Y', strtotime($project->getTopProductsLastUpdated()));
        if(count($products) > 0){
            $this->html .= "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='max-width: 800px;'>
                                <tr>
                                    <td align='center'><b>Year</b></td>
                                    <td align='center'><b>Category</b></td>
                                    <td align='center'><b>Product</b></td>
                                </th>";
            foreach($products as $product){
                $year = substr($product->getDate(), 0, 4);
                if($year == "0000"){
                    $year = "";
                }
                if($year == YEAR){
                    $year = "<b><u>$year</u></b>";
                }
                $this->html .= "<tr>
                                    <td align='center'>{$year}</td>
                                    <td>{$product->getCategory()}</td>
                                    <td>{$product->getProperCitation()}</td>
                                </tr>";
            }
            $this->html .= "</table><i>Last updated on: $date</i><br />";
        }
        else{
            $this->html .= "You have not entered any <i>Top Products/Outputs</i> yet<br />";
        }
        if($this->canEdit()){
            $this->html .= "<button type='submit' value='Edit Dashboard' name='submit'>Edit Top Products/Outputs</button>";
        }
    }
    
    function showDashboard($project, $visibility){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = new DashboardTable(PROJECT_PUBLIC_STRUCTURE, $project);
            if($dashboard != null){
                $this->html .= "<h2>Dashboard</h2>";
                $this->html .= $dashboard->render(false, $visibility['isLead']);
            }
        }
    }

}    
    
?>

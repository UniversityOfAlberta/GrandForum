<?php

class PersonDashboardTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonDashboardTab($person, $visibility){
        parent::AbstractTab("Dashboard");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        if($this->canEdit() && isset($_POST['top_products']) && is_array($_POST['top_products'])){
            DBFunctions::delete('grand_top_products',
                                array('type' => EQ('PERSON'),
                                      'obj_id' => EQ($this->person->getId())));
            foreach($_POST['top_products'] as $productId){
                if($productId != ""){
                    DBFunctions::insert('grand_top_products',
                                        array('type' => 'PERSON',
                                              'obj_id' => $this->person->getId(),
                                              'product_id' => $productId));
                }
            }
        }
    }
    
    function canEdit(){
        return ($this->visibility['isMe']);
    }

    function generateBody(){
        $this->showTopProducts($this->person, $this->visibility);
        $this->showDashboard($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
        $this->showEditTopProducts($this->person, $this->visibility);
        $this->showDashboard($this->person, $this->visibility);
    }
    
    private function optGroup($products, $category, $value){
        $html = "";
        $plural = Inflect::pluralize($category);
        $html .= "<optgroup label='$plural'>";
        $count = 0;
        foreach($products as $product){
            if($category == $product->getCategory()){
                $selected = ($value == $product->getId()) ? "selected='selected'" : "";
                $html .= "<option value='{$product->getId()}' $selected>{$product->getType()}: {$product->getTitle()}</option>";
                $count++;
            }
        }
        $html .= "</optgroup>";
        if($count > 0){
            return $html;
        }
        return "";
    }
    
    private function selectList($person, $value){
        $allProducts = $person->getPapers('all', true, 'grand');
        $html = "<select name='top_products[]' style='max-width:800px;'>";
        $html .= "<option value=''>---</option>";
        $html .= $this->optGroup($allProducts, "Publication", $value);
        $html .= $this->optGroup($allProducts, "Artifact", $value);
        $html .= $this->optGroup($allProducts, "Activity", $value);
        $html .= $this->optGroup($allProducts, "Presentation", $value);
        $html .= $this->optGroup($allProducts, "Press", $value);
        $html .= $this->optGroup($allProducts, "Award", $value);
        $html .= "</select><br />";
        return $html;
    }
    
    function showEditTopProducts($person, $visibility){
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $products = $person->getTopProducts();
        $lastUpdated = $person->getTopProductsLastUpdated();
        $i = 0;
        foreach($products as $product){
            $this->html .= $this->selectList($person, $product->getId());
            $i++;
        }
        for($i; $i < 5; $i++){
            $this->html .= $this->selectList($person, "");
        }
        $this->html .= "<input type='submit' value='Save Dashboard' name='submit'>
                        <input type='submit' value='Cancel' name='submit'>";
    }
    
    function showTopProducts($person, $visibility){
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $products = $person->getTopProducts();
        $lastUpdated = $person->getTopProductsLastUpdated();
        $this->html .= "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='max-width: 800px;border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;'>
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
                                <td>{$year}</td>
                                <td>{$product->getCategory()}</td>
                                <td>{$product->getProperCitation()}</td>
                            </tr>";
        }
        $this->html .= "</table><br />";
        $this->html .= "<input type='submit' value='Edit Dashboard' name='submit'>";
    }
    
    function showDashboard($person, $visibility){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = null;
            $me = Person::newFromId($wgUser->getId());
            if($person->isRoleAtLeast(CNI) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(CNI))){
                if($visibility['isMe'] || $me->isRoleAtLeast(STAFF)){
                    // Display Private Dashboard
                    $dashboard = new DashboardTable(NI_PRIVATE_PROFILE_STRUCTURE, $person);
                }
                else{
                    // Display Public Dashboard
                    $dashboard = new DashboardTable(NI_PUBLIC_PROFILE_STRUCTURE, $person);
                }
            }
            else if($person->isRole(HQP) || $person->isRole(EXTERNAL) || ($person->isRole(INACTIVE) && $person->wasLastRole(HQP))){
                $dashboard = new DashboardTable(HQP_PUBLIC_PROFILE_STRUCTURE, $person);
            }
            if($dashboard != null){
                $this->html .= "<h2>Dashboard</h2>";
                $this->html .= $dashboard->render();
            }
            $this->html .= "<script type='text/javascript'>
                var completedRows = $('table.dashboard td:contains((Completed))').parent();
                completedRows.hide();
                if(completedRows.length > 0){
                    var last = completedRows.last();
                    var colspan = completedRows.children().length;
                    var newRow = $('<tr><td style=\'cursor:pointer;\' align=\'center\' colspan=\'' + colspan + '\'><b>Show Completed Projects</b></td></tr>');
                    newRow.hover(function(){
                        $(this).css('background', '#DDDDDD');
                    }, function(){
                        $(this).css('background', '#FFFFFF');
                    });
                    newRow.click(function(){
                        completedRows.show();
                        newRow.hide();
                    });
                    last.after(newRow);
                }
            </script>";
        }
    }
}
?>

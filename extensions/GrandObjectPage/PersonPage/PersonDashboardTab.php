<?php

class PersonDashboardTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonDashboardTab($person, $visibility){
        parent::AbstractTab("Dashboard");
        $this->person = $person;
        $this->visibility = $visibility;
        if(isset($_GET['showDashboard'])){
            echo $this->showDashboard($this->person, $this->visibility);
            exit;
        }
    }
    
    function tabSelect(){
        return "_.defer(function(){
            $('select.chosen').chosen();
            $('button[value=\"Edit Dashboard\"]:not(#realEdit)').css('display', 'none');
            $('button[value=\"Save Dashboard\"]:not(#realSave)').css('display', 'none');
        });";
    }
    
    function handleEdit(){
        if($this->canEdit() && isset($_POST['top_products']) && is_array($_POST['top_products'])){
            DBFunctions::delete('grand_top_products',
                                array('type' => EQ('PERSON'),
                                      'obj_id' => EQ($this->person->getId())));
            foreach($_POST['top_products'] as $product){
                if($product != ""){
                    $exploded = explode("_", $product);
                    $type = $exploded[0];
                    $productId = $exploded[1];
                    DBFunctions::insert('grand_top_products',
                                        array('type' => 'PERSON',
                                              'obj_id' => $this->person->getId(),
                                              'product_type' => $type,
                                              'product_id' => $productId));
                }
            }
        }
    }
    
    function canEdit(){
        return ($this->visibility['isMe']);
    }

    function generateBody(){
        $me = Person::newFromWgUser();
        $amount = 3;
        if($this->person->isRoleAtLeast(NI)){
            $amount = 5;
        }
        $this->showTopProducts($this->person, $this->visibility, $amount);
        $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
        if($me->isLoggedIn()){
            $this->html .= "<script type='text/javascript'>
                $.get('{$this->person->getUrl()}?showDashboard', function(response){
                $('#ajax_dashboard').html(response);
            });
            _.defer(function(){
                $('button[value=\"Edit Dashboard\"]:not(#realEdit)').css('display', 'none');
            });</script>";
        }
        return $this->html;
    }
    
    function generateEditBody(){
        $amount = 3;
        if($this->person->isRoleAtLeast(NI)){
            $amount = 5;
        }
        $this->showEditTopProducts($this->person, $this->visibility, $amount);
        $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
        $this->html .= "<script type='text/javascript'>
            $.get('{$this->person->getUrl()}?showDashboard', function(response){
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
                $('button[value=\"Save Dashboard\"]:not(#realSave)').css('display', 'none');
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
    
    private function selectList($person, $value){
        $productStructure = Product::structure();
        $categories = @array_keys($productStructure['categories']);
        $allProducts = array_merge($person->getPapers('all', true, 'grand', true, 'Public'),
                                   $person->getContributions());
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
    
    function showEditTopProducts($person, $visibility, $max=5){
        global $config;
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $this->html .= "<small>Select up to {$max} research outcomes that you believe showcase your productivity the greatest.  The order that you specify them in does not matter.  The ".strtolower(Inflect::pluralize($config->getValue('productsTerm')))." will be sorted in descending order by date.  These top ".strtolower(Inflect::pluralize($config->getValue('productsTerm')))." will be shown in your annual report.
        <ul>
            <li>Publication in a high-impact journal</li>
            <li>Major partnerships or collaborations</li>
            <li>Licensing a product</li>
            <li>High profile awards</li>
            <li>Formation of a start-up company</li>
        </ul></small><br />";
        $products = $person->getTopProducts();
        $i = 0;
        foreach($products as $product){
            if($i == $max){
                break;
            }
            $this->html .= $this->selectList($person, $product->getId());
            $i++;
        }
        for($i; $i < $max; $i++){
            $this->html .= $this->selectList($person, "");
        }
        $this->html .= "<br /><button id='realSave' type='submit' value='Save Dashboard' name='submit'>Save Top Research Outcomes</button>
                        <input type='submit' value='Cancel' name='submit' />";
    }
    
    function showTopProducts($person, $visibility, $max=5){
        global $config;
        $products = $person->getTopProducts();
        if(!$visibility['isMe'] && count($products) == 0){
            return;
        }
        $this->html .= "<h2>Top Research Outcomes</h2>";
        $date = date('M j, Y', strtotime($person->getTopProductsLastUpdated()));
        if(count($products) > 0){
            $this->html .= "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='max-width: 800px;'>
                                <tr>
                                    <td align='center'><b>Year</b></td>
                                    <td align='center'><b>Category</b></td>
                                    <td align='center'><b>".$config->getValue('productsTerm')."</b></td>
                                </th>";
            $i = 0;
            foreach($products as $product){
                if($i == $max){
                    break;
                }
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
                $year = substr($product->getDate(), 0, 4);
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
                $i++;
            }
            $this->html .= "</table><i>Last updated on: $date</i><br />";
        }
        else{
            $this->html .= "You have not entered any <i>Top Research Outcomes</i> yet<br />";
        }
        if($this->canEdit()){
            $this->html .= "<button id='realEdit' type='submit' value='Edit Dashboard' name='submit'>Edit Top Research Outcomes</button>";
        }
    }
    
    function showDashboard($person, $visibility){
        global $wgUser;
        $html = "";
        if($wgUser->isLoggedIn()){
            $dashboard = null;
            $me = Person::newFromId($wgUser->getId());
            if($person->isRoleAtLeast(NI) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(NI))){
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
                $html .= "<h2>Dashboard</h2>";
                $html .= $dashboard->render(false, $visibility['isMe']);
            }
            $html .= "<script type='text/javascript'>
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
        return $html;
    }
}
?>

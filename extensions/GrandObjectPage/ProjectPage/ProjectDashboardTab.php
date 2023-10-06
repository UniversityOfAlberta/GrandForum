<?php

class ProjectDashboardTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function __construct($project, $visibility){
        parent::__construct("Dashboard");
        $this->project = $project;
        $this->visibility = $visibility;
        if(isset($_GET['showDashboard'])){
            QueryableTable::$idCounter = 1000;
            echo $this->showDashboard($this->project, $this->visibility);
            exit;
        }
    }
    
    function tabSelect(){
        return "_.defer(function(){
            $('select.chosen').chosen();
            $('button[value=\"Edit Dashboard\"]:not(#editTopResearchOutcomes):not(#editTechnologyEvaluationAdoption):not(#editPolicy)').css('display', 'none');
            $('button[value=\"Save Dashboard\"]:not(#editTopResearchOutcomes):not(#editTechnologyEvaluationAdoption):not(#editPolicy)').css('display', 'none');
            $('div#dashboard input[value=\"Cancel\"]:not(#cancelTopResearchOutcomes):not(#cancelTechnologyEvaluationAdoption):not(#cancelPolicy)').css('display', 'none');
        });";
    }
    
    function handleEdit(){
        global $config;
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
            if($config->getValue('projectTechEnabled')){
                $this->project->technology = array(
                    'response1'      => $_POST['response1'],
                    'response2'      => $_POST['response2'],
                    'response2_yes1' => $_POST['response2_yes1'],
                    'response2_yes2' => $_POST['response2_yes2'],
                    'response3'      => $_POST['response3'],
                    'response4'      => $_POST['response4']
                );
                $this->project->policy = array(
                    'policy'         => $_POST['policy'],
                    'policy_yes'     => $_POST['policy_yes']
                );
                $this->project->saveTechnology();
                $this->project->savePolicy();
            }
            if(isset($_POST['upToDate'])){
                $year = date('Y', time() - (3 * 30 * 24 * 60 * 60));
                $this->project->saveUpToDate($year, $_POST['upToDate']);
            }
        }
    }
    
    function canEdit(){
        return ($this->project->userCanEdit() && !$this->project->isSubProject());
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function generatePDFBody(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || !$me->isSubRole("UofC"))){
            if(!$this->project->isSubProject()){
                $this->showTopProducts($this->project, $this->visibility);
                if($config->getValue('projectTechEnabled')){
                    $this->showTechnologyEvaluationAdoption($this->project, $this->visibility);
                    $this->showPolicy($this->project, $this->visibility);
                }
            }
        }
    }
    
    function generateBody(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || !$me->isSubRole("UofC"))){
            $this->showUpToDate($this->project, $this->visibility);
            if(!$this->project->isSubProject()){
                $this->showTopProducts($this->project, $this->visibility);
                if($config->getValue('projectTechEnabled')){
                    $this->showTechnologyEvaluationAdoption($this->project, $this->visibility);
                    $this->showPolicy($this->project, $this->visibility);
                }
            }
            $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
            $this->html .= "<script type='text/javascript'>
            $.get('{$this->project->getUrl()}?showDashboard', function(response){
                $('#ajax_dashboard').html(response);
            });
            _.defer(function(){
                $('button[value=\"Edit Dashboard\"]:not(#editTopResearchOutcomes):not(#editTechnologyEvaluationAdoption):not(#editPolicy)').css('display', 'none');
            });</script>";
        }
        return $this->html;
    }
    
    function generateEditBody(){
        global $config;
        $this->showEditUpToDate($this->project, $this->visibility);
        if(!$this->project->isSubProject()){
            $this->showEditTopProducts($this->project, $this->visibility);
            if($config->getValue('projectTechEnabled')){
                $this->showEditTechnologyEvaluationAdoption($this->project, $this->visibility);
                $this->showEditPolicy($this->project, $this->visibility);
            }
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
                $('button[value=\"Save Dashboard\"]:not(#editTopResearchOutcomes):not(#editTechnologyEvaluationAdoption):not(#editPolicy)').css('display', 'none');
                $('div#dashboard input[value=\"Cancel\"]:not(#cancelTopResearchOutcomes):not(#cancelTechnologyEvaluationAdoption):not(#cancelPolicy)').css('display', 'none');
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
        $html = "<div style='margin-bottom:2px;'><select class='chosen' name='top_products[]' style='max-width:800px;'>";
        $html .= "<option value=''>---</option>";
        foreach($categories as $category){
            $html .= $this->optGroup($products, $category, $value);
        }
        $html .= $this->optGroup($products, "Contribution", $value);
        $html .= "</select></div>";
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
        $this->html .= "<br /><button id='editTopResearchOutcomes' type='submit' value='Save Dashboard' name='submit'>Save</button>
                        <input id='cancelTopResearchOutcomes' type='submit' value='Cancel' name='submit' />";
    }
    
    function showEditTechnologyEvaluationAdoption($project, $visibility){
        if(!$visibility['isLead']){
            return;
        }
        $technology = $project->getTechnology();
        $options = array("No",
                         "Yes, only assessed",
                         "Yes, only adopted",
                         "Yes, both assessed and adopted");
        $blankSelected = ($technology['response1'] == "") ? "selected='selected'" : "";
        $response1 = "<select name='response1'>
                        <option value='' $blankSelected>---</option>";
                        foreach($options as $option){
                            $selected = @($technology['response1'] == $option) ? "selected='selected'" : "";
                            $response1 .= "<option value='$option' $selected>$option</option>";
                        }
        $response1 .= "</select>";
        $options = array("Level 1",
                         "Level 2",
                         "Level 3",
                         "Level 4",
                         "Level 5",
                         "Level 6",
                         "Level 7",
                         "Level 8",
                         "Level 9");
        $blankSelected = ($technology['response3'] == "") ? "selected='selected'" : "";
        $response3 = "<select name='response3'>
                        <option value='' $blankSelected>---</option>";
                        foreach($options as $option){
                            $selected = @($technology['response3'] == $option) ? "selected='selected'" : "";
                            $response3 .= "<option value='$option' $selected>$option</option>";
                        }
        $response3 .= "</select>";
        $this->html .= "<br /><br />
                        <h2>Technology Evaluation/Adoption</h2>
                        <b>Have your research group developed any new technology that has been assessed and/or adopted by a third party organization?</b><br />
                        {$response1}<br />
                        <br />
                        <div id='tech_yes' style='display:none;'>
                            <b>Please provide the name of the technology:</b><br />
                            <input type='text' name='response2' value='{$technology['response2']}' size='50' />
                            <br /><br />
                            <div id='tech_yes1' style='display:none;'>
                                <b>Please provide the name, sector and country of the third party organization which assessed your technology:</b>
                                <textarea style='height:100px;' name='response2_yes1'>{$technology['response2_yes1']}</textarea>
                                <br />
                                <br />
                            </div>
                            <div id='tech_yes2' style='display:none;'>
                                <b>Please provide the name, sector and country of the third party organization which adopted your technology:</b>
                                <textarea style='height:100px;' name='response2_yes2'>{$technology['response2_yes2']}</textarea>
                                <br />
                                <br />
                            </div>
                            <b>Based on the definitions provided by Innovation Canada in the link below, please indicate the Technology Readiness Level (TRL) of the technology:</b><br />
                            {$response3}<br />
                            <br />
                            <b>Please provide a brief description of your technology:</b>
                            <textarea style='height:100px;' name='response4'>{$technology['response4']}</textarea>
                            <br />
                            <p><small>Note: If your research group has developed more than one technology that have been assessed and/or adopted by a third party organization, please contact the FES office at <a href='mailto:fes@ualberta.ca'>fes@ualberta.ca</a></small></p>
                            <p><small>Innovation Canada info: <a target='_blank' href='https://www.ic.gc.ca/eic/site/080.nsf/eng/00002.html'>https://www.ic.gc.ca/eic/site/080.nsf/eng/00002.html</a></small></p>
                        </div>
                        <script type='text/javascript'>
                            $('[name=response1]').change(function(){
                                var val = $(this).val();
                                if(val != 'No' && val != ''){
                                    $('#tech_yes').show();
                                    $('#tech_yes1').hide();
                                    $('#tech_yes2').hide();
                                    if(val == 'Yes, only assessed' ||
                                       val == 'Yes, both assessed and adopted'){
                                        $('#tech_yes1').show();
                                    }
                                    if(val == 'Yes, only adopted' ||
                                            val == 'Yes, both assessed and adopted'){
                                        $('#tech_yes2').show();
                                    }
                                }
                                else{
                                    $('#tech_yes').hide();
                                }
                            });
                            $('[name=response1]').change();
                        </script>";
        $this->html .= "<button id='editTechnologyEvaluationAdoption' type='submit' value='Save Dashboard' name='submit'>Save</button>
                        <input id='cancelTechnologyEvaluationAdoption' type='submit' value='Cancel' name='submit' /><br /><br />";
    }
    
    function showEditPolicy($project, $visibility){
        if(!$visibility['isLead']){
            return;
        }
        $policy = $project->getPolicy();
        $options = array("Yes",
                         "No");
        $blankSelected = ($policy['policy'] == "") ? "selected='selected'" : "";
        $select = "<select name='policy'>
                        <option value='' $blankSelected>---</option>";
                        foreach($options as $option){
                            $selected = @($policy['policy'] == $option) ? "selected='selected'" : "";
                            $select .= "<option value='$option' $selected>$option</option>";
                        }
        $select .= "</select>";
        $this->html .= "<h2>Contributions to Government Policy or Regulation</h2>
                        <p><small>The CFREF definition of a contribution to policy or regulation is as follows: &quot;A contribution is defined as a direct, structured engagement with policy makers at a municipal, provincial, or federal level, and in Aboriginal governments, for the purposes of informing policy or regulatory development, such as testimony before a parliamentary committee, service on a government appointed panel, partnership in a research activity, or adoption of a policy or regulation that explicitly draws upon a research outcome. It does not include lobbying, publication in policy journals (regardless of stated impacts of those journals), or op-eds.&quot;</small></p>
                        <b>Have you made contributions at any level to government policy or regulation?</b><br />
                        {$select}<br />
                        <br />
                        <div id='policy_yes' style='display:none;'>
                            <b>Contribution Description:</b>
                            <textarea style='height:100px;' name='policy_yes'>{$policy['policy_yes']}</textarea>
                            <br />
                            <br />
                        </div>
                        <script type='text/javascript'>
                            $('[name=policy]').change(function(){
                                var val = $(this).val();
                                if(val != 'No' && val != ''){
                                    $('#policy_yes').show();
                                    if(val == 'Yes'){
                                        $('#policy_yes').show();
                                    }
                                }
                                else{
                                    $('#policy_yes').hide();
                                }
                            });
                            $('[name=policy]').change();
                        </script>";
        $this->html .= "<button id='editPolicy' type='submit' value='Save Dashboard' name='submit'>Save</button>
                        <input id='cancelPolicy' type='submit' value='Cancel' name='submit' /><br /><br />";
    }
    
    function showUpToDate($project, $visibility){
        global $config;
        if($config->getValue('networkType') == "CFREF"){
            $year = date('Y', time() - (3 * 30 * 24 * 60 * 60));
            $upToDate = $project->getUpToDate($year);
            if($upToDate){
                $this->html .= "<p><b>The information in this section is current as per the end of Fiscal Year (".($year-1)."-".($year).")</b></p>\n";
            }
        }
    }
    
    function showEditUpToDate($project, $visibility){
        global $config;
        if($config->getValue('networkType') == "CFREF"){
            $year = date('Y', time() - (3 * 30 * 24 * 60 * 60));
            $upToDate = $project->getUpToDate($year);
            $field = new SingleCheckBox("upToDate", "Up To Date", $upToDate, array("&nbsp;<b>The information in this section is current as per the end of Fiscal Year (".($year-1)."-".($year).")</b>" => 1));
            $this->html .= "<input type='hidden' name='upToDate' value='0' />{$field->render()}";
        }
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
            $this->html .= "<button id='editTopResearchOutcomes' class='pdfnodisplay' type='submit' value='Edit Dashboard' name='submit'>Edit Top Research Outcomes</button>";
        }
    }
    
    function showTechnologyEvaluationAdoption($project, $visibility){
        if(!$visibility['isLead']){
            return;
        }
        $technology = $project->getTechnology();
        $this->html .= "<br /><br />
                        <h2>Technology Evaluation/Adoption</h2>
                        <div>
                            <b>Have your research group developed any new technology that has been assessed and/or adopted by a third party organization?</b><br />
                            {$technology['response1']}
                        </div><br />";
        if($technology['response1'] != "" && $technology['response1'] != "No"){
            $this->html .= "<div>
                                <b>Please provide the name of the technology:</b><br />
                                {$technology['response2']}
                            </div><br />";
            if($technology['response1'] == 'Yes, only assessed' || 
               $technology['response1'] == 'Yes, both assessed and adopted'){
                $this->html .= "<div>
                                    <b>Please provide the name, sector and country of the third party organization which assessed your technology:</b><br />
                                    ".nl2br($technology['response2_yes1'])."
                                </div><br />";
            }
            if($technology['response1'] == 'Yes, only adopted' || 
               $technology['response1'] == 'Yes, both assessed and adopted'){
                $this->html .= "<div>
                                    <b>Please provide the name, sector and country of the third party organization which adopted your technology:</b><br />
                                    ".nl2br($technology['response2_yes2'])."
                                </div><br />";
            }
            $this->html .= "<div>
                                <b>Based on the definitions provided by Innovation Canada in the link below, please indicate the Technology Readiness Level (TRL) of the technology:</b><br />
                                {$technology['response3']}
                            </div><br />";
            $this->html .= "<div>
                                <b>Please provide a brief description of your technology:</b><br />
                                ".nl2br($technology['response4'])."
                            </div><br />";
        }
        if($this->canEdit()){
            $this->html .= "<button id='editTechnologyEvaluationAdoption' class='pdfnodisplay' type='submit' value='Edit Dashboard' name='submit'>Edit Technology</button><br />";
        }
    }
    
    function showPolicy($project, $visibility){
        if(!$visibility['isLead']){
            return;
        }
        $policy = $project->getPolicy();
        $this->html .= "<br /><br />
                        <h2>Contributions to Government Policy or Regulation</h2>
                        <div>
                            <b>Have you made contributions at any level to government policy or regulation?</b><br />
                            {$policy['policy']}
                        </div><br />";
        if($policy['policy'] == "Yes"){
            $this->html .= "<div>
                                <b>Contribution Description:</b><br />
                                ".nl2br($policy['policy_yes'])."
                            </div><br />";
        }
        if($this->canEdit()){
            $this->html .= "<button id='editPolicy' class='pdfnodisplay' type='submit' value='Edit Dashboard' name='submit'>Edit Policy</button><br />";
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

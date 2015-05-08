<?php

class NSERCRangeVariableTab extends NSERCVariableTab {

    var $startYear = "";
    var $endYear = "";

    function NSERCRangeVariableTab($label, $from, $to, $startYear, $endYear){
        global $wgOut;
        ini_set('memory_limit','256M');
        parent::NSERCVariableTab($label, $from, $to, $startYear);
        $this->startYear = $startYear;
        $this->endYear = $endYear;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $config;
        $label = $this->label;

        $foldscript = "
<script type='text/javascript'>
function mySelect(form){ form.select(); }
function ShowOrHide(d1, d2) {
    if (d1 != '') DoDiv(d1);
    if (d2 != '') DoDiv(d2);
}
function DoDiv(id) {
    var item = null;
    if (document.getElementById) {
        item = document.getElementById(id);
    } else if (document.all) {
        item = document.all[id];
    } else if (document.layers) {
        item = document.layers[id];
    }
    if (!item) {
    }
    else if (item.style) {
        if (item.style.display == 'none') { item.style.display = ''; }
        else { item.style.display = 'none'; }
    }
    else { item.visibility = 'show'; }
}
function showDiv(div_id, details_div_id){   
    details_div_id = '#' + details_div_id;
    $(details_div_id).html( $(div_id).html() );
    $(details_div_id).show();
}

</script>
<style media='screen,projection' type='text/css'>
#details_div, .details_div{
    border: 1px solid #CCCCCC;
    margin-top: 10px;
    padding: 10px;
    position: relative;
    width: 980px;
} 
</style>
";
        
        $this->showContentsTable();

        if(ArrayUtils::get_string($_GET, 'year') == "tabs_{$this->startYear}-{$this->endYear}_".$label){
        switch (ArrayUtils::get_string($_GET, 'summary')) {
        /*
        case 'table2':
            $wgOut->addScript($foldscript);
            $this->html .= "<a id='Grand'></a><h2>NCE tables</h2>";
            //self::show_grand_table2();
            break;
            
        case 'table3':
            $this->html .= "<a id='Grand'></a><h2>NCE tables</h2>";
            self::showHQPTable();
            break;
        */
        case 'grand':
            $wgOut->addScript($foldscript);
            $this->html .= "<a id='Table2.1'></a><h2>Contributions</h2>";
            self::showContributionsTable();
            $this->html .= "<a id='Table2.2'></a><h2>Contributions By Project</h2>";
            self::showContributionsByProjectTable();
            $this->html .= "<a id='Grand'></a><h2>NCE tables</h2>";
            self::showGrandTables();
            self::showDisseminations();
            self::showArtDisseminations();
            self::showActDisseminations();
            self::showPublicationList();
            if(isExtensionEnabled('Reporting')){
                self::showProjectRequests();
            }
            break;
        }
        }
        //$this->showProductivity();
        
        return $this->html;
    }

    function showContentsTable(){
        global $wgServer, $wgScriptPath, $config;
        $label = $this->label;

        $this->html .= "
            <h2>Jan{$this->startYear}-Mar{$this->endYear}</h2>
            <table class='toc' summary='Contents'>
            <tr><td>
            <div id='toctitle'><h2>Contents</h2></div>
            <ul>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Grand'><span class='tocnumber'>4</span> <span class='toctext'>NCE tables</span></a>
                <ul>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table2.1'><span class='tocnumber'>2.1</span> <span class='toctext'>Table 2.1: Contributions</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table2.2'><span class='tocnumber'>2.2</span> <span class='toctext'>Table 2.2: Contributions by Projects</span></a></li>
                <!--<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?summary=table3#Table3'><span class='tocnumber'>4.2</span> <span class='toctext'>Table 3: Number of network Research Personnel paid with NCE funds or other funds, by sectors</span></a></li-->
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table4'><span class='tocnumber'>4.1</span> <span class='toctext'>Table 4: Number of Graduate Students Working on Network Research</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table4.2a'><span class='tocnumber'>4.2a</span> <span class='toctext'>Table 4.2a: HQP Breakdown by University</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table4.2b'><span class='tocnumber'>4.2b</span> <span class='toctext'>Table 4.2b: HQP Breakdown by Project</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table4.3'><span class='tocnumber'>4.3</span> <span class='toctext'>Table 4.3: NI Breakdown by University</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table5'><span class='tocnumber'>4.4</span> <span class='toctext'>Table 5: Post Network employment of graduate students</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table6'><span class='tocnumber'>4.5</span> <span class='toctext'>Table 6: Dissemination of Network Research Results and Collaborations</span></a></li>
                <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table7'><span class='tocnumber'>4.6</span> <span class='toctext'>Table 7: Publications list</span></a></li>";
                if(isExtensionEnabled('Reporting')){
                    $this->html .= "<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$this->startYear}-{$this->endYear}&year=tabs_{$this->startYear}-{$this->endYear}_{$label}&summary=grand#Table8'><span class='tocnumber'>4.7</span> <span class='toctext'>Table 8: Project Budget Requests</span></a></li>";
                }
                $this->html .= "</ul>
            </li>
            </ul>
            </td></tr>
         </table>";
    }

    function showContributionsTable() {
        $html =<<<EOF
        <script type="text/javascript">
        $(document).ready(function(){
            $('#contributionsTable').dataTable({
                //'aLengthMenu': [[-1], ['All']],
                'iDisplayLength': 100,
                'bFilter': true,
                'aaSorting': [[0,'asc']],
            });
            $('span.contribution_descr').qtip({ style: { name: 'cream', tip: true } });
        });
        </script>
        <a id='Table4.0'></a>
        <table id='contributionsTable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
        <thead>
        <tr>
            <th width="27%">Name</th>
            <th width="15%">Partners</th>
            <th width="15%">Related Members</th>
            <th width="15%">Related Projects</th>
            <th width="10%">Updated</th>
            <th width="6%" align='right'>Cash</th>
            <th width="6%" align='right'>In-Kind</th>
            <th width="6%" align='right'>Total</th>
        </tr>
        </thead>
        <tbody>
EOF;
        
        $dialog_js =<<<EOF
            <script type="text/javascript">
EOF;
        $contributions = Contribution::getContributionsDuring(null, $this->startYear, $this->endYear);
        $totalCash = 0;
        $totalKind = 0;
        $totalTotal = 0;
        foreach ($contributions as $contr) {
            $con_id = $contr->getId();
            $name_plain = $contr->getName();
            $url = $contr->getUrl();
            $name = "<a href='{$url}'>{$name_plain}</a>";
            $total = $contr->getTotal();
            $cash = $contr->getCash();
            $kind = $contr->getKind();
            $people = $contr->getPeople();
            $projects = $contr->getProjects();
            $partners = $contr->getPartners();

            $partners_array = array();
            $details = "";
            foreach($partners as $p){
                $org = $p->getOrganization();
                if(!empty($org)){
                    $partners_array[] = $org;
                }
                
                $tmp_type = $contr->getTypeFor($p);
                $hrType = $contr->getHumanReadableTypeFor($p);
                $hrSubType = $contr->getHumanReadableSubTypeFor($p);

                if(!$contr->getUnknownFor($p)){
                    $tmp_cash = "\$".number_format($contr->getCashFor($p), 2);
                    $tmp_kind = "\$".number_format($contr->getKindFor($p), 2);
                    $details .= "<h4>{$org}</h4><table>";
                    $details .="<tr><td align='right'><b>Type:</b></td><td>{$hrType}</td></tr>";

                    if($tmp_type == "inki" || $tmp_type == "caki"){
                        $details .="<tr><td align='right'><b>Sub-Type:</b></td><td>{$hrSubType}</td></tr>";
                    }
                    if($tmp_type == "inki"){
                        $details .="<tr><td align='right'><b>In-Kind:</b></td><td>{$tmp_kind}</td></tr>";
                    }
                    else if($tmp_type == "cash"){
                        $details .="<tr><td align='right'><b>Cash:</b></td><td>{$tmp_cash}</td></tr>";
                    }
                    else if($tmp_type == "caki"){
                        $details .="<tr><td align='right'><b>In-Kind:</b></td><td>{$tmp_kind}</td></tr>";
                        $details .="<tr><td align='right'><b>Cash:</b></td><td>{$tmp_cash}</td></tr>";
                    }
                    else{
                        $details .="<tr><td align='right'><b>Estimated Value:</b></td><td>{$tmp_cash}</td></tr>";
                    }
                    $details .= "</table>";
                }
            }
            if(empty($details)){
                $details .= "<h4>Other</h4>";
            }
            $tmp_total = number_format($total, 2);
            $details .= "<h4>Total: \$<span id='contributionTotal'>{$tmp_total}</span></h4>";
            $partner_names = implode(', ', $partners_array);

            $people_names = array();
            foreach($people as $p){
                if($p instanceof Person){
                    $p_url = $p->getUrl();
                    $p_name = $p->getNameForForms();

                    $people_names[] = "<a href='{$p_url}'>{$p_name}</a>";
                }
            }
            $people_names = implode(', ', $people_names);

            $project_names = array();
            foreach ($projects as $p) {
                $p_url = $p->getUrl();
                $p_name = $p->getName();

                $project_names[] = "<a href='{$p_url}'>{$p_name}</a>";
            }
            $date = substr($contr->getDate(), 0, 10);
            $project_names = implode(', ', $project_names);
            if(!empty($total) && (!empty($people_names) || !empty($project_names))){
                $totalTotal += $total;
                $totalCash += $cash;
                $totalKind += $kind;
            
                $total = number_format($total, 2);
                $cash = number_format($cash, 2);
                $kind = number_format($kind, 2);
                $descr = $contr->getDescription();

                $html .=<<<EOF
                    <tr>
                        <td><span class="contribution_descr" title="{$descr}">{$name}</span></td>
                        <td>{$partner_names}</td>
                        <td>{$people_names}</td>
                        <td>{$project_names}</td>
                        
                        <td>{$date}</td>
                        <td align='right'><a href='#' onclick='$( "#contr_details-{$con_id}" ).dialog( "open" ); return false;'>\${$cash}</a></td>
                        <td align='right'><a href='#' onclick='$( "#contr_details-{$con_id}" ).dialog( "open" ); return false;'>\${$kind}</a></td>
                        <td align='right'><a href='#' onclick='$( "#contr_details-{$con_id}" ).dialog( "open" ); return false;'>\${$total}</a>
                        <div id="contr_details-{$con_id}" title="{$name_plain}">
                        {$details}
                        </div>
                        </td>
                    </tr>
EOF;
                $dialog_js .= "$( '#contr_details-{$con_id}' ).dialog({autoOpen: false});";
            }
        }

        $html .= "</tbody>
        <tfoot>
            <tr>
                <th colspan='5'></th>
                <th>$".number_format($totalCash, 2)."</th>
                <th>$".number_format($totalKind, 2)."</th>
                <th>$".number_format($totalTotal, 2)."</th>
            </tr>
        </tfoot></table>";
        $dialog_js .=<<<EOF
            </script>
EOF;
        $this->html .= $html .  $dialog_js ;   
    }
    
    function showProjectRequests(){
        ini_set("memory_limit","256M");
        $html = "";
        $allProjects = Project::getAllProjectsEver();
        $table = "<table cellpadding='3' frame='box' rules='all'><tr><th>&nbsp;</th>";
        for($y=$this->startYear+1;$y<=$this->endYear-1;$y++){
            $table .= "<th colspan='3'>$y</th>";
        }
        $table .= "<th colspan='3'>".($this->startYear+1)." - ".($this->endYear-1)."</th></tr>";
        $table .= "<tr><th>Project</th>";
        for($y=$this->startYear+1;$y<=$this->endYear-1;$y++){
            $table .= "<th>PNI</th>";
            $table .= "<th>CNI</th>";
            $table .= "<th>Total</th>";
        }
        $table .= "<th>PNI</th>";
        $table .= "<th>CNI</th>";
        $table .= "<th>Total</th>";
        $table .= "</tr>";
        $overallPNITotals = array();
        $overallCNITotals = array();
        $overallTotals = array();
        foreach($allProjects as $project){
            $pniTotals = array();
            $cniTotals = array();
            for($y=$this->startYear;$y<=$this->endYear-2;$y++){
                $pniTotals[$y] = array();
                $cniTotals[$y] = array();
                $people = array();
                foreach($project->getAllPeopleDuring(PNI, $y."-04-01", ($y+1)."-03-31") as $person){
                    if(!isset($people[$person->getId()])){
                        $budget = $person->getRequestedBudget($y);
                        if($budget != null){
                            $b = $budget->copy()->rasterize()->select(V_PROJ, array($project->getName()))->limit(22, 1);
                            if($b->nCols() > 0 && $b->nRows() > 0){
                                $pniTotals[$y][] = $b;
                                $people[$person->getId()] = true;
                            }
                        }
                    }
                }
                foreach($project->getAllPeopleDuring(CNI, $y."-04-01", ($y+1)."-03-31") as $person){
                    if(!isset($people[$person->getId()])){
                        $budget = $person->getRequestedBudget($y);
                        if($budget != null){
                            $b = $budget->copy()->rasterize()->select(V_PROJ, array($project->getName()))->limit(22, 1);
                            if($b->nCols() > 0 && $b->nRows() > 0){
                                $cniTotals[$y][] = $b;
                                $people[$person->getId()] = true;
                            }
                        }
                    }
                }
            }
            $pniSums = array();
            $cniSums = array();
            foreach($pniTotals as $year => $budgets){
                $joined = Budget::join_tables($budgets);
                $pniSums[] = $joined->sum()->xls[0][0]->getValue();
            }
            foreach($cniTotals as $year => $budgets){
                $joined = Budget::join_tables($budgets);
                $cniSums[] = $joined->sum()->xls[0][0]->getValue();
            }
            $pniTotalTotal = 0;
            $cniTotalTotal = 0;
            $totalTotal = 0;
            $table .= "<tr><td><b>{$project->getName()}</b></td>";
            for($y=$this->startYear;$y<=$this->endYear-2;$y++){
                $pniTotal = $pniSums[$y-$this->startYear];
                $cniTotal = $cniSums[$y-$this->startYear];
                $total = $pniSums[$y-$this->startYear] + $cniSums[$y-$this->startYear];
                $table .= "<td align='right'>\$".number_format(intval($pniTotal), 2)."</td>
                           <td align='right'>\$".number_format(intval($cniTotal), 2)."</td>
                           <td align='right'>\$".number_format(intval($total), 2)."</td>";
                $pniTotalTotal += $pniTotal;
                $cniTotalTotal += $cniTotal;
                $totalTotal += $total;
                
                @$overallPNITotals[$y] += $pniTotal;
                @$overallCNITotals[$y] += $cniTotal;
                @$overallTotals[$y] += $total;
            }
            $table .= "<td align='right'>\$".number_format($pniTotalTotal, 2)."</td>
                       <td align='right'>\$".number_format($cniTotalTotal, 2)."</td>
                       <td align='right'>\$".number_format($totalTotal, 2)."</td></tr>";
        }
        $pniTotalTotal = 0;
        $cniTotalTotal = 0;
        $totalTotal = 0;
        $table .= "<tr><td><b>Total</b></td>";
        for($y=$this->startYear;$y<=$this->endYear-2;$y++){
            $pniTotal = $overallPNITotals[$y];
            $cniTotal = $overallCNITotals[$y];
            $total = $overallPNITotals[$y] + $overallCNITotals[$y];
            $table .= "<td align='right'>\$".number_format(intval($pniTotal), 2)."</td>
                       <td align='right'>\$".number_format(intval($cniTotal), 2)."</td>
                       <td align='right'>\$".number_format(intval($total), 2)."</td>";
            $pniTotalTotal += $pniTotal;
            $cniTotalTotal += $cniTotal;
            $totalTotal += $total;
        }
        $table .= "<td align='right'>\$".number_format(intval($pniTotalTotal), 2)."</td>
                   <td align='right'>\$".number_format(intval($cniTotalTotal), 2)."</td>
                   <td align='right'>\$".number_format(intval($totalTotal), 2)."</td></tr>";
        $table .= "</table>";
        
        $this-> html .= <<<EOF
            <a id='Table8'></a><h3>Table 8: Project Budget Requests</h3>
            $table
EOF;
    }
}    
    
?>

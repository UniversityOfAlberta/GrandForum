<?php

class NSERCVariableTab extends AbstractTab {

    var $from = "";
    var $to = "";
    var $label = "";
    var $year = "";

    function NSERCVariableTab($label, $from, $to, $year){
        global $wgOut;
        
        $this->label = $label;
        $this->from = $from;
        $this->to = $to;
        $this->year = $year;

        parent::AbstractTab($label);
        $wgOut->setPageTitle("Evaluation Tables: NCE");
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

        if(@$_GET['year'] == "tabs_{$this->year}_".$label){
            switch (@$_GET['summary']) {
                case 'grand':
                    $wgOut->addScript($foldscript);
                    $this->html .= "<a id='Grand'></a><h2>NCE tables</h2>";
                    $this->html .= "<a id='Table1'></a><h3>Table 1: Organizations participating and contributing to the network and its projects</h3>";
                    self::showContributions();
                    $this->html .= "<a id='Table1.1'></a><h3>Table 1.1: Contributions</h3>";
                    self::showContributionsTable();
                    $this->html .= "<a id='Table1.2'></a><h3>Table 1.2: Contributions by Project</h3>";
                    self::showContributionsByProjectTable();
                    self::showGrandTables();
                    self::showDisseminations();
                    self::showArtDisseminations();
                    self::showActDisseminations();
                    self::showPublicationList();
                    break;
            }
        }
        
        return $this->html;
    }

    function showContentsTable(){
        global $wgServer, $wgScriptPath;
        $label = $this->label;
        $lastYear = $this->year - 1;
        $url = "$wgServer$wgScriptPath/index.php/Special:NCETable?tab={$lastYear}-{$this->year}&year=tabs_{$this->year}_{$label}&summary=grand";
        $this->html .=<<<EOF
            <table class='toc' summary='Contents'>
            <tr><td>
            <div id='toctitle'><h2>Contents</h2></div>
            <ul>
            <li class='toclevel-1'><a href='{$url}#Grand'><span class='tocnumber'></span> <span class='toctext'>NCE tables</span></a>
                <ul>
                <li class='toclevel-2'>
                    <a href='{$url}#Table1'>
                        <span class='tocnumber'>Table 1: </span>
                        <span class='toctext'>Organizations participating and contributing to the network and its projects</span>
                    </a>
                    <ul>
                        <li class='toclevel-3'>
                            <a href='{$url}#Table1.1'>
                                <span class='tocnumber'>Table 1.1: </span>
                                <span class='toctext'>Contributions</span>
                            </a>
                        </li>
                        <li class='toclevel-3'>
                            <a href='{$url}#Table1.2'>
                                <span class='tocnumber'>Table 1.2: </span>
                                <span class='toctext'>Contributions by Project</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class='toclevel-2'>
                    <a href='{$url}#Table2'>
                        <span class='tocnumber'>Table 2: </span>
                        <span class='toctext'>Number of network Research Personnel providing time to network research projects with NCE funds or other funds</span>
                    </a>
                </li>
                <li class='toclevel-2'>
                    <a href='{$url}#Table3'>
                        <span class='tocnumber'>Table 3: </span>
                        <span class='toctext'>Number of HQP Involved in the Network (including KM activities) and Post-Network Employment</span>
                    </a>
                    <ul>
                        <li class='toclevel-3'>
                            <a href='{$url}#Table3.1'>
                                <span class='tocnumber'>Table 3.1: </span>
                                <span class='toctext'>Post Network employment of graduate students</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class='toclevel-2'>
                    <a href='{$url}#Table4'>
                        <span class='tocnumber'>Table 4: </span>
                        <span class='toctext'>Dissemination of Network Research Results and Collaborations</span>
                    </a>
                </li>
                <li class='toclevel-2'>
                    <a href='{$url}#Table5'>
                        <span class='tocnumber'>Table 5: </span>
                        <span class='toctext'>Publications list</span>
                    </a>
                </li>
                </ul>
            </li>
            </ul>
            </td></tr>
         </table>
EOF;

    }
    
    function showContributions() {
        $contributions = Contribution::getContributionsDuring(null, $this->from, $this->to);
        $partners = array();
        foreach ($contributions as $contr) {
            $people = $contr->getPeople();
            $projects = $contr->getProjects();
            if(count($people) > 0){
                foreach($contr->getPartners() as $partner){
                    $partners[$partner->getOrganization()][] = array('partner' => $partner,
                                                                     'contribution' => $contr);
                }
            }
        }
        
        $html = "<table id='table1' class='wikitable' cellpadding='2' frame='box' rules='all' width='100%'>
                    <thead>
                    <tr>
                        <th>Participating organizations</th>
                        <th>Sector</th>
                        <th>Country</th>
                        <th>Province</th>
                        <th>City</th>
                        <th>Network Agreement Signatories</th>
                        <th>Total Cash Contributions</th>
                        <th>Total In-Kind Contributions</th>
                        <th>a) Equipment, software</th>
                        <th>b) Materials</th>
                        <th>c) Logistical support of field work</th>
                        <th>d) Provision of services</th>
                        <th>e) Use of company facilities</th>
                        <th>f) Salaries of scientific staff</th>
                        <th>g) Salaries of managerial and administrative staff</th>
                        <th>h) Project-related travel</th>
                        <th>i) Other (Justify in Column R)</th>
                        <th>Justification for 'Other' Category</th>
                    </tr>
                    </thead>
                    <tbody>";

        $nSignatory = 0;
        $totalCash = 0;
        $totalInkind = 0;
        foreach($partners as $org => $partner){
            $sector = "";
            $country = "";
            $prov = "";
            $city = "";
            $signatory = "";
            $cash = 0;
            $inkind = 0;
            $a = 0;
            $b = 0;
            $c = 0;
            $d = 0;
            $e = 0;
            $f = 0;
            $g = 0;
            $h = 0;
            $i = 0;
            $other = array();
            foreach($partner as $part){
                $sector = ($sector == "") ? $part['partner']->getIndustry() : $sector;
                $country = ($country == "") ? $part['partner']->getCountry() : $country;
                $prov = ($prov == "") ? $part['partner']->getProv() : $prov;
                $city = ($city == "") ? $part['partner']->getCity() : $city;
                $signatory = ($signatory == "") ? $part['partner']->getSignatory() : $signatory;
                if($signatory == "Yes"){
                    $nSignatory++;
                }
                $cash += $part['contribution']->getCashFor($part['partner']);
                $inki = $part['contribution']->getKindFor($part['partner']);
                $inkind += $inki;
                
                if($inki > 0){
                    switch($part['contribution']->getHumanReadableSubTypeFor($part['partner'])){
                        case "Equipment, Software":
                            $a += $inki;
                            break;
                        case "Materials":
                            $b += $inki;
                            break;
                        case "Logistical support of field work":
                            $c += $inki;
                            break;
                        case "Provision of Services":
                            $d += $inki;
                            break;
                        case "Use of Company Facilites":
                            $e += $inki;
                            break;
                        case "Salaries of Scientific Staff":
                            $f += $inki;
                            break;
                        case "Salaries of Managerial and Administrative Staff":
                            $g += $inki;
                            break;
                        case "Project-related Travel":
                            $h += $inki;
                            break;
                        default:
                            $other[] = $part['contribution']->getHumanReadableSubTypeFor($part['partner']);
                            $i += $inki;
                            break;
                    }
                }
            }
            $totalInkind += $inkind;
            $totalCash += $cash;
            $html .= "<tr>
                <td>{$org}</td>
                <td>{$sector}</td>
                <td>{$country}</td>
                <td>{$prov}</td>
                <td>{$city}</td>
                <td>{$signatory}</td>
                <td align='right'>$".number_format($cash, 2)."</td>
                <td align='right'>$".number_format($inkind, 2)."</td>
                <td align='right'>$".number_format($a, 2)."</td>
                <td align='right'>$".number_format($b, 2)."</td>
                <td align='right'>$".number_format($c, 2)."</td>
                <td align='right'>$".number_format($d, 2)."</td>
                <td align='right'>$".number_format($e, 2)."</td>
                <td align='right'>$".number_format($f, 2)."</td>
                <td align='right'>$".number_format($g, 2)."</td>
                <td align='right'>$".number_format($h, 2)."</td>
                <td align='right'>$".number_format($i, 2)."</td>
                <td>".implode("; ", $other)."</td>
            </tr>";
        }
        $html .= "</tbody>
                  <tfoot>
                    <tr>
                        <td><b>Total:</b></td>
                        <td colspan='4'></td>
                        <td align='right'>{$nSignatory}</td>
                        <td align='right'>$".number_format($totalCash, 2)."</td>
                        <td align='right'>$".number_format($totalInkind, 2)."</td>
                        <td colspan='10'>
                    </tr>
                  </tfoot>
                </table>";
                
        $html .= "<script type='text/javascript'>
            $(document).ready(function(){
                $('#table1').dataTable({
                    'aLengthMenu': [[100,-1], [100,'All']],
                    'iDisplayLength': 100,
                    'bFilter': true,
                    'aaSorting': [[0,'asc']],
                    'dom': 'Blfrtip',
                    'buttons': [
                        'excel'
                    ]
                });
            });
        </script>"; 
               
        $this->html .= $html;
    }

    function showContributionsTable() {
        $html =<<<EOF
        <script type="text/javascript">
        $(document).ready(function(){
            $('#contributionsTable').dataTable({
                'aLengthMenu': [[100,-1], [100,'All']],
                'iDisplayLength': 100,
                'bFilter': true,
                'aaSorting': [[0,'asc']],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
            $('span.contribution_descr').qtip({ style: { name: 'cream', tip: true } });
        });
        </script>
        <table id='contributionsTable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
        <thead>
        <tr>
            <th>Name</th>
            <th style='width:200px;'>Description</th>
            <th>Partners</th>
            <th>Types</th>
            <th>Related Members</th>
            <th>Related Projects</th>
            <th>Start</th>
            <th>End</th>
            <th>Updated</th>
            <th align='right'>Cash</th>
            <th align='right'>In-Kind</th>
            <th align='right'>Total</th>
        </tr>
        </thead>
        <tbody>
EOF;
        
        $dialog_js =<<<EOF
            <script type="text/javascript">
EOF;
        $contributions = Contribution::getContributionsDuring(null, $this->from, $this->to);
        $totalCash = 0;
        $totalKind = 0;
        $totalTotal = 0;
        foreach ($contributions as $contr) {
            $con_id = $contr->getId();
            $name_plain = $contr->getName();
            $url = $contr->getUrl();
            $name = "<a href='{$url}'>{$name_plain}</a>";
            $description = nl2br($contr->getDescription());
            $total = $contr->getTotal();
            $cash = $contr->getCash();
            $kind = $contr->getKind();
            $people = $contr->getPeople();
            $projects = $contr->getProjects();
            $partners = $contr->getPartners();

            $partners_array = array();
            $subType_array = array();
            $details = "";
            foreach($partners as $p){
                $org = $p->getOrganization();
                if(!empty($org)){
                    $partners_array[] = $org;
                }
                
                $tmp_type = $contr->getTypeFor($p);
                $hrType = $contr->getHumanReadableTypeFor($p);
                $hrSubType = $contr->getHumanReadableSubTypeFor($p);
                
                if($hrSubType != "None"){
                    $subType_array[] = "{$hrType} ({$hrSubType})";
                }
                else{
                    $subType_array[] = "{$hrType}";
                }

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
            $subType_names = implode(', ', $subType_array);

            $project_names = array();
            foreach ($projects as $p) {
                $p_url = $p->getUrl();
                $p_name = $p->getName();

                $project_names[] = "<a href='{$p_url}'>{$p_name}</a>";
            }
            $date = substr($contr->getDate(), 0, 10);
            $start = substr($contr->getStartDate(), 0, 10);
            $end = substr($contr->getEndDate(), 0, 10);
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
                        <td><div style='max-height:60px;overflow-y:auto;'>{$description}</div></td>
                        <td>{$partner_names}</td>
                        <td>{$subType_names}</td>
                        <td>{$people_names}</td>
                        <td>{$project_names}</td>
                        <td align='center'>{$start}</td>
                        <td align='center'>{$end}</td>
                        <td align='center'>{$date}</td>
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
                <th colspan='9'></th>
                <th>$".number_format($totalCash, 2)."</th>
                <th>$".number_format($totalKind, 2)."</th>
                <th>$".number_format($totalTotal, 2)."</th>
            </tr>
        </tfoot></table>";
        $dialog_js .=<<<EOF
            </script>
EOF;
        $this->html .= $html .  $dialog_js;   
    }
    
    function showContributionsByProjectTable(){
        $projects = Project::getAllProjectsEver();
        $projects[] = Project::newFromId(-1);
        $this-> html .= "<table class='wikitable' cellpadding='2' frame='box' rules='all' width='100%'>
                            <thead>
                                <th>Project Name</th>
                                <th>Contribution</th>
                                <th style='width:200px;'>Description</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Updated</th>
                                <th>Partner</th>
                                <th>Type</th>
                                <th>Cash</th>
                                <th>In-Kind</th>
                                <th>Sub-Total</th>
                                <th>Cash Total</th>
                                <th>In-Kind Total</th>
                                <th>Total</th>
                            </thead>
                            <tbody>";
        foreach($projects as $project){
            $contributions = $project->getContributionsDuring($this->from, $this->to);
            foreach($contributions as $contribution){
                $partners = $contribution->getPartners();
                $nRows = max(1, count($partners));
                $start = substr($contribution->getStartDate(), 0, 10);
                $end = substr($contribution->getEndDate(), 0, 10);
                $date = substr($contribution->getDate(), 0, 10);
                $this->html .= "<tr>
                                    <td rowspan='$nRows'>{$project->getName()}</td>
                                    <td rowspan='$nRows'><a href='{$contribution->getUrl()}' target='_blank'>{$contribution->getName()}</td>
                                    <td rowspan='$nRows'><div style='max-height:60px;overflow-y:auto;'>".nl2br($contribution->getDescription())."</div></td>
                                    <td rowspan='$nRows' align='center'>$start</td>
                                    <td rowspan='$nRows' align='center'>$end</th>
                                    <td rowspan='$nRows' align='center'>$date</td>";
                if(count($partners) > 0){
                    foreach($partners as $i => $partner){
                        $this->html .= "<td>{$partner->organization}</td>
                                        <td>{$contribution->getHumanReadableSubTypeFor($partner)}</td>
                                        <td align='right'>$".number_format($contribution->getCashFor($partner), 2)."</td>
                                        <td align='right'>$".number_format($contribution->getKindFor($partner), 2)."</td>
                                        <td align='right'>$".number_format($contribution->getTotalFor($partner), 2)."</td>";
                        if($i == 0){
                            $this->html .= "<td rowspan='$nRows' align='right'>$".number_format($contribution->getCash(), 2)."</td>";
                            $this->html .= "<td rowspan='$nRows' align='right'>$".number_format($contribution->getKind(), 2)."</td>";
                            $this->html .= "<td rowspan='$nRows' align='right'>$".number_format($contribution->getTotal(), 2)."</td>";
                        }
                        $this->html .= "</tr>";
                        if($i < $nRows-1){
                            $this->html .= "<tr>";
                        }
                    }
                }
                else{
                    $this->html .= "<td></td>
                                    <td></td>
                                    <td align='right'>$".number_format($contribution->getCash(), 2)."</td>
                                    <td align='right'>$".number_format($contribution->getKind(), 2)."</td>
                                    <td align='right'>$".number_format($contribution->getTotal(), 2)."</td>
                                    <td align='right'>$".number_format($contribution->getCash(), 2)."</td>
                                    <td align='right'>$".number_format($contribution->getKind(), 2)."</td>
                                    <td align='right'>$".number_format($contribution->getTotal(), 2)."</td></tr>";
                }
            }
        }
        $this->html .= "</tbody></table>";
    }

    function showGrandTables() {
        global $wgOut, $_pdata, $_projects;
        

        $canadian = array();
        $foreign = array();
        $unknown = array();

        $unique = array();
        $movedons = Person::getAllMovedOnDuring($this->from, $this->to);
        foreach($movedons as $m){
            if(in_array($m->getName(), $unique)) {
                continue;
            }
            $unique[] = $m->getName();

            $m_nation = $m->getNationality();
            if($m_nation == "Canadian" || $m_nation == "Landed Immigrant"){
                $canadian[] = $m;
            }
            else if($m_nation == "Foreign" || $m_nation == "Visa Holder" || $m_nation == "American"){
                $foreign[] = $m;
            }
            else{
                $unknown[] = $m;
            }
        }    

        $this->html .= "<a id='Table2'></a><h3>Table 2:  Number of network Research Personnel providing time to network research projects with NCE funds or other funds
</h3>" .self::getUniStats();
        $this->html .= "<a id='Table3'></a><h3>Table 3: Number of HQP Involved in the Network (including KM activities) and Post-Network Employment</h3>" . self::getHQPStats();
        $this->html .= "<a id='Table3.1'></a><h3>Table 3.1: Post Network employment of graduate students</h3>" . self::getHQPEmployment($movedons, "all");
        $this->html .= "<h4>Canadian</h4>". self::getHQPEmployment($canadian, "canada");
        $this->html .= "<h4>Foreign</h4>". self::getHQPEmployment($foreign, "foreign");
        $this->html .= "<h4>Unknown</h4>". self::getHQPEmployment($unknown, "unknown");
    }

    function getHQPStats(){
        $hqps = Person::getAllPeopleDuring(HQP, $this->from, $this->to);

        //Setup the table structure
        $positions = array( "Undergraduate Student"=>"Ugrad",
                            "Graduate Student - Master's"=>"Masters",
                            "Graduate Student - Doctoral"=>"PhD",
                            "Post-Doctoral Fellow"=>"Post-Doctoral Fellows",
                            "Technician"=> "Technicians / Research Associates",
                            "Research Associate" => "Technicians / Research Associates",
                            "Professional End User" => "Professional End Users",
                            "Other"=>"Other");

        $nations = array("Canadian"=>array(array(),array()), "Foreign"=>array(array(),array()), "Unknown"=>array(array(),array()));

        $hqp_table = array();
        foreach($positions as $key=>$val){
            $hqp_table[$val] = array("Male"=>$nations, "Female"=>$nations, "Unknown"=>$nations);
        }

        //Fill the table
        foreach($hqps as $hqp){
            $pos = $hqp->getUniversityDuring($this->from, $this->to);
            if(!isset($positions[$pos['position']])){
                $pos = $hqp->getUniversity();
            }
            $pos = (isset($positions[$pos['position']])) ? $pos['position'] : "Other";
            $gender = $hqp->getGender();
            $gender = (empty($gender))? "Unknown" : $gender;
            $nation = $hqp->getNationality();
            $nation = (empty($nation))? "Unknown" : $nation;
            if($nation == "Landed Immigrant"){
                $nation = "Canadian";
            }
            else if($nation == "Visa Holder"){
                $nation = "Foreign";
            }

            $hqp_table[$positions[$pos]][$gender][$nation][0][] = $hqp;
            $movedOns = $hqp->getAllMovedOn();
            foreach($movedOns as $movedOn){
                if($movedOn['reason'] == 'graduated'){
                    $hqp_table[$positions[$pos]][$gender][$nation][1][] = $movedOn['thesis'];
                }
            }
        }

        $details_div_id = "hqp_details";
        $html =<<<EOF
         <table id='table_hqp1' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
            <tr>
                <td width='25%'></td>
                <th width='25%'>HQP</th>
                <th width='25%'>N. of HQP</th>
                <th width='25%'>N. of degrees completed</th>
            </tr>
EOF;
        
        $total = array(array(), array());
        foreach ($hqp_table as $pos=>$data){
            $nRows = 2;
            foreach($data as $gender => $nations){
                $nRows++;
                foreach($nations as $label => $counts){
                    $nRows++;
                }
            }
            $html .=<<<EOF
                <tr>
                <th rowspan='$nRows'>{$pos}</th>
EOF;
            $inner_tbl = "";
            $total_gen = array(array(), array());
            foreach($data as $gender => $nations){
                $inner_tbl .= "<tr><td>{$gender}</td><td></td><td></td></tr>";
                $total_nat = array(array(), array());
                foreach($nations as $label => $counts){
                    
                    $lnk_id = "lnk_" .$pos. "_" .$gender. "_". $label;
                    $div_id = "div_" .$pos. "_" .$gender. "_". $label;
                    
                    $lnk_id = str_replace("/", "_", str_replace(" ", "_", $lnk_id));
                    $div_id = str_replace("/", "_", str_replace(" ", "_", $div_id));
                                
                    $inner_tbl .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{$label}</td>";
                    $num_students = count($counts[0]);
                    $student_details = Dashboard::hqpDetails($counts[0]);
                    if($num_students > 0){
                        $inner_tbl .=<<<EOF
                            <td>
                            <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                            $num_students
                            </a>
                            <div style="display: none;" id="$div_id" class="cell_details_div">
                                <p><span class="label">{$pos} / {$gender} / {$label}:</span> 
                                <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                                <ul>$student_details</ul>
                            </div>
                            </td>
EOF;
                    }
                    else{
                        $inner_tbl .= "<td>0</td>";
                    }

                    //Theses
                    $lnk_id = "lnk_thes_" .$pos. "_" .$gender. "_". $label;
                    $div_id = "div_thes_" .$pos. "_" .$gender. "_". $label;
                    
                    $lnk_id = str_replace("/", "_", str_replace(" ", "_", $lnk_id));
                    $div_id = str_replace("/", "_", str_replace(" ", "_", $div_id));
                    
                    $num_theses = @count($counts[1]);
                    $theses_details = @Dashboard::paperDetails($counts[1]);
                    if($num_theses > 0){
                        $inner_tbl .=<<<EOF
                            <td>
                            <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                            $num_theses
                            </a>
                            <div style="display: none;" id="$div_id" class="cell_details_div">
                                <p><span class="label">Theses: {$pos} / {$gender} / {$label}:</span> 
                                <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                                <ul>$theses_details</ul>
                            </div>
                            </td>
EOF;
                    }
                    else{
                        $inner_tbl .= "<td>0</td>";
                    }

                    //$inner_tbl .= "<td>{$counts[1]}</td></tr>";
                    //$inner_tbl .= "<tr><td>{$label}</td><td>{$counts[0]}</td><td>{$counts[1]}</td></tr>";
                    $total_nat[0] = @array_merge($total_nat[0], $counts[0]); // += $num_students;
                    $total_nat[1] = @array_merge($total_nat[1], $counts[1]); //+= $counts[1];
                }


                $lnk_id = "lnk_" .$pos. "_" .$gender. "_total";
                $div_id = "div_" .$pos. "_" .$gender. "_total";
                
                $lnk_id = str_replace("/", "_", str_replace(" ", "_", $lnk_id));
                $div_id = str_replace("/", "_", str_replace(" ", "_", $div_id));
                
                $num_total_nat = count($total_nat[0]);
                $total_nat_details = Dashboard::hqpDetails($total_nat[0]);

                $lnk_id = "lnk_thes_" .$pos. "_" .$gender. "_total";
                $div_id = "div_thes_" .$pos. "_" .$gender. "_total";
                $num_total_nat_thes = count($total_nat[1]);
                $total_nat_thes_details = Dashboard::paperDetails($total_nat[1]);

                $total_gen[0] = @array_merge($total_gen[0], $total_nat[0]); // += $total_nat[0];
                $total_gen[1] = @array_merge($total_gen[1], $total_nat[1]); //+= $total_nat[1];
            }   
            
            $inner_tbl .= "<tr style='font-weight:bold;'><td>Total:</td>"; //<td>{$total_gen[0]}</td><td>{$total_gen[1]}</td></tr>";
            $lnk_id = "lnk_" .$pos. "_total";
            $div_id = "div_" .$pos. "_total";
            
            $lnk_id = str_replace("/", "_", str_replace(" ", "_", $lnk_id));
            $div_id = str_replace("/", "_", str_replace(" ", "_", $div_id));
            
            $num_total_gen = count($total_gen[0]);
            $total_gen_details = Dashboard::hqpDetails($total_gen[0]);
            if($num_total_gen > 0){
                $inner_tbl .=<<<EOF
                    <td>
                    <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num_total_gen
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$pos} / Total:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$total_gen_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $inner_tbl .= "<td>0</td>";
            }

            $lnk_id = "lnk_thes_" .$pos. "_total";
            $div_id = "div_thes_" .$pos. "_total";
            
            $lnk_id = str_replace("/", "_", str_replace(" ", "_", $lnk_id));
            $div_id = str_replace("/", "_", str_replace(" ", "_", $div_id));
            
            $num_total_gen_thes = count($total_gen[1]);
            $total_gen_thes_details = Dashboard::paperDetails($total_gen[1]);
            if($num_total_gen_thes > 0){
                $inner_tbl .=<<<EOF
                    <td>
                    <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num_total_gen_thes
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">Theses: {$pos} / Total:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$total_gen_thes_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $inner_tbl .= "<td>0</td>";
            }
            //$inner_tbl .= "<td>{$total_gen[1]}</td></tr>";         
            $html .= $inner_tbl."</tr>";
            $html .= "<tr><th colspan='4'></th></tr>";
            
            $total[0] = @array_merge($total[0], $total_gen[0]);// += $total_gen[0];
            $total[1] = @array_merge($total[1], $total_gen[1]);//+= $total_gen[1];
        }
        $html .= "<tr style='font-weight:bold;'><td></td><td>Total:</td><td>"; //Total Thesis: {$total[1]}</td></tr>";
        $lnk_id = "lnk_total";
        $div_id = "div_total";
        $num_total = count($total[0]);
        $total_details = Dashboard::hqpDetails($total[0]);
        if($num_total > 0){
            $html .=<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $num_total
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">All Students:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    <ul>$total_details</ul>
                </div>
EOF;
        }
        else{
            $html .= "0";
        }
        $html .= "</td><td>";
        $lnk_id = "lnk_thes_total";
        $div_id = "div_thes_total";
        $num_total_thes = count($total[1]);
        $total_thes_details = Dashboard::paperDetails($total[1]);
        if($num_total_thes > 0){
            $html .=<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $num_total_thes
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">All Theses:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    <ul>$total_thes_details</ul>
                </div>
EOF;
        }
        else{
            $html .= "0";
        }

        //$html .= "; Total Thesis: {$total[1]}</td></tr>";
        $html .= "</td></tr>";
        $html .= "</table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        
        return $html;
    }

    function getUniStats(){
        $hqps = Person::getAllPeopleDuring(HQP, $this->from, $this->to);
        $nis  = Person::getAllPeopleDuring(NI,  $this->from, $this->to);

        //Setup the table structure
        $universities = array();
        $empty = array("Researchers" => array(), 
                       "Research Associates" => array(),
                       "Post-Doctoral Fellows" => array(),
                       "Technical Staff" => array(),
                       "Graduates" => array(),
                       "Undergrad" => array(), 
                       "Professional End Users" => array(), 
                       "Other" => array());
        $unknown = $empty;

        $positions = array("Undergraduate Student" => "Undergrad",
                           "Graduate Student - Master's" => "Graduates",
                           "Graduate Student - Doctoral" => "Graduates",
                           "Post-Doctoral Fellow" => "Post-Doctoral Fellows",
                           "Technician" => "Technical Staff",
                           "Research Associate" => "Research Associates",
                           "Professional End User" => "Professional End Users",
                           "Other" => "Other");

        //Fill the table for HQP
        foreach ($hqps as $hqp){
            $uniobj = $hqp->getUniversityDuring($this->from, $this->to);
            if(!isset($uniobj['university'])){
                $uniobj = $hqp->getUniversity();
            }
            $uni = (isset($uniobj['university']))? $uniobj['university'] : "Unknown";
            if($uni == ""){
                continue;
            }
            if($uni != "Unknown" && !array_key_exists($uni, $universities)){
                $universities[$uni] = $empty;
            }

            $pos = (isset($uniobj['position']))? $uniobj['position'] : "Other";
            $pos = (isset($positions[$pos]))? $positions[$pos] : "Other";

            if($uni == "Unknown"){
                $unknown[$pos][] = $hqp;
            }
            else{
                $universities[$uni][$pos][] = $hqp;
            }
        }
        
        // Fill the table for NI
        foreach($nis as $ni){
            $uniobj = $ni->getUniversityDuring($this->from, $this->to);
            if(!isset($uniobj['university'])){
                $uniobj = $ni->getUniversity();
            }
            $uni = (isset($uniobj['university']))? $uniobj['university'] : "Unknown";
            if($uni == ""){
                continue;
            }
            if($uni != "Unknown" && !array_key_exists($uni, $universities)){
                $universities[$uni] = $empty;
            }

            if($uni == "Unknown"){
                $unknown["Researchers"][] = $ni;
            }
            else{
                $universities[$uni]["Researchers"][] = $ni;
            }
        }

        ksort($universities);
        $universities["Unknown"] = $unknown;

        $details_div_id = "hqp_uni_details";
        $html =<<<EOF
         <table id='table_hqp2' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
         <tr>
            <td colspan='2'></td>
            <th colspan='8'>Research Personnel (HQP)</th>
         </tr>
         <tr>
             <th rowspan='2'>Organization</th>
             <th rowspan='2'>Researchers</th>
             <th rowspan='2'>Research Associates</th>
             <th rowspan='2'>Postdoctoral fellows</th>
             <th rowspan='2'>Technical staff</th>
             <th colspan='2'>Students</th>
             <th rowspan='2'>Professional End Users</th>
             <th rowspan='2'>Other</th>
             <th rowspan='2'>Total (HQP)</th>
         </tr>
         <tr>
            <th>Graduates</th>
            <th>Undergrad</th>
         </tr>
EOF;

        $totals = $empty;
        
        foreach ($universities as $uni=>$data){
            $html .=<<<EOF
                <tr>
                <td align="left">{$uni}</td>
EOF;
            $total_uni = array();
            foreach($data as $posi => $hqpa){
                $uni_id = str_replace("/", "_", str_replace(" ", "_", $uni));
                $pos_id = str_replace("/", "_", str_replace(" ", "_", $posi));
                $lnk_id = "lnk_" . $uni_id . "_" . $pos_id;
                $div_id = "div_" . $uni_id . "_" . $pos_id;
                if($posi != "Researchers"){
                    $total_uni = array_merge($total_uni, $hqpa);
                }
                $num_students = count($hqpa);   
                $student_details = Dashboard::hqpDetails($hqpa);
                if($num_students > 0){
                    $html .=<<<EOF
                        <td align='right'>
                        <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $num_students
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">{$uni} / {$posi}:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                            <ul>$student_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $html .= "<td align='right'>0</td>";
                }
                $totals[$posi][] = $num_students;
            }

            //Row Total
            $lnk_id = "lnk_" . $uni_id . "_total";
            $div_id = "div_" . $uni_id . "_total";

            $num_students = count($total_uni);   
            $student_details = Dashboard::hqpDetails($total_uni);
            if($num_students > 0){
                $html .=<<<EOF
                    <td align='right'>
                    <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num_students
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$uni} / {$posi}:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$student_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $html .= "<td align='right'>0</td>";
            }
            $totals["HQP"][] = $num_students;
            $html .= "</tr>";
        }
        $html .= "<tfoot>
                    <tr><td><b>Total:</b></td>";
        foreach($totals as $total){
            $sum = array_sum($total);
            $html .= "<td align='right'>{$sum}</td>";
        }
        $html .= "</tr>
        </tfoot>";
        $html .= "</table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        
        return $html;
    }

    function getHQPEmployment($people, $type){
        $movedons = $people;
        
        $positions = array( "Undergraduate Student"=>"Ugrad",
                            "Graduate Student - Master's"=>"Masters",
                            "Graduate Student - Doctoral"=>"PhD",
                            "Post-Doctoral Fellow"=>"Post-Doctoral Fellows",
                            "Technician"=> "Technicians / Research Associates",
                            "Research Associate" => "Technicians / Research Associates",
                            "Professional End User" => "Professional End Users",
                            "Other"=>"Other");
        
        $intkeys = array(
            'Canadian' => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()),
            'Foreign'  => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()),
            'Other'    => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()));

        $hqp_table = array();
        foreach($positions as $key=>$val){
            $hqp_table[$val] = $intkeys;
        }

        $totals_arr = $intkeys;

        $details_div_id = "movedon_details_".$type;

        foreach ($movedons as $m){

            $movedon_data = $m->getMovedOn();

            //Theses data
            if(isset($movedon_data['works']) && $movedon_data['works']==""){

                $thesis = $m->getThesis();
                if(!is_null($thesis) && $thesis->getType() == "PhD Thesis"){
                    $m_pos = "PhD";
                }
                else if(!is_null($thesis) && $thesis->getType() == "Masters Thesis"){
                    $m_pos = "Masters";
                }
                else{
                    continue;
                }
                
                $hqp_table[$m_pos]["Canadian"]['university'][] = $m;
            }
            //Movedon data
            else{
                $m_pos = $m->getUniversityDuring($this->from, $this->to);
                if(isset($positions[$m_pos['position']])){
                    $m_pos = $m_pos['position'];
                }
                else{
                    $m_pos = "Other";
                }

               
                if($movedon_data['country'] == ""){
                    $m_nation = "Other";
                }
                else if($movedon_data['country'] == "Canada"){
                    $m_nation = "Canadian";
                }else{
                    $m_nation = "Foreign";
                }

                if(!empty($movedon_data['studies']) || $m->isActive() ){
                    $hqp_table[$positions[$m_pos]][$m_nation]['university'][] = $m;
                }
                else if(!empty($movedon_data['employer'])){
                    $hqp_table[$positions[$m_pos]][$m_nation]['industry'][] = $m;
                }
                else{
                    $hqp_table[$positions[$m_pos]][$m_nation]['unknown'][] = $m;
                }
            }
        }   

        $html =<<<EOF
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
            <tr>
            <th rowspan='2'>Position /<br />Degree completed</th>
            <th colspan='3'>Canadian</th>
            <th colspan='3'>Foreign</th>
            <th colspan='3'>Other</th>
            </tr>
            <tr>
            <th>University</th>
            <th>Industry</th>
            <th>Unknown</th>
            <th>University</th>
            <th>Industry</th>
            <th>Unknown</th>
            <th>University</th>
            <th>Industry</th>
            <th>Unknown</th>
            <th>Total</th>
            </tr>
EOF;
        $all_total = array();
        foreach($hqp_table as $pos => $data){
            $html .= "<tr><td>{$pos}</td>";
            $pos_total = array();
            foreach ($data as $nation => $area){
                foreach ($area as $name => $val){
                    $lnk_id = "lnk_" .$pos. "_" .$nation. "_". $name ."_tbl5_".$type;
                    $div_id = "div_" .$pos. "_" .$nation. "_". $name ."_tbl5_".$type;
                    
                    $num_students = count($val);
                    $student_details = Dashboard::hqpDetails($val);
                    if($num_students > 0){
                        $html .=<<<EOF
                            <td>
                            <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                            $num_students
                            </a>
                            <div style="display: none;" id="$div_id" class="cell_details_div">
                                <p><span class="label">{$pos} / {$nation} / {$name}:</span> 
                                <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                                <ul>$student_details</ul>
                            </div>
                            </td>
EOF;
                    }
                    else{
                        $html .= "<td>0</td>";
                    }

                    //$html .= "<td>{$val}</td>";
                    $pos_total = array_merge($pos_total, $val);
                    $totals_arr[$nation][$name] = array_merge($totals_arr[$nation][$name], $val);
                    //$all_total += $num_students;
                    $all_total = array_merge($all_total, $val);
                }
            }

            //Totals
            $html .= "<td style='font-weight:bold;'>";
            $lnk_id = "lnk_" .$pos. "_total_tbl5_".$type;
            $div_id = "div_" .$pos. "_total_tbl5_".$type;
            
            $num_students = count($pos_total);
            $student_details = Dashboard::hqpDetails($pos_total);
            if($num_students > 0){
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                    $num_students
                    </a>
                    <div style="display: none;" id="$div_id" class="cell_details_div">
                        <p><span class="label">{$pos} / Total:</span> 
                        <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                        <ul>$student_details</ul>
                    </div>
                    </td>
EOF;
            }
            else{
                $html .= "0</td>";
            }

            $html .= "</td></tr>";

        }
        $html .= "<tr style='font-weight:bold;'><td>Total:</td>";
        
        foreach($totals_arr as $nation => $data){
            foreach($data as $k=>$v){
                
                $lnk_id = "lnk_" .$nation. "_".$k. "_total_".$type;
                $div_id = "div_" .$nation. "_".$k. "_total_".$type;
                
                $num_students = count($v);
                $student_details = Dashboard::hqpDetails($v);
                if($num_students > 0){
                    $html .=<<<EOF
                        <td>
                        <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $num_students
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">{$nation} / $k / Total:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                            <ul>$student_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $html .= "<td>0</td>";
                }
            }
        }

        $lnk_id = "lnk_" .$nation. "_alltotal_".$type;
        $div_id = "div_" .$nation. "_alltotal_".$type;
        
        $num_students = count($all_total);
        $student_details = Dashboard::hqpDetails($all_total);
        if($num_students > 0){
            $html .=<<<EOF
                <td>
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $num_students
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">{$nation} / Total:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    <ul>$student_details</ul>
                </div>
                </td>
EOF;
        }
        else{
            $html .= "<td>0</td>";
        }

        $html .= "</tr></table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        return $html;
    }

    function showDisseminations(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'all', "grand", $this->from, $this->to);

        $dissem = array("a1_r1"=>array(), "a1_r2"=>array(), "a2_r1"=>array(), "a2_r2"=>array(), "b_r1"=>array(), "b_r2"=>array());

        foreach($publications as $pub){
            $authors = $pub->getAuthors();
            $pub_projects = array();
            $status = $pub->getStatus();
            if($status == "Rejected"){
                continue;
            }
            
            $groups = array();
            $author_ids = array();
            foreach($authors as $author){
                $author_ids[] = $author->getId();
                if($author->getId() == ""){
                    break;
                }
                //echo "sdsd".$author->isSupervisor();
                if($author->isSupervisor()){
                    if(!isset($groups[$author->getId()])){
                        $groups[$author->getId()] = array($author->getId());
                    }
                }else{
                    $supervisors = $author->getSupervisors();
                    foreach($supervisors as $sup){
                        if(isset($groups[$sup->getId()])){
                            $groups[$sup->getId()][] = $author->getId();
                        }else{
                            $groups[$sup->getId()] = array($sup->getId(), $author->getId());
                        }
                    }

                }
            }
            $key = "_r2";

            foreach($groups as $k=>$sup){
                if(in_array($k, $author_ids) && count($sup) == count($authors) ){
                    $key = "_r1";
                }
            }
            
            switch ($pub->getType()) {
                // A1: Articles in refereed publications
                case 'Journal Paper':
                case 'Magazine/Newspaper Article':
                    if($pub->getData('peer_reviewed') == "Yes"){
                        $dissem["a1".$key][] = $pub;
                    }
                    else{
                        $dissem["b".$key][] = $pub;
                    }
                    break;
                // A2: Other refereed contributions
                case 'Book':
                case 'Book Chapter':
                case 'Edited Book':
                case 'Collections Paper':
                case 'Conference Paper':
                case 'Proceedings Paper':
                    if($pub->getData('peer_reviewed') == "Yes"){
                        $dissem["a2".$key][] = $pub;
                    }
                    else{
                        $dissem["b".$key][] = $pub;
                    }
                    break;
                // B: Non-refereed contributions
                //case 'Misc':
                //case 'Poster':
                //case 'Book Review':
                case 'Review Article':
                //case 'Invited Presentation':
                //default:
                    if($pub->getData('peer_reviewed') == "No" || $pub->getData('peer_reviewed') == ""){
                            if($pub->getCategory() == "Publication" ||
                               $pub->getCategory() == "Scientific Excellence - Advancing Knowledge" ||
                               ($pub->getCategory() == "Scientific Excellence - Leadership" && $pub->getType() == "Invited Presentation")){
                            $dissem["b".$key][] = $pub;
                        }
                    }
                    break;
                // C: Specialized Publications
                case 'Bachelors Thesis':
                case 'Masters Thesis':
                case 'Masters Dissertation':
                case 'PHD Thesis':
                case 'PHD Dissertation':
                case 'Tech Report':
                case 'Abstract':
                case 'Journal Abstract':
                case 'Conference Abstract':
                case 'White Paper':
                case 'Symposium Record':
                case 'Industrial Report':
                case 'Internal Report':
                case 'Manual':
                    $dissem["c".$key][] = $pub;   
                    break;
            }
        }
        
        $n_a1_r1 = count($dissem['a1_r1']);
        $n_a1_r2 = count($dissem['a1_r2']);
        $n_a2_r1 = count($dissem['a2_r1']);
        $n_a2_r2 = count($dissem['a2_r2']);
        $n_b_r1 = count($dissem['b_r1']);
        $n_b_r2 = count($dissem['b_r2']);

        $d_a1_r1 = self::getDisseminationDetails($dissem['a1_r1']);
        $d_a1_r2 = self::getDisseminationDetails($dissem['a1_r2']);
        $d_a2_r1 = self::getDisseminationDetails($dissem['a2_r1']);
        $d_a2_r2 = self::getDisseminationDetails($dissem['a2_r2']);
        $d_b_r1 = self::getDisseminationDetails($dissem['b_r1']);
        $d_b_r2 = self::getDisseminationDetails($dissem['b_r2']);

        $html =<<<EOF
            <a id='Table4'></a><h3>Table 4: Dissemination of Network Research Results and Collaborations</h3>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr>
                <th align='left'>Articles in refereed publications</th><th>Number of publications</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>{$n_a1_r1} {$d_a1_r1}</td>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>{$n_a1_r2} {$d_a1_r2}</td>
            </tr>
            <tr>
                <th align='left' colspan='2'>Other refereed contributions</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>{$n_a2_r1}  {$d_a2_r1}</td>
            </tr>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>{$n_a2_r2}  {$d_a2_r2}</td>
            </tr>
            <tr>
                <th align='left' colspan='2'>Non-refereed contributions</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>{$n_b_r1} {$d_b_r1}</td>
            </tr>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>{$n_b_r2} {$d_b_r2}</td>
            </tr>
            </table>
EOF;

        $this->html .= $html;
    }

    function showArtDisseminations(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'Artifact', "grand", $this->from, $this->to);
        
        if(count($publications) == 0){
            return;
        }

        $types = Paper::getCategoryTypes("Artifact");

        $dissem = array();
        foreach($types as $t){
            $t = preg_replace('/ /', '_', $t);
            $dissem["{$t}_r1"] = array();
            $dissem["{$t}_r2"] = array();
        }

        foreach($publications as $pub){
            $authors = $pub->getAuthors();
            $pub_projects = array();
            $status = $pub->getStatus();
            
            $groups = array();
            $author_ids = array();
            foreach($authors as $author){
                
                $author_ids[] = $author->getId();
                if($author->getId() == ""){
                    break;
                }
                //echo "sdsd".$author->isSupervisor();
                if($author->isSupervisor()){
                    if(!isset($groups[$author->getId()])){
                        $groups[$author->getId()] = array($author->getId());
                    }
                }else{
                    $supervisors = $author->getSupervisors();
                    foreach($supervisors as $sup){
                        if(isset($groups[$sup->getId()])){
                            $groups[$sup->getId()][] = $author->getId();
                        }else{
                            $groups[$sup->getId()] = array($sup->getId(), $author->getId());
                        }
                    }

                }


            }
            
            $key = "_r2";
            
            foreach($groups as $k=>$sup){
                if(in_array($k, $author_ids) && count($sup) == count($authors) ){
                    $key = "_r1";
                }
            }
            

            $pub_type = preg_replace('/ /', '_', $pub->getType());
                
            $dissem["{$pub_type}{$key}"][] = $pub;
            
        }
      
        $html =<<<EOF
            <h4>Artifacts</h4>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr>
                <th align='left'></th><th>Number of artifacts</th>
            </tr>
EOF;

        foreach($types as $t){
            $t_key = preg_replace('/ /', '_', $t);
            $html .= "
            <tr>
                <th align='left' colspan='2'>{$t}</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>".count($dissem["{$t_key}_r1"])." ".self::getDisseminationDetails2($dissem["{$t_key}_r1"])."</td>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>".count($dissem["{$t_key}_r2"])." ".self::getDisseminationDetails2($dissem["{$t_key}_r2"])."</td>
            </tr>
            ";
        }    
        
        $html .= "</table>";

        $this->html .= $html;
    }

    function showActDisseminations(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'Activity', "grand", $this->from, $this->to);
        
        if(count($publications) == 0){
            return;
        }
        
        //echo (sizeof($publications ));
        $types = Paper::getCategoryTypes("Activity");

        $dissem = array();
        foreach($types as $t){
            $t = preg_replace('/ /', '_', $t);
            $dissem["{$t}_r1"] = array();
            $dissem["{$t}_r2"] = array();
        }

        foreach($publications as $pub){
            $authors = $pub->getAuthors();
            $pub_projects = array();
            $status = $pub->getStatus();
            
            $groups = array();
            $author_ids = array();
            foreach($authors as $author){
                
                $author_ids[] = $author->getId();
                if($author->getId() == ""){
                    break;
                }
                if($author->isSupervisor()){
                    if(!isset($groups[$author->getId()])){
                        $groups[$author->getId()] = array($author->getId());
                    }
                }else{
                    $supervisors = $author->getSupervisors();
                    foreach($supervisors as $sup){
                        if(isset($groups[$sup->getId()])){
                            $groups[$sup->getId()][] = $author->getId();
                        }else{
                            $groups[$sup->getId()] = array($sup->getId(), $author->getId());
                        }
                    }

                }


            }
            
            $key = "_r2";
            
            foreach($groups as $k=>$sup){
                if(in_array($k, $author_ids) && count($sup) == count($authors) ){
                    $key = "_r1";
                }
            }
            

            $pub_type = preg_replace('/ /', '_', $pub->getType());
                
            $dissem["{$pub_type}{$key}"][] = $pub;
            
        }
      
        $html =<<<EOF
            <h4>Activities</h4>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr>
                <th align='left'></th><th>Number of activities</th>
            </tr>
EOF;

        foreach($types as $t){
            $t_key = preg_replace('/ /', '_', $t);
            $html .= "
            <tr>
                <th align='left' colspan='2'>{$t}</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>".count($dissem["{$t_key}_r1"])." ".self::getDisseminationDetails2($dissem["{$t_key}_r1"])."</td>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>".count($dissem["{$t_key}_r2"])." ".self::getDisseminationDetails2($dissem["{$t_key}_r2"])."</td>
            </tr>
            ";
        }    
        
        $html .= "</table>";

        $this->html .= $html;
    }

    private function getDisseminationDetails($arr) {
        if (empty($arr))
            return "";

        // Grab a random identifier to name this <div>.
        $id = "dis" . mt_rand();
        $ret = "<small><a href=\"javascript:ShowOrHide('{$id}','')\">Details</a></small><div id='{$id}' style='display:none;text-align:left'><ol>";
        foreach ($arr as $publ) {
            $title = $publ->getTitle();
            $type = $publ->getType();
            $authors = $publ->getAuthors();
            $yr = substr($publ->getDate(), 0, 4);
            $pg = $publ->getData('pages');
            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = $publ->getData('publisher', '(no publisher)');

            switch ($publ->getType()) {
                case 'Book':
                case 'Book Chapter':
                case 'Collections Paper':
                case 'Proceedings Paper':
                    $vn = $publ->getData('book_title', 'no venue');
                    $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
                    break;

                case 'Journal Paper':
                case 'Magazine/Newspaper Article':
                    $vn = $publ->getData('journal_title', 'no venue');
                    $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
                    break;

                case 'Masters Thesis':
                case 'PhD Thesis':
                case 'Tech Report':
                    break;

                case 'Misc':
                case 'Poster':
                default:
                    $vn = $publ->getData('book_title', $publ->getData('eventname', 'no venue'));
                    $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}\n";
            }

            $ret .= "\n<ul>";
            foreach ($authors as $auth) {
                $name = ($auth->getId())? "<strong>". $auth->getName() ."</strong>" : $auth->getName();
                $projs = $auth->getProjects();
                $proj_names = array();
                if(!empty($projs)){
                    foreach($projs as $p){
                        $proj_names[] = $p->getName();
                    }


                }
                $ret .= "\n<li>{$name} <small>(" . implode(', ', $proj_names) . ")</small></li>";
            }
            $ret .= "\n</ul></li>";
        }

        $ret .= "\n</ol></div>";

        return $ret;
    }

    private function getDisseminationDetails2($arr) {
        if (empty($arr))
            return "";

        // Grab a random identifier to name this <div>.
        $id = "dis" . mt_rand();
        $ret = "<small><a href=\"javascript:ShowOrHide('{$id}','')\">Details</a></small><div id='{$id}' style='display:none;text-align:left'><ol>";
        foreach ($arr as $publ) {
            $title = $publ->getTitle();
            $type = $publ->getType();
            $authors = $publ->getAuthors();
            $yr = substr($publ->getDate(), 0, 4);

            $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;\n";            

            $ret .= "\n<ul>";
            foreach ($authors as $auth) {
                $name = ($auth->getId())? "<strong>". $auth->getName() ."</strong>" : $auth->getName();
                $projs = $auth->getProjects();
                $proj_names = array();
                if(!empty($projs)){
                    foreach($projs as $p){
                        $proj_names[] = $p->getName();
                    }


                }
                $ret .= "\n<li>{$name} <small>(" . implode(', ', $proj_names) . ")</small></li>";
            }
            $ret .= "\n</ul></li>";
        }

        $ret .= "\n</ol></div>";

        return $ret;
    }

    function showPublicationList(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'all', "grand", $this->from, $this->to);
        $pub_count = array("a1"=>array(), "a2"=>array(), "b"=>array(), "c"=>array());

        $alreadyDone = array();
        foreach($publications as $pub){
            if(isset($alreadyDone[$pub->getId()])){
                continue;
            }
            $alreadyDone[$pub->getId()] = true;
            $status = $pub->getStatus();
            if($status == "Rejected"){
                continue;
            }
            switch ($pub->getType()) {
                // A1: Articles in refereed publications
                case 'Journal Paper':
                case 'Magazine/Newspaper Article':
                    if($pub->getData('peer_reviewed') == "Yes"){
                        $pub_count["a1"][] = $pub;
                    }
                    else{
                        $pub_count["b"][] = $pub;
                    }
                    break;
                // A2: Other refereed contributions
                case 'Book':
                case 'Book Chapter':
                case 'Edited Book':
                case 'Collections Paper':
                case 'Conference Paper':
                case 'Proceedings Paper':
                    if($pub->getData('peer_reviewed') == "Yes"){
                        $pub_count["a2"][] = $pub;
                    }
                    else{
                        $pub_count["b"][] = $pub;
                    }
                    break;
                // B: Non-refereed contributions
                //case 'Misc':
                //case 'Poster':
                //case 'Book Review':
                case 'Review Article':
                //case 'Invited Presentation':
                //default:
                    if($pub->getData('peer_reviewed') == "No" || $pub->getData('peer_reviewed') == ""){
                        if($pub->getCategory() == "Publication" ||
                           $pub->getCategory() == "Scientific Excellence - Advancing Knowledge" ||
                           ($pub->getCategory() == "Scientific Excellence - Leadership" && $pub->getType() == "Invited Presentation")){
                            $pub_count["b"][] = $pub;
                        }
                    }
                    break;
                // C: Specialized Publications
                case 'Bachelors Thesis':
                case 'Masters Thesis':
                case 'Masters Dissertation':
                case 'PHD Thesis':
                case 'PHD Dissertation':
                case 'Tech Report':
                case 'Abstract':
                case 'Journal Abstract':
                case 'Conference Abstract':
                case 'White Paper':
                case 'Symposium Record':
                case 'Industrial Report':
                case 'Internal Report':
                case 'Manual':
                    $pub_count["c"][] = $pub;   
                    break;
            }
        }
        
        $a1 = count($pub_count['a1']);
        $a2 = count($pub_count['a2']);
        $c  = count($pub_count['c']);
        $b  = count($pub_count['b']);
        $a12 = $a1 + $a2;
        $total = $a12 + $b + $c;    

        $details_div_id = "pub_list_details_div";

        //A1 details
        $list_sub = '';
        $list_pub = '';
        $a1_details = "";
        foreach($pub_count['a1'] as $pub){
            $issub = $pub->getData('submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$pub->getCitation(false, false, false)}\n";
        }
        if (strlen($list_pub) > 0)
            $a1_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $a1_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_a1";
        $div_id = "div_a1";
        if($a1 > 0){
            $a1 =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a1
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">A) Refereed Contributions / 1. Articles in refereed publications:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$a1_details}
                </div>
                </td>
EOF;
        }
        else{
            $a1 = "0";
        }

        //A2 details
        $list_sub = '';
        $list_pub = '';
        $a2_details = "";
        foreach($pub_count['a2'] as $pub){
            $issub = $pub->getData('submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$pub->getCitation(false, false, false)}\n";
        }
        if (strlen($list_pub) > 0)
            $a2_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $a2_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_a2";
        $div_id = "div_a2";
        if($a2 > 0){
            $a2 =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a2
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">A) Refereed Contributions / 2. Other refereed contributions:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$a2_details}
                </div>
                </td>
EOF;
        }
        else{
            $a2 = "0";
        }

        //A1+2 details
        $list_sub = '';
        $list_pub = '';
        $a12_details =  "<br /><span class='label'>A) Refereed Contributions / 1. Articles in refereed publications:</span><br />".
                        $a1_details .
                        "<br /><span class='label'>A) Refereed Contributions / 2. Other refereed contributions:</span><br />".
                        $a2_details;
        

        $lnk_id = "lnk_a12";
        $div_id = "div_a12";
        if($a12 > 0){
            $a12 =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a12
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">Total Refereed:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$a12_details}
                </div>
                </td>
EOF;
        }
        else{
            $a12 = "0";
        }

        //B details
        $list_sub = '';
        $list_pub = '';
        $b_details = "";
        foreach($pub_count['b'] as $pub){
            $issub = $pub->getData('submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$pub->getCitation(false, false, false)}\n";
        }
        if (strlen($list_pub) > 0)
            $b_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $b_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_b";
        $div_id = "div_b";
        if($b > 0){
            $b =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $b
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">B) Non-refereed contributions:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$b_details}
                </div>
                </td>
EOF;
        }
        else{
            $b = "0";
        }   

        //C details
        $list_sub = '';
        $list_pub = '';
        $c_details = "";
        foreach($pub_count['c'] as $pub){
            $issub = $pub->getData('submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$pub->getCitation(false, false, false)}\n";
        }
        if (strlen($list_pub) > 0)
            $c_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $c_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_c";
        $div_id = "div_c";
        if($c > 0){
            $c =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $c
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">C) Specialized publications:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$c_details}
                </div>
                </td>
EOF;
        }
        else{
            $c = "0";
        }   

        //Total details
        $list_sub = '';
        $list_pub = '';
        $total_details= $a12_details . 
                        "<br /><span class='label'>B) Non-refereed contributions:</span><br />". 
                        $b_details . 
                        "<br /><span class='label'>C) Specialized publications:</span><br />".
                        $c_details;

        $lnk_id = "lnk_total_pub";
        $div_id = "div_total_pub";
        if($total > 0){
            $total =<<<EOF
                <a id="$lnk_id" onclick="showDiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $total
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">All Publications:</span> 
                    <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                    {$total_details}
                </div>
                </td>
EOF;
        }
        else{
            $total = "0";
        }   

        $html =<<<EOF
            <a id='Table5'></a><h3>Table 5: Publications List</h3>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr><th align='left'>A) Refereed Contributions</th><th>Number of publications</th></tr>
            <tr><td>&emsp;1. Articles in refereed publications</td><td align='center'>{$a1}</td></tr>
            <tr><td>&emsp;2. Other refereed contributions</td><td align='center'>{$a2}</td></tr>
            <tr><td align='right'>Total refereed</td><td align='center'>{$a12}</td></tr>
            <tr><th align='left'>B) Non-refereed contributions<td align='center'>{$b}</td></tr>
            <tr><th align='left'>C) Specialized publications</th><td align='center'>{$c}</td></tr>
            <tr><th align='left'>Total publications</th><th>{$total}</th></tr>
            </table>
            <div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div>

EOF;
        $this->html .= $html;
    }

    static function dollar_format($val) {
        return '$&nbsp;' . number_format($val, 2);
    }
}    
    
?>

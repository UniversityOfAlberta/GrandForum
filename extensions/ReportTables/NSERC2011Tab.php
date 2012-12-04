<?php

class NSERC2011Tab extends AbstractTab {

    function NSERC2011Tab(){
        global $wgOut;
        parent::AbstractTab("2011");
        $wgOut->setPageTitle("Evaluation Tables: NCE");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $wgOut;

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
function showdiv(div_id, details_div_id){   
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

        if(ArrayUtils::get_string($_GET, 'year') == "2011"){
            $wgOut->addScript("<script type='text/javascript'>$(document).ready(function(){ $('#tabs_nserc').tabs('select', 1); });</script>");
        switch (ArrayUtils::get_string($_GET, 'summary')) {
        
        /*case 'table2':
            $wgOut->addScript($foldscript);
            $this->html .= "<a id='Grand'></a><h2>GRAND tables</h2>";
            self::show_grand_table2();
            break;
            
        case 'table3':
            $this->html .= "<a id='Grand'></a><h2>GRAND tables</h2>";
            self::showHQPTable();
            break;*/

        case 'grand':
            $wgOut->addScript($foldscript);
            $this->html .= "<a id='Grand'></a><h2>GRAND tables</h2>";
            self::showGrandTables();
            self::showDisseminations();
            self::showPublicationList();
            //self::show_disseminations();
            //self::show_publication_list();
            break;
        }
        }
        //$this->showProductivity();
        
        return $this->html;
    }


    function showContentsTable(){
        global $wgServer, $wgScriptPath;
        $this->html .=<<<EOF
            <table class='toc' summary='Contents'>
            <tr><td>
            <div id='toctitle'><h2>Contents</h2></div>
            <ul>
            <li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=grand#Grand'><span class='tocnumber'>4</span> <span class='toctext'>GRAND tables</span></a>
                <ul>
                    <!--li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=table2#Table2'><span class='tocnumber'>4.1</span> <span class='toctext'>Table 2: Direct Contributions From Non-NCE Sources</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=table3#Table3'><span class='tocnumber'>4.2</span> <span class='toctext'>Table 3: Number of network Research Personnel paid with NCE funds or other funds, by sectors</span></a></li-->
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=grand#Table4'><span class='tocnumber'>4.3</span> <span class='toctext'>Table 4: Number of Graduate Students Working on Network Research</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=grand#Table5'><span class='tocnumber'>4.4</span> <span class='toctext'>Table 5: Post Network employment of graduate students</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=grand#Table6'><span class='tocnumber'>4.5</span> <span class='toctext'>Table 6: Dissemination of Network Research Results and Collaborations</span></a></li>
                    <li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC&year=2011&summary=grand#Table7'><span class='tocnumber'>4.6</span> <span class='toctext'>Table 7: Publications list</span></a></li>
                </ul>
            </li>
            </ul>
            </td></tr>
         </table>
EOF;

    }

    function show_grand_table2() {
        global $wgOut;

        $pnis = Person::getAllPeople(PNI);
        $cnis = Person::getAllPeople(CNI);

        // Partners array is organized as (type, source, researcher, contributions):
        // [cash]
        //  [Source]
        //      [Researcher]
        //          [0]: {cash = xx, inki = yy, desc = zz, amount = xx+yy}
        //          ...
        // [inki]
        //  ...
        $contributions = array();
        $subtotals = array();
        $totalcash = 0;
        $totalinki = 0;
        foreach (array($pnis, $cnis) as $groups) {
            foreach ($groups as $person) {
                $rp = new ResearcherProductivity($person);

                // Contributions.
                $tmp = $rp->get_metric(RPST_CONTRIBUTIONS);
                $longname = $person->getNameForForms();

                // Loop over contributions to sort them according to
                // source of contribution.
                $conts = ArrayUtils::get_array($tmp, 'table2');
                foreach ($conts as &$cont) {
                    // Check whether this contribution is valid for inclusion.
                    if (ArrayUtils::get_string($cont, 'Internal') == 'yes')
                        continue;

                    // Select from the proper array: cash or inkind.
                    $src = ArrayUtils::get_string($cont, 'Source', 'invalid');
                    $type = ArrayUtils::get_string($cont, 'Type', 'none');
                    $cash = ResearcherProductivity::extract_number(ArrayUtils::get_field($cont, 'Cash', 0));
                    $inki = ResearcherProductivity::extract_number(ArrayUtils::get_field($cont, 'Inkind', 0));

                    // Skip empty contributions.
                    if ($cash == 0 && $inki == 0)
                        continue;

                    // Sub-array for this type of contribution.
                    $parr = ArrayUtils::get_array($contributions, $type);

                    // Include an entry for this researcher/contribution combo.
                    $oarr = ArrayUtils::get_array($parr, $src);
                    $iarr = ArrayUtils::get_array($oarr, $longname);
                    $iarr[] = array('amount' => $cash + $inki,
                            'cash' => $cash,
                            'inki' => $inki,
                            'desc' => ArrayUtils::get_string($cont, 'Desc'));
                    // Store.
                    $oarr[$longname] = $iarr;
                    $parr[$src] = $oarr;
                    $contributions[$type] = $parr;

                    // Some pre-computation.
                    $totalcash += $cash;
                    $totalinki += $inki;
                    $arr = ArrayUtils::get_array($subtotals, $type);
                    $arr['cash'] = ArrayUtils::get_field($arr, 'cash', 0) + $cash;
                    $arr['inki'] = ArrayUtils::get_field($arr, 'inki', 0) + $inki;
                    $subtotals[$type] = $arr;
                }
            }
        }

        // The data is collected: now go over both arrays for cash/inkind contributions,
        // and produce a list of them with the relevant details.
        $cont_names = array('none' => 'Invalid type',
                'cash' => 'Cash',
                'caki' => 'Cash and In-kind',
                'inki' => 'In-kind',
                'equi' => 'Equipment donations',
                'soft' => 'Software',
                'conf' => 'Conference organization',
                'work' => 'Workshop hosting',
                'talk' => 'Invited talks',
                'othe' => 'Other');

        $chunk = '';
        $pone = true;
        foreach ($contributions as $ctype => &$ctarr) {
            $chunk .= "<h2>Contribution Type: {$cont_names[$ctype]}</h2>\n" .
                "<p><table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>" .
                "<tr><th>Partner</th><th>Researcher</th><th>Contributions</th><th>Description</th></tr>";

            foreach ($ctarr as $partner => &$res_conts) {
                foreach ($res_conts as $name => &$conts) {
                    foreach ($conts as &$cont) {
                        // Print each contribution in a row.  All contributions have an amount.
                        $amt = $cont['amount'];
                        // Some contributions will either have cash, inkind, or both.  If both,
                        // gather info as to present the breakdown.
                        $cash = ArrayUtils::get_string($cont, 'cash');
                        $inki = ArrayUtils::get_string($cont, 'inki');

                        $chunk .= "\n<tr><td style='width:20em'>{$partner}</td>" .
                            "<td style='width:12em'>{$name}</td>" .
                            "<td align='right' style='width:12em'>" .
                            self::dollar_format($cont['amount']) .
                            "<br /><small><span style='color:#0a0'>Cash:&nbsp;" .
                            self::dollar_format($cash) .
                            "</span><br /><span style='color:#00a'>In&#8209;kind:&nbsp;" .
                            self::dollar_format($inki) .
                            "</small></td><td>" .
                            self::create_details_abbrev($cont['desc']) .
                            "</td>";
                    }
                }
            }

            // Include a row with totals for this type of contribution, and close the table.
            $arr = ArrayUtils::get_array($subtotals, $ctype);
            $cash = ArrayUtils::get_field($arr, 'cash', 0);
            $inki = ArrayUtils::get_field($arr, 'inki', 0);

            $chunk .= "\n<tr><th></th><th>Total</th><th align='right'>" .
                self::dollar_format($cash + $inki);
            if ($cash > 0 && $inki > 0)
                $chunk .= "<br /><small><span style='color:#0a0'>Cash:&nbsp;" .
                    self::dollar_format($cash) .
                    "</span><br /><span style='color:#00a'>In&#8209;kind:&nbsp;" .
                    self::dollar_format($inki) .
                    "</small>";

            $chunk .= "</th></tr></table></p>\n";
        }

        // TODO: sort by key.

        // Subtotals table, summarizing all contributions listed previously.
        $chunk .= "<h2>Contribution Summary/Breakdown by Type</h2>" .
            "<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all'>\n" .
            "<tr><th>Contribution Type</th><th>Cash amount</th><th>In-kind amount</th><th>Total</th></tr>\n";
        $ctotal = 0;
        $itotal = 0;
        foreach ($subtotals as $ctype => &$carr) {
            $cash = ArrayUtils::get_field($carr, 'cash', 0);
            $inki = ArrayUtils::get_field($carr, 'inki', 0);
            $sum = $cash + $inki;
            $ctotal += $cash;
            $itotal += $inki;

            $chunk .= "<tr><td>{$cont_names[$ctype]}</td>" .
                "<td style='color:#0a0' align='right'>" . self::dollar_format($cash) . "</td>" .
                "<td style='color:#00a' align='right'>" . self::dollar_format($inki) . "</td>" .
                "<td align='right'>" . self::dollar_format($sum) . "</td></tr>\n";
        }
        $gtotal = $ctotal + $itotal;
        $chunk .= "<tr><th>Grand total</th>" .
            "<th style='color:#0a0' align='right'>" . self::dollar_format($ctotal) . "</th>" .
            "<th style='color:#00a' align='right'>" . self::dollar_format($itotal) . "</th>" .
            "<th align='right'>" . self::dollar_format($gtotal) . "</th></tr></table>\n";

        $this->html .= $chunk;
    }

    function showHQPTable(){
        global $wgOut;
        $wgOut->addScript("<script type='text/javascript'>
            function showHideTable(id){
                $('#' + id).toggle();
            }
        </script>");
        $people = Person::getAllPeople(HQP);
        $chunk = "
<a id='Table3'></a><h3>Table 3: Number of network Research Personnel paid with NCE funds or other funds, by sectors</h3>
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
    <tr><th width='15%'>Name</th><th width='10%'>Type</th><th width='30%'>University</th><th width='25%'>Title</th><th width='20%'>Projects</th></tr>
";
        foreach($people as $hqp){
            if($hqp->getName() == "Sandra.Tpze" ||
               $hqp->getName() == "Beth" ||
               $hqp->getName() == "Scott.Newsom" ||
               $hqp->getName() == "Dinara" ||
               $hqp->getName() == "Andrea" ||
               $hqp->getName() == "Bardia"){
                continue;
            }
            $projects = $hqp->getProjects();
            $university = $hqp->getUniversity();
            $chunk .= "<tr>
                           <td valign='top'>{$hqp->getName()}</td>
                           <td valign='top'>HQP</td>
                           <td valign='top'>{$university['university']}</td>
                           <td valign='top'>{$university['position']}</td>
                           <td style='padding:0;'>";
            $table = "";
            $max = 0;
            foreach($projects as $project){
                $month = $hqp->getHQPMonth($project);
                $table .= "<tr>
                               <td valign='top'>{$project->getName()}</td>
                               <td>$month</td>
                           </tr>";
                if($month != "Unknown" && $month > $max){
                    $max = $month;
                }
            }
            if($max < 10){
                $max = "0".$max;
            }
            $table = "<span hidden='hidden'>$max</span><table class='wikitable' cellspacing='1' cellpadding='2' rules='all' width='100%'>
                                <tr><th width='100px'>Project</th><th>Months Active</th></tr>".$table."</table>";
                           
            $chunk .= "$table</td></tr>";
        }
        $chunk .= "</table>";
        $this->html .= "$chunk";
    }

    function showGrandTables() {
        global $wgOut, $_pdata, $_projects;
        /*
        self::preload_project_data();

        foreach ($_projects as $project) {
            $_pdata[$project->getId()] = new ProjectProductivity($project);
        }

        $levels = array('total', 'ugrad', 'master', 'phd', 'postdoc', 'tech', 'other');
        $blank = "<td style='color: #000000' align='right'>0";

        // Collect HQP data.
        $table4 = array();
        $grouped = array();
        foreach ($_pdata as $pobj) {
            // HQP stuff.
            $tmp = $pobj->get_metric(PJST_HQPS);
            $table4 = array_merge_recursive($table4, ArrayUtils::get_array($tmp, 'table4'));
            $grouped = array_merge_recursive($grouped, ArrayUtils::get_array($tmp, 'moved'));
        }

        $chunk = "
<a id='Table4'></a><h3>Table 4: Number of Graduate Students Working on Network Research</h3>
<table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
";
        $details = "<p align='right'><a href=\"javascript:ShowOrHide(table4details','')\">Show/Hide Details for Theses</a><div id='table4details' style='display:none'>\n<ul>";
        foreach (array('master' => 'Masters', 'phd' => 'PhDs') as $lvl => $lvlname) {
            $chunk .= "<tr><th>{$lvlname}<td><table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>\n";
            $outer = ArrayUtils::get_array($table4, $lvl);
            $nrows = 0;
            $theses = 0;
            $students = 0;
            // Prepare a header to be patched in the intermediate loop.
            $buf = "<tr><th>Graduate Students<th>Number of students<th>Number of theses completed\n";
            foreach (array('female', 'male', 'unknown') as $gen) {
                $chunk .= "{$buf}<tr><td>" . ucfirst($gen) . "<td><td>";
                // The header has been patched, clear buf.
                $buf = "";
                $mid = ArrayUtils::get_array($outer, $gen);
                foreach (array('canadian', 'foreign', 'unknown') as $cit) {
                    $inner = ArrayUtils::get_array($mid, $cit);
                    $st = count($inner);
                    $th = count(array_keys($inner, 'yes'));
                    $students += $st;
                    $theses += $th;
                    $chunk .= "\n<tr><td>&emsp;&emsp;" . ucfirst($cit) . "<td>{$st}<td>{$th}";
                }
            }

            // Totals and close table.
            $chunk .= "<tr><th>Total<td>{$students}<td>{$theses}</table>\n";
        }
        // Done.
        $chunk .= "</table>\n";

        $chunk .= "
<a id='Table5'></a><h3>Table 5: Post Network employment of graduate students</h3>
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
<tr><th rowspan='2'>Position /<br />Degree completed
<th colspan='5'>Canadian
<th colspan='4'>Foreign
<th colspan='2'>
<tr>
<th>University
<th>Industry
<th>Government
<th>Other
<th>Unemployed
<th>University
<th>Industry
<th>Government
<th>Other
<th>Unknown
<th>Total
";

        // Iterate.
        $intkeys = array('canadian' => array('university', 'industry', 'government', 'other', 'unemployed'),
                'foreign' => array('university', 'industry', 'government', 'other'));
        $ctotals = array();
        $gtotal = 0;
        foreach (array('master' => 'Masters', 'phd' => 'PhDs', 'postdoc' => 'Post-Doctoral Fellow') as $lvl => $lvlname) {
            $oarr = ArrayUtils::get_array($grouped, $lvl);
            $cind = 0;
            $rtotal = 0;
            $chunk .= "<tr><td>{$lvlname}";
            foreach (array('canadian', 'foreign') as $cit) {
                $iarr = ArrayUtils::get_array($oarr, $cit);
                foreach ($intkeys[$cit] as $type) {
                    $det_arr = ArrayUtils::get_array($iarr, $type);
                    $v = count($det_arr);
                    $rtotal += $v;
                    $ctotals[$cind] = ArrayUtils::get_field($ctotals, $cind, 0) + $v;
                    $cind++;
                    $chunk .= "<td>{$v}";
                    if ($v > 0)
                        $chunk .= self::create_table5_details($det_arr);
                    $chunk .= "</td>";
                }
            }

            // The 'unknown' column.
            $det_arr = ArrayUtils::get_array($oarr, 'unknown');
            $v = count($det_arr);
            $rtotal += $v;
            $ctotals[$cind] = ArrayUtils::get_field($ctotals, $cind, 0) + $v;

            // The column itself, and the total for this row.
            $chunk .= "<td>{$v}";
            if ($v > 0)
                $chunk .= self::create_table5_details($det_arr);
            $chunk .= "</td>";
            $chunk .= "<td>{$rtotal}</td>\n";
            $gtotal += $rtotal;
        }

        // Per-column totals.
        $chunk .= '<tr><td>Total';
        for ($i = 0; $i < count($ctotals); $i++) {
            $chunk .= "<td>{$ctotals[$i]}";
        }
        $chunk .= "<td>{$gtotal}</table>\n";

//      $chunk .= "<pre>grouped:\n" . print_r($grouped, true) . "table4:\n" . print_r($table4, true) . "</pre>\n";

        $this->html .= $chunk;
        */
        $this->html .= self::getHQPStats();
        $this->html .= self::getHQPEmployment();
    }

    function getHQPStats(){

        $hqps = Person::getAllPeopleDuring(HQP, "2010-01-01 00:00:00", "2010-12-31 23:59:59");

        //Setup the table structure
        $positions = array( "Undergraduate"=>"Ugrad",
                            "Masters Student"=>"Masters",
                            "PhD Student"=>"PhD",
                            "PostDoc"=>"PostDoc",
                            "Technician"=>"Tech",
                            "Other"=>"Other",
                            "Unknown"=>"Unknown");

        $nations = array("Canadian"=>array(array(),0), "Foreign"=>array(array(),0), "Landed Immigrant"=>array(array(),0), "Visa Holder"=>array(array(),0), "Unknown"=>array(array(),0));

        $hqp_table = array();
        foreach($positions as $key=>$val){
            $hqp_table[$val] = array("Female"=>$nations, "Male"=>$nations, "Unknown"=>$nations);
        }

        //Fill the table
        foreach ($hqps as $hqp){
            $pos = $hqp->getUniversity();
            $pos = (isset($positions[$pos['position']]))? $pos['position'] : "Other";
            $gender = $hqp->getGender();
            $gender = (empty($gender))? "Unknown" : $gender;
            $nation = $hqp->getNationality();
            $nation = (empty($nation))? "Unknown" : $nation;
            $thesis = $hqp->getThesis();
            $thesis = (!is_null($thesis))? 1 : 0;

            $hqp_table[$positions[$pos]][$gender][$nation][0][] = $hqp;
            $hqp_table[$positions[$pos]][$gender][$nation][1] += $thesis;
        }

        $details_div_id = "hqp_details";
        $html =<<<EOF
         <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
EOF;
        
        $total = array(array(), 0);
        foreach ($hqp_table as $pos=>$data){
            $html .=<<<EOF
                <tr>
                <th>{$pos}</th>
                <td>
EOF;
            $inner_tbl =<<<EOF
                <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
                <tr>
                    <td>Graduate Students</td>
                    <td>Number of Students</td>
                    <td>Number of Theses Completed</td>
                 </tr>
EOF;
            $total_gen = array(array(), 0);
            foreach($data as $gender => $nations){

                $inner_tbl .= "<tr><td align='center'>{$gender}</td><td></td><td></td></tr>";
                $total_nat = array(array(), 0);
                foreach($nations as $label => $counts){
                    
                    $lnk_id = "lnk_" .$pos. "_" .$gender. "_". $label;
                    $div_id = "div_" .$pos. "_" .$gender. "_". $label;
            
                    $inner_tbl .= "<tr><td>{$label}</td>";
                    $num_students = count($counts[0]);
                    $student_details = Dashboard::hqpDetails($counts[0]);
                    if($num_students > 0){
                        $inner_tbl .=<<<EOF
                            <td>
                            <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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

                    $inner_tbl .= "<td>{$counts[1]}</td></tr>";
                    //$inner_tbl .= "<tr><td>{$label}</td><td>{$counts[0]}</td><td>{$counts[1]}</td></tr>";
                    $total_nat[0] = array_merge($total_nat[0], $counts[0]); // += $num_students;
                    $total_nat[1] += $counts[1];
                }

                $inner_tbl .= "<tr style='font-weight:bold;'><td>Total:</td>"; //<td>{$total_nat[0]}</td><td>{$total_nat[1]}</td></tr>";
                $lnk_id = "lnk_" .$pos. "_" .$gender. "_total";
                $div_id = "div_" .$pos. "_" .$gender. "_total";
                $num_total_nat = count($total_nat[0]);
                $total_nat_details = Dashboard::hqpDetails($total_nat[0]);
                if($num_total_nat > 0){
                    $inner_tbl .=<<<EOF
                        <td>
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                        $num_total_nat
                        </a>
                        <div style="display: none;" id="$div_id" class="cell_details_div">
                            <p><span class="label">{$pos} / {$gender} / Total:</span> 
                            <button class="hide_div" onclick="$('#$details_div_id').hide();return false;">x</button></p> 
                            <ul>$total_nat_details</ul>
                        </div>
                        </td>
EOF;
                }
                else{
                    $inner_tbl .= "<td>0</td>";
                }
                $inner_tbl .= "<td>{$total_nat[1]}</td></tr>";

                $total_gen[0] = array_merge($total_gen[0], $total_nat[0]); // += $total_nat[0];
                $total_gen[1] += $total_nat[1];
            }   
            
            $inner_tbl .= "<tr style='font-weight:bold;'><td>Total $pos:</td>"; //<td>{$total_gen[0]}</td><td>{$total_gen[1]}</td></tr>";
            $lnk_id = "lnk_" .$pos. "_total";
            $div_id = "div_" .$pos. "_total";
            $num_total_gen = count($total_gen[0]);
            $total_gen_details = Dashboard::hqpDetails($total_gen[0]);
            if($num_total_gen > 0){
                $inner_tbl .=<<<EOF
                    <td>
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
            $inner_tbl .= "<td>{$total_gen[1]}</td></tr>";

            $inner_tbl .= "</table>";           
            $html .= $inner_tbl."</td></tr>";
            
            $total[0] = array_merge($total[0], $total_gen[0]);// += $total_gen[0];
            $total[1] += $total_gen[1];
        }
        $html .= "<tr style='font-weight:bold;'><td></td><td>Total Students: "; //Total Thesis: {$total[1]}</td></tr>";
        $lnk_id = "lnk_total";
        $div_id = "div_total";
        $num_total = count($total[0]);
        $total_details = Dashboard::hqpDetails($total[0]);
        if($num_total > 0){
            $html .=<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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

        $html .= "; Total Thesis: {$total[1]}</td></tr>";
        $html .= "</table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        
        return $html;

    }

    function getHQPEmployment(){
        $movedons = Person::getAllMovedOnDuring("2010-01-01 00:00:00", "2010-12-31 23:59:59");

        
        $positions = array( "Undergraduate"=>"Ugrad",
                            "Masters Student"=>"Masters",
                            "PhD Student"=>"PhD",
                            "PostDoc"=>"PostDoc",
                            "Technician"=>"Tech",
                            "Other"=>"Other",
                            "Unknown"=>"Unknown");
        
        $intkeys = array(
            'Canadian' => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()),
            'Foreign'  => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()),
            'Other'    => array('university'=>array(), 'industry'=>array(), 'unknown'=>array()));

        $hqp_table = array();
        foreach($positions as $key=>$val){
            $hqp_table[$val] = $intkeys;
        }

        $totals_arr = $intkeys;

        $details_div_id = "movedon_details";

        foreach ($movedons as $m){

            $movedon_data = $m->getMovedOn();

            $m_pos = $m->getUniversity();
            if(isset($positions[$m_pos['position']])){
                $m_pos = $m_pos['position'];
            }
            else{
                $m_pos = "Other";
            }

            $m_nation = $m->getNationality();
            if($m_nation != "Canadian" && $m_nation != "Foreign"){
                $m_nation = "Other";
            }

            if(!empty($movedon_data['studies'])){
                $hqp_table[$positions[$m_pos]][$m_nation]['university'][] = $m;
            }
            else if(!empty($movedon_data['employer'])){
                $hqp_table[$positions[$m_pos]][$m_nation]['industry'][] = $m;
            }
            else{
                $hqp_table[$positions[$m_pos]][$m_nation]['unknown'][] = $m;
            }
    
        }   

        $html =<<<EOF
            <a id='Table5'></a><h3>Table 5: Post Network employment of graduate students</h3>
            <table cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
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
        $all_total = 0;
        foreach($hqp_table as $pos => $data){
            $html .= "<tr><td>{$pos}</td>";
            $pos_total = array();
            foreach ($data as $nation => $area){
                foreach ($area as $name => $val){
                    $lnk_id = "lnk_" .$pos. "_" .$nation. "_". $name;
                    $div_id = "div_" .$pos. "_" .$nation. "_". $name;
                    
                    $num_students = count($val);
                    $student_details = Dashboard::hqpDetails($val);
                    if($num_students > 0){
                        $html .=<<<EOF
                            <td>
                            <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
                    $all_total += $num_students;
                }
            }

            //Totals
            $html .= "<td style='font-weight:bold;'>";
            $lnk_id = "lnk_" .$pos. "_total";
            $div_id = "div_" .$pos. "_total";
            
            $num_students = count($pos_total);
            $student_details = Dashboard::hqpDetails($pos_total);
            if($num_students > 0){
                $html .=<<<EOF
                    <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
                
                $lnk_id = "lnk_" .$nation. "_".$k. "_total";
                $div_id = "div_" .$nation. "_".$k. "_total";
                
                $num_students = count($v);
                $student_details = Dashboard::hqpDetails($v);
                if($num_students > 0){
                    $html .=<<<EOF
                        <td>
                        <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
                //$html .= "<td>{$v}</td>";
            }
        }
        $html .= "<td>{$all_total}</td></tr></table>";
        $html .= "<div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div><br />";
        return $html;
    }

    function showDisseminations(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'Publication', 'grand', "2010-01-01 00:00:00", "2010-12-31 23:59:59");

        $dissem = array("a1_r1"=>array(), "a1_r2"=>array(), "a2_r1"=>array(), "a2_r2"=>array(), "b_r1"=>array(), "b_r2"=>array());

        foreach($publications as $pub){
            $authors = $pub->getAuthors();
            $pub_projects = array();
            foreach($authors as $author){
                $projects = $author->getProjects();
                if(is_null($projects)){continue;}
                foreach($projects as $p){
                    $p_name = $p->getName();
                    if(array_key_exists($p_name, $pub_projects)){
                        $pub_projects[$p_name]++;
                    }
                    else{
                        $pub_projects[$p_name] = 1;
                    }
                }

            }

            $key = "";
            if( array_search(count($authors), $pub_projects) ){
                $key = "_r1";
            }
            else{
                $key = "_r2";
            }

            switch ($pub->getType()) {
                case 'Book':
                case 'Collections Paper':
                case 'Proceedings Paper':
                    $dissem["a2".$key][] = $pub;
                    break;

                case 'Journal Paper':
                    $dissem["a1".$key][] = $pub;
                    break;

                case 'Masters Thesis':
                case 'Tech Report':
                    break;

                case 'Misc':
                case 'Poster':
                default:
                    $dissem["b".$key][] = $pub;
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
            <a id='Table6'></a><h3>Table 6: Dissemination of Network Research Results and Collaborations</h3>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr>
                <th align='left'>Articles in referred publications</th><th>Number of publications</th>
            </tr>
            <tr>
                <td valign='top'>&emsp;All authors from one research group</td>
                <td align='center'>{$n_a1_r1} {$d_a1_r1}</td>
            <tr>
                <td valign='top'>&emsp;The authors from two or more research groups</td>
                <td align='center'>{$n_a1_r2} {$d_a1_r2}</td>
            </tr>
            <tr>
                <th align='left' colspan='2'>Other published refereed contributions</th>
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
                <th align='left' colspan='2'>Published non-refereed contributions</th>
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
            $data = $publ->getData();
            $yr = substr($publ->getDate(), 0, 4);
            $pg = ArrayUtils::get_string($data, 'pages');
            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');

            switch ($publ->getType()) {
                case 'Book':
                case 'Collections Paper':
                case 'Proceedings Paper':
                    $vn = ArrayUtils::get_string($data, 'book_title', 'no venue');
                    $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
                    break;

                case 'Journal Paper':
                    $vn = ArrayUtils::get_string($data, 'journal_title', 'no venue');
                    $ret .= "<li>{$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
                    break;

                case 'Masters Thesis':
                case 'Tech Report':
                    break;

                case 'Misc':
                case 'Poster':
                default:
                    $vn = ArrayUtils::get_string($data, 'book_title', ArrayUtils::get_string($data, 'eventname', 'no venue'));
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

    function showPublicationList(){
        global $wgOut;
        $publications = Paper::getAllPapersDuring('all', 'Publication', 'grand', "2010-01-01 00:00:00", "2010-12-31 23:59:59");
        $pub_count = array("a1"=>array(), "a2"=>array(), "b"=>array(), "c"=>array());

        foreach($publications as $pub){

            switch ($pub->getType()) {
                case 'Book':
                case 'Collections Paper':
                case 'Proceedings Paper':
                    $pub_count["a2"][] = $pub;
                    break;

                case 'Journal Paper':
                    $pub_count["a1"][] = $pub;
                    break;

                case 'Masters Thesis':
                case 'Tech Report':
                    $pub_count["c"][] = $pub;   
                    break;

                case 'Misc':
                case 'Poster':
                default:
                    $pub_count["b"][] = $pub;
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
            $data = $pub->getData();
            $type = $pub->getType();
            $title = $pub->getTitle();
            $au = array();
            foreach($pub->getAuthors() as $a){
                if($a->getId()){
                    $au[] = "<strong>". $a->getNameForForms() ."</strong>";
                }else{
                    $au[] = $a->getNameForForms();
                }
            }
            $au = implode(', ', $au);
            $yr = substr($pub->getDate(), 0, 4);
            $vn = ArrayUtils::get_string($data, 'journal_title', 'no venue');
            $pg = ArrayUtils::get_string($data, 'pages');
            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');
            $issub = ArrayUtils::get_field($data, 'submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$au}. {$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
        }
        if (strlen($list_pub) > 0)
            $a1_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $a1_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_a1";
        $div_id = "div_a1";
        if($a1 > 0){
            $a1 =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a1
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">A) Referred Contributions / 1. Articles in referred publications:</span> 
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
            $data = $pub->getData();
            $type = $pub->getType();
            $title = $pub->getTitle();
            $au = array();
            foreach($pub->getAuthors() as $a){
                if($a->getId()){
                    $au[] = "<strong>". $a->getNameForForms() ."</strong>";
                }else{
                    $au[] = $a->getNameForForms();
                }
            }
            $au = implode(', ', $au);
            $yr = substr($pub->getDate(), 0, 4);
            $vn = ArrayUtils::get_string($data, 'book_title', 'no venue');
            $pg = ArrayUtils::get_string($data, 'pages');
            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');
            $issub = ($pub->getStatus() == "Submitted")? true : false; //ArrayUtils::get_field($data, 'submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$au}. {$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}. {$pg} {$pb}\n";
        }
        if (strlen($list_pub) > 0)
            $a2_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $a2_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_a2";
        $div_id = "div_a2";
        if($a2 > 0){
            $a2 =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a2
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">A) Referred Contributions / 2. Other referred contributions:</span> 
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
        $a12_details =  "<br /><span class='label'>A) Referred Contributions / 1. Articles in referred publications:</span><br />".
                        $a1_details .
                        "<br /><span class='label'>A) Referred Contributions / 2. Other referred contributions:</span><br />".
                        $a2_details;
        

        $lnk_id = "lnk_a12";
        $div_id = "div_a12";
        if($a12 > 0){
            $a12 =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $a12
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">Total Referred:</span> 
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
            $data = $pub->getData();
            $type = $pub->getType();
            $title = $pub->getTitle();
            $au = array();
            foreach($pub->getAuthors() as $a){
                if($a->getId()){
                    $au[] = "<strong>". $a->getNameForForms() ."</strong>";
                }else{
                    $au[] = $a->getNameForForms();
                }
            }
            $au = implode(', ', $au);
            $yr = substr($pub->getDate(), 0, 4);
            
            $vn = ArrayUtils::get_string($data, 'book_title', ArrayUtils::get_string($data, 'eventname', 'no venue'));

            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');
            $issub = ($pub->getStatus() == "Submitted")? true : false; //ArrayUtils::get_field($data, 'submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$au}. {$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}\n";
        }
        if (strlen($list_pub) > 0)
            $b_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $b_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_b";
        $div_id = "div_b";
        if($b > 0){
            $b =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
                $b
                </a>
                <div style="display: none;" id="$div_id" class="cell_details_div">
                    <p><span class="label">B) Non-referred contributions:</span> 
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
            $data = $pub->getData();
            $type = $pub->getType();
            $title = $pub->getTitle();
            $au = array();
            foreach($pub->getAuthors() as $a){
                if($a->getId()){
                    $au[] = "<strong>". $a->getNameForForms() ."</strong>";
                }else{
                    $au[] = $a->getNameForForms();
                }
            }
            $au = implode(', ', $au);
            $yr = substr($pub->getDate(), 0, 4);
            
            $vn = ArrayUtils::get_string($elem, 'book_title', ArrayUtils::get_string($elem, 'eventname', 'no venue'));

            if (strlen($pg) > 0){
                $pg = "{$pg}pp.";
            }
            else{
                $pg = "(no pages)";
            }
            $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');
            $issub = ($pub->getStatus() == "Submitted")? true : false; //ArrayUtils::get_field($data, 'submitted');
            if ($issub !== false){
                $ptr = &$list_sub;
            }
            else{
                $ptr = &$list_pub;
            }
            $ptr .= "<li>{$au}. {$yr}. <i>{$title}</i>.&emsp;{$type}: {$vn}\n";
        }
        if (strlen($list_pub) > 0)
            $c_details .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $c_details .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $lnk_id = "lnk_c";
        $div_id = "div_c";
        if($c > 0){
            $c =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
                        "<br /><span class='label'>B) Non-referred contributions:</span><br />". 
                        $b_details . 
                        "<br /><span class='label'>C) Specialized publications:</span><br />".
                        $c_details;

        $lnk_id = "lnk_total_pub";
        $div_id = "div_total_pub";
        if($total > 0){
            $total =<<<EOF
                <a id="$lnk_id" onclick="showdiv('#$div_id','$details_div_id');" href="#$details_div_id">
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
            <a id='Table7'></a><h3>Table 7: Publications List</h3>
            <table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
            <tr><th align='left'>A) Referred Contributions</th><th>Number of publications</th></tr>
            <tr><td>&emsp;1. Articles in referred publications</td><td align='center'>{$a1}</td></tr>
            <tr><td>&emsp;2. Other referred contributions</td><td align='center'>{$a2}</td></tr>
            <tr><td align='right'>Total referred</td><td align='center'>{$a12}</td></tr>
            <tr><th align='left'>B) Non-referred contributions<td align='center'>{$b}</td></tr>
            <tr><th align='left'>C) Specialized publications</th><td align='center'>{$c}</td></tr>
            <tr><th align='left'>Total publications</th><th>{$total}</th></tr>
            </table>
            <div class='pdf_hide details_div' id='$details_div_id' style='display: none;'></div>

EOF;

        $this->html .= $html;

    }

    private function create_table5_details(&$arr) {
        $id = "dis" . mt_rand();
        $ret = "&nbsp;<small><a href=\"javascript:ShowOrHide('{$id}','')\">Details</a></small><div id='{$id}' style='display:none;text-align:left'><ol>";
        foreach ($arr as $hqp => &$details) {
            $p_hqp = str_replace('_', '.', $hqp);
            $p_hqp = Person::newFromName($p_hqp);
            if (is_object($p_hqp))
                $p_hqp = $p_hqp->getNameForForms();
            else
                $p_hqp = str_replace('_', ' ', $hqp);

            $report = ArrayUtils::get_array($details, 'report');
            $reporters = array();
            foreach ($report as $uid)
                if (! array_key_exists($uid, $reporters))
                    $reporters[$uid] = Person::newFromId($uid);

            $field = ArrayUtils::get_array($details, 'field');
            $movingto = ArrayUtils::get_array($details, 'where');
            $original = ArrayUtils::get_array($details, 'original');

            $ret .= "<li><b>{$p_hqp}</b>:<ul>";
            $dupl = array();
            foreach ($report as $seq => $uid) {
                // Detect duplicates.  Some HQPs are part of multiple projects, and the
                // supervisors might repeat the content.
                if (in_array($uid, $dupl))
                    continue;
                else
                    $dupl[] = $uid;

                $p = ArrayUtils::get_field($reporters, $uid);
                if ($p === false)
                    // This should never happen, but just in case...
                    continue;

                $ret .= "<li>{$p->getNameForForms()} reports: " .
                    ArrayUtils::get_string($movingto, $seq) .
                    ", <i>" .
                    ArrayUtils::get_string($field, $seq) .
                    "</i> <small><br />From the report: &laquo;" .
                    ArrayUtils::get_string($original, $seq) .
                    "&raquo;</small></li>";
            }

            $ret .= "\n</ul></li>";
        }
        $ret .= "\n</ol></div>";

        return $ret;
    }

    private function show_disseminations() {
        global $wgOut, $_pdata, $_projects;

        if (self::preload_project_data() !== true)
            return;

        // Collect and organize the data hierarchically on distinct arrays, according to:
        // . publication index
        // .. author
        // ... project(s)
        $arr_a1_r1 = array();
        $arr_a1_r2 = array();
        $arr_a2_r1 = array();
        $arr_a2_r2 = array();
        $arr_b_r1 = array();
        $arr_b_r2 = array();
        foreach ($_projects as $proj) {
            $tmp = $_pdata[$proj->getId()]->get_metric(PJST_PUBLICATIONS);
            $tmparr = ArrayUtils::get_array($tmp, 'table7');
            $raw_a1 = ArrayUtils::get_array($tmparr, 'a1');
            $raw_a2 = ArrayUtils::get_array($tmparr, 'a2');
            $raw_b = ArrayUtils::get_array($tmparr, 'b');

            // Process data for each of the 6 arrays.
            self::extract_disseminations($raw_a1, $arr_a1_r1, $arr_a1_r2);
            self::extract_disseminations($raw_a2, $arr_a2_r1, $arr_a2_r2);
            self::extract_disseminations($raw_b, $arr_b_r1, $arr_b_r2);
        }

        $n_a1_r1 = count($arr_a1_r1);
        $n_a1_r2 = count($arr_a1_r2);
        $n_a2_r1 = count($arr_a2_r1);
        $n_a2_r2 = count($arr_a2_r2);
        $n_b_r1 = count($arr_b_r1);
        $n_b_r2 = count($arr_b_r2);

        $d_a1_r1 = self::create_dissemination_details($arr_a1_r1);
        $d_a1_r2 = self::create_dissemination_details($arr_a1_r2);
        $d_a2_r1 = self::create_dissemination_details($arr_a2_r1);
        $d_a2_r2 = self::create_dissemination_details($arr_a2_r2);
        $d_b_r1 = self::create_dissemination_details($arr_b_r1);
        $d_b_r2 = self::create_dissemination_details($arr_b_r2);

        $chunk = "
<a id='Table6'></a><h3>Table 6: Dissemination of Network Research Results and Collaborations</h3>
<table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
<tr><th align='left'>Articles in referred publications<th>Number of publications
<tr><td valign='top'>&emsp;All authors from one research group<td align='center'>{$n_a1_r1} {$d_a1_r1}
<tr><td valign='top'>&emsp;The authors from two or more research groups<td align='center'>{$n_a1_r2} {$d_a1_r2}
<tr><th align='left' colspan='2'>Other published refereed contributions
<tr><td valign='top'>&emsp;All authors from one research group<td align='center'>{$n_a2_r1} {$d_a2_r1}
<tr><td valign='top'>&emsp;The authors from two or more research groups<td align='center'>{$n_a2_r2} {$d_a2_r2}
<tr><th align='left' colspan='2'>Published non-refereed contributions
<tr><td valign='top'>&emsp;All authors from one research group<td align='center'>{$n_b_r1} {$d_b_r1}
<tr><td valign='top'>&emsp;The authors from two or more research groups<td align='center'>{$n_b_r2} {$d_b_r2}
</table>
";

        $this->html .= $chunk;
    }

    private function show_publication_list() {
        global $wgOut, $_pdata, $_projects;

        if (self::preload_project_data() !== true)
            return;

        // Collect.
        $arr_a1 = array();
        $arr_a2 = array();
        $arr_b = array();
        $arr_c = array();
        foreach ($_projects as $proj) {
            $tmp = $_pdata[$proj->getId()]->get_metric(PJST_PUBLICATIONS);
            $tmparr = ArrayUtils::get_array($tmp, 'table7');
            $arr_a1 = $arr_a1 + ArrayUtils::get_array($tmparr, 'a1');
            $arr_a2 = $arr_a2 + ArrayUtils::get_array($tmparr, 'a2');
            $arr_b = $arr_b + ArrayUtils::get_array($tmparr, 'b');
            $arr_c = $arr_c + ArrayUtils::get_array($tmparr, 'c');
        }

        // Compute.
        $a1 = count($arr_a1);
        $a2 = count($arr_a2);
        $b = count($arr_b);
        $c = count($arr_c);
        $a12 = $a1 + $a2;
        $total = $a12 + $b + $c;

        $chunk = "
<a id='Table7'></a><h3>Table 7: Publications List</h3>
<table class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
<tr><th align='left'>A) Referred Contributions<th>Number of publications
<tr><td>&emsp;1. Articles in referred publications<td align='center'>{$a1}
<tr><td>&emsp;2. Other referred contributions<td align='center'>{$a2}
<tr><td align='right'>Total referred<td align='center'>{$a12}
<tr><th align='left'>B) Non-referred contributions<td align='center'>{$b}
<tr><th align='left'>C) Specialized publications<td align='center'>{$c}
<tr><th align='left'>Total publications<th>{$total}
</table>
";

        // Details.
        $chunk .= "
<p>
<small><a href=\"javascript:ShowOrHide('table7details','')\">Show/Hide Details</a></small>
</p>
<div id='table7details' style='display:none;background-color:#e7e7e7'>
";
        $list_sub = '';
        $list_pub = '';
        foreach ($arr_a1 as &$elem) {
            $au = ArrayUtils::get_string($elem, 'authors', '(no author list)');
            $vn = ArrayUtils::get_string($elem, 'journal_title', 'no venue');
            $yr = ArrayUtils::get_string($elem, 'year', '(no year)');
            $pg = ArrayUtils::get_string($elem, 'pages');
            if (strlen($pg) > 0)
                $pg = "{$pg}pp.";
            else
                $pg = "(no pages)";
            $pb = ArrayUtils::get_string($elem, 'publisher', '(no publisher)');
            $issub = ArrayUtils::get_field($elem, 'submitted');
            if ($issub !== false)
                $ptr = &$list_sub;
            else
                $ptr = &$list_pub;
            $ptr .= "<li>{$au}. {$yr}. <i>{$elem['title']}</i>.&emsp;{$elem['__type__']}: {$vn}. {$pg} {$pb}\n";
        }
        $chunk .= "<p><b>Articles in refereed publications:</b></p><br />";
        if (strlen($list_pub) > 0)
            $chunk .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $chunk .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $list_sub = '';
        $list_pub = '';
        foreach ($arr_a2 as &$elem) {
            $au = ArrayUtils::get_string($elem, 'authors', '(no author list)');
            $vn = ArrayUtils::get_string($elem, 'book_title', 'no venue');
            $yr = ArrayUtils::get_string($elem, 'year', '(no year)');
            $pg = ArrayUtils::get_string($elem, 'pages');
            if (strlen($pg) > 0)
                $pg = "{$pg}pp.";
            else
                $pg = "(no pages)";
            $pb = ArrayUtils::get_string($elem, 'publisher', '(no publisher)');
            $issub = ArrayUtils::get_field($elem, 'submitted');
            if ($issub !== false)
                $ptr = &$list_sub;
            else
                $ptr = &$list_pub;
            $ptr .= "<li>{$au}. {$yr}. <i>{$elem['title']}</i>.&emsp;{$elem['__type__']}: {$vn}. {$pg} {$pb}\n";
        }
        $chunk .= "<p><b>Other refereed contributions:</b></p><br />";
        if (strlen($list_pub) > 0)
            $chunk .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $chunk .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $list_sub = '';
        $list_pub = '';
        foreach ($arr_b as &$elem) {
            $au = ArrayUtils::get_string($elem, 'authors',
                    ArrayUtils::get_string($elem, 'people', '(no author list)'));
            $vn = ArrayUtils::get_string($elem, 'book_title',
                    ArrayUtils::get_string($elem, 'eventname', 'no venue'));
            $yr = ArrayUtils::get_string($elem, 'year', '(no year)');
            $issub = ArrayUtils::get_field($elem, 'submitted');
            if ($issub !== false)
                $ptr = &$list_sub;
            else
                $ptr = &$list_pub;
            $ptr .= "<li>{$au}. {$yr}. <i>{$elem['title']}</i>.&emsp;({$elem['__type__']}: {$vn})\n";
        }
        $chunk .= "<p><b>Non-refereed contributions:</b></p><br />";
        if (strlen($list_pub) > 0)
            $chunk .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $chunk .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $list_sub = '';
        $list_pub = '';
        foreach ($arr_c as &$elem) {
            $au = ArrayUtils::get_string($elem, 'authors', '(no author list)');
            $yr = ArrayUtils::get_string($elem, 'year', '(no year)');
            $issub = ArrayUtils::get_field($elem, 'submitted');
            if ($issub !== false)
                $ptr = &$list_sub;
            else
                $ptr = &$list_pub;
            $ptr .= "<li>{$au}. {$yr}. <i>{$elem['title']}</i>.&emsp;({$elem['__type__']})\n";
        }
        $chunk .= "<p><b>Specialized Publications:</b></p><br />";
        if (strlen($list_pub) > 0)
            $chunk .= "Published:<ol>\n{$list_pub}\n</ol>";
        if (strlen($list_sub) > 0)
            $chunk .= "Submitted:<ol>\n{$list_sub}\n</ol>\n";

        $this->html .= $chunk;
    }

    private static function extract_disseminations(&$org, &$dst_single, &$dst_multi) {
        // Iterate over the publications in $org.
        foreach ($org as $ind => $blob) {
            // Instantiate each of the authors in the author lin by alias,
            // and save publication details (suitable for audit track).
            $aulist = ArrayUtils::get_field($blob, 'authors');
            if (! is_array($aulist))
                $aulist = explode(', ', $aulist);

            // Iterate over all authors in the author list, gathering
            // the projects from them.
            $projs = array();
            $usrprj = array();
            foreach ($aulist as $au) {
                try {
                    $usr = Person::newFromAlias($au);
                    if ($usr === null || $usr === false)
                        continue;

                    $uplist = array();
                    foreach ($usr->getProjects() as $p) {
                        // Mark project and include it for this author.
                        $projs[$p->getName()] = 1;
                        $uplist[] = $p->getName();
                    }

                    if (count($uplist) > 0)
                        // Collect project list from the author.
                        $usrprj[$usr->getNameForForms()] = $uplist;
                }
                catch (DomainException $de) {
                    // XXX: for now, don't do anything with an author
                    // that triggered an exception.
                }
            }

            // The projects are gathered.  Depending on how many different
            // projects, update the appropriate array (single project or
            // multiple projects array).
            switch (count($projs)) {
                case 0:
                    // No projects.
                    // XXX: log to investigate?
                    break;

                case 1:
                    $arr = ArrayUtils::get_array($dst_single, $ind);
                    $arr['details'] = $blob;
                    $arr['authors'] = ArrayUtils::get_array($arr, 'authors') + $usrprj;
                    $dst_single[$ind] = $arr;
                    break;

                default:
                    $arr = ArrayUtils::get_array($dst_multi, $ind);
                    $arr['details'] = $blob;
                    $arr['authors'] = ArrayUtils::get_array($arr, 'authors') + $usrprj;
                    $dst_multi[$ind] = $arr;
            }
        }
    }

    private function create_dissemination_details($arr) {
        if (empty($arr))
            return "";

        // Grab a random identifier to name this <div>.
        $id = "dis" . mt_rand();
        $ret = "<small><a href=\"javascript:ShowOrHide('{$id}','')\">Details</a></small><div id='{$id}' style='display:none;text-align:left'><ol>";
        foreach ($arr as $publ) {
            $det = ArrayUtils::get_array($publ, 'details');
            $aut = ArrayUtils::get_array($publ, 'authors');
            $ret .= "\n<li>" . ArrayUtils::get_string($det, 'title') . "\n<ul>";
            foreach ($aut as $name => $projs) {
                $ret .= "\n<li>{$name} <small>(" . implode(', ', $projs) . ")</small></li>";
            }
            $ret .= "\n</ul></li>";
        }

        $ret .= "\n</ol></div>";

        return $ret;
    }

    function preload_project_data() {
        global $_pdata, $_pdata_loaded, $_projects;

        if ($_pdata_loaded)
            return true;

        $_pdata = array();
        $_projects = Project::getAllProjects();
        foreach ($_projects as $project) {
            $_pdata[$project->getId()] = new ProjectProductivity($project);
        }

        $_pdata_loaded = true;
        return true;
    }

    static function dollar_format($val) {
        return '$&nbsp;' . number_format($val, 2);
    }

    static function create_details_abbrev(&$txt, $len = 72) {
        $abbr = substr($txt, 0, $len);
        if (strlen($txt) - strlen($abbr) > 60) {
            // Worth abbreviating.
            $id = "dis" . mt_rand();
            return "<div id='{$id}M'>{$abbr}&hellip;<small>" .
                "<a href=\"javascript:ShowOrHide('{$id}','{$id}M')\">(More)</a>" .
                "</small></div><div id='{$id}' style='display:none;text-align:left'>{$txt} " .
                "<a href=\"javascript:ShowOrHide('{$id}','{$id}M')\">(Less)</a></div>";
        }
        else
            // Too short.
            return $txt;
    }
}

?>

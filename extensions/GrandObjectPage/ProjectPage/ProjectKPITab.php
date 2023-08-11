<?php

class ProjectKPITab extends AbstractTab {

    static $autoProjects = array("GIS-03", "GIS-07", "GIS-13");

    static $qMap = array(1 => "Apr-Jun",
                         2 => "Jul-Sep",
                         3 => "Oct-Dec",
                         4 => "Jan-Mar");

    var $project;
    var $visibility;

    function ProjectKPITab($project, $visibility){
        parent::AbstractTab("KPI Summary");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function generatePDFBody(){
        $this->showKPI(true);
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        
        $this->showKPI();
        
        return $this->html;
    }
    
    static function optimizeFn($obj, $project=null, $start_date="0000-00-00", $end_date="2100-01-01"){
        // Optimize formulas
        $sheets = $obj->getAllSheets();

        $max = 0;
        for($i=2; $i<count($sheets); $i++){
            $obj->setActiveSheetIndex($i);
            $sheet = $obj->getActiveSheet();
            $cells = @$sheet->toArray(null, false, false);
            $nRows = 0;
            foreach($cells as $rowN => $row){
                if($row[0] != "" || $rowN <= 25){
                    $nRows++;
                }
            }
            $nRows = min(200, $nRows);
            $max = max($max, $nRows);
            
            foreach($cells as $rowN => $row){
                foreach($row as $colN => $col){
                    if(strstr($cells[$rowN][$colN], "=") !== false && strstr($cells[$rowN][$colN], "200") !== false){
                        $cells[$rowN][$colN] = str_replace("200", $nRows, $cells[$rowN][$colN]);
                    }
                }
            }
            @$sheet->fromArray($cells, null, 'A1', false);
        }

        $obj->setActiveSheetIndex(min(1, count($sheets)-1));
        $sheet = $obj->getActiveSheet();
        $cells = @$sheet->toArray(null, false, false);
        foreach($cells as $rowN => $row){
            foreach($row as $colN => $col){
                if(strstr($cells[$rowN][$colN], "=") !== false && strstr($cells[$rowN][$colN], "200") !== false){
                    $cells[$rowN][$colN] = str_replace("200", $max, $cells[$rowN][$colN]);
                }
            }
        }
        
        // Retrieving from LIMS
        if($project != null && (in_array($project->getName(), self::$autoProjects))){
            $userTypes = array();
            $userGeos = array();
            $userSectors = array();
            $section2 = array();
            $section5 = array();
            $section8 = array();
            $prods = array();
             
            $users = array();
            $requests = LIMSOpportunity::newFromProjectId($project->getId(), $start_date, $end_date);
            foreach($requests as $request){
                $contact = $request->getContact();
                $details = $contact->getDetails();
                $tasks = $request->getTasks();
                $products = $request->getProducts();
                $users[$contact->getId()] = $contact;
                @$userTypes[$request->getUserType()]++;
                @$section2['requests']++;
                if($request->getSurveyed() == "Yes"){
                    @$section5['surveyed']++;
                }
                foreach($tasks as $task){
                    if($task->getStatus() == "Closed"){
                        @$section2['accommodated']++;
                        break;
                    }
                }
                foreach($products as $product){
                    if($product->type == "Total Value of Research Grants & Awards Held by Facility Staff and Faculty" ||
                       $product->type == "Total Number of Research Grants and Awards Held by Facility Staff and Faculty"){
                        @$prods[$product->type] += intval($product->text);
                    }
                    else{
                        @$prods[$product->type]++;
                    }
                }
                
                if($details->hqp == "Yes"){
                    $pCount = 0;
                    foreach($products as $product){
                        if($product->type == "Courses, Workshops & Training Sessions" ||
                           $product->type == "Public Events Hosted by Facility (Symposia, Conferences, Open Houses, Tours)" ||
                           $product->type == "Media Interviews, Press Conferences & Broadcasts" ||
                           $product->type == "Stakeholder Events Attended by GIS Personnel Conferences, Tradeshows & Industry, Governments, Community Events"){
                            @$section8[$details->hqp_other]++;
                        }
                    }
                }
                @$userGeos[$details->geographic]++;
                @$userSectors[$details->sector]++;
            }
            
            // Section 1
            $cells[5][2] = (is_numeric($cells[5][2])) ? @$userTypes['On site'] : $cells[5][2];
            $cells[6][2] = (is_numeric($cells[6][2])) ? @$userTypes['Remote'] : $cells[6][2];
            $cells[7][2] = (is_numeric($cells[7][2])) ? @$userTypes['Data'] : $cells[7][2];
            
            $cells[10][2] = (is_numeric($cells[10][2])) ? @$userGeos['Alberta'] : $cells[10][2];
            $cells[11][2] = (is_numeric($cells[11][2])) ? @$userGeos['British Columbia'] : $cells[11][2];
            $cells[12][2] = (is_numeric($cells[12][2])) ? @$userGeos['Manitoba'] : $cells[12][2];
            $cells[13][2] = (is_numeric($cells[13][2])) ? @$userGeos['New Brunswick'] : $cells[13][2];
            $cells[14][2] = (is_numeric($cells[14][2])) ? @$userGeos['Newfoundland'] : $cells[14][2];
            $cells[15][2] = (is_numeric($cells[15][2])) ? @$userGeos['Nova Scotia'] : $cells[15][2];
            $cells[16][2] = (is_numeric($cells[16][2])) ? @$userGeos['Ontario'] : $cells[16][2];
            $cells[17][2] = (is_numeric($cells[17][2])) ? @$userGeos['Quebec'] : $cells[17][2];
            $cells[18][2] = (is_numeric($cells[18][2])) ? @$userGeos['Saskatchewan'] : $cells[18][2];
            $cells[19][2] = (is_numeric($cells[19][2])) ? @$userGeos['North West Territories'] : $cells[19][2];
            $cells[20][2] = (is_numeric($cells[20][2])) ? @$userGeos['Prince Edward Island'] : $cells[20][2];
            $cells[21][2] = (is_numeric($cells[21][2])) ? @$userGeos['Nunavut'] : $cells[21][2];
            $cells[22][2] = (is_numeric($cells[22][2])) ? @$userGeos['Yukon'] : $cells[22][2];
            $cells[23][2] = (is_numeric($cells[23][2])) ? @$userGeos['Outside Canada: United States'] : $cells[23][2];
            $cells[24][2] = (is_numeric($cells[24][2])) ? @$userGeos['Outside Canada: other than United States'] : $cells[24][2];
            
            $cells[27][2] = (is_numeric($cells[27][2])) ? @$userSectors['University, college, research hospital'] : $cells[27][2];
            $cells[28][2] = (is_numeric($cells[28][2])) ? @$userSectors['Other public'] : $cells[28][2];
            $cells[29][2] = (is_numeric($cells[29][2])) ? @$userSectors['Private'] : $cells[29][2];
            $cells[30][2] = (is_numeric($cells[30][2])) ? @$userSectors['Not-for-profit'] : $cells[30][2];

            // Section 2
            $cells[35][2] = (is_numeric($cells[35][2])) ? @$section2['requests'] : $cells[35][2];
            $cells[36][2] = (is_numeric($cells[36][2])) ? @$section2['accommodated'] : $cells[36][2];
            
            // Section 5
            $cells[54][2] = (is_numeric($cells[54][2])) ? @$section5['surveyed'] : $cells[54][2];
            
            // Section 6
            $cells[60][2] = (is_numeric($cells[60][2])) ? @$prods['Peer Reviewed Publications'] : $cells[60][2];
            $cells[61][2] = (is_numeric($cells[61][2])) ? @$prods['Other Publication (e.g. Trade Journal)'] : $cells[61][2];
            $cells[62][2] = (is_numeric($cells[62][2])) ? @$prods['Conference Presentations (Oral and Poster)'] : $cells[62][2];
            $cells[63][2] = (is_numeric($cells[63][2])) ? @$prods['Monographs, Books, Book Chapters'] : $cells[63][2];
            
            // Section 7
            $cells[68][2] = (is_numeric($cells[68][2])) ? @$prods['Courses, Workshops & Training Sessions'] : $cells[68][2];
            $cells[69][2] = (is_numeric($cells[69][2])) ? @$prods['Public Events Hosted by Facility (Symposia, Conferences, Open Houses, Tours)'] : $cells[69][2];
            $cells[70][2] = (is_numeric($cells[70][2])) ? @$prods['Media Interviews, Press Conferences & Broadcasts'] : $cells[70][2];
            $cells[71][2] = (is_numeric($cells[71][2])) ? @$prods['Stakeholder Events Attended by GIS Personnel Conferences, Tradeshows & Industry, Governments, Community Events'] : $cells[71][2];
            
            // Section 8
            $cells[76][2] = (is_numeric($cells[76][2])) ? @$section8['College Students'] : $cells[76][2];
            $cells[77][2] = (is_numeric($cells[77][2])) ? @$section8['University Undergrad Students'] : $cells[77][2];
            $cells[78][2] = (is_numeric($cells[78][2])) ? @$section8['M.Sc. Students'] : $cells[78][2];
            $cells[79][2] = (is_numeric($cells[79][2])) ? @$section8['Ph.D. Students'] : $cells[79][2];
            $cells[80][2] = (is_numeric($cells[80][2])) ? @$section8['PDFs'] : $cells[80][2];
            $cells[81][2] = (is_numeric($cells[81][2])) ? @$section8['Scientific & Technical Personnel (Outside GIS)'] : $cells[81][2];
            
            // Section 9
            $cells[86][2] = (is_numeric($cells[86][2])) ? @$prods['Technical & Consultancy Reports'] : $cells[86][2];
            $cells[87][2] = (is_numeric($cells[87][2])) ? @$prods['Provisional Patent Applications Filed'] : $cells[87][2];
            $cells[88][2] = (is_numeric($cells[88][2])) ? @$prods['PCT Application Filed & Patents Granted'] : $cells[88][2];
            $cells[89][2] = (is_numeric($cells[89][2])) ? @$prods['Outlicenses'] : $cells[89][2];
            $cells[90][2] = (is_numeric($cells[90][2])) ? @$prods['Spin-Off Companies Created'] : $cells[90][2];
            
            // Section 10
            $cells[95][2] = (is_numeric($cells[95][2])) ? @$prods['Collaboration with Industry Partners'] : $cells[95][2];
            $cells[96][2] = (is_numeric($cells[96][2])) ? @$prods['Collaborations with Scientific Institutions'] : $cells[96][2];
            $cells[97][2] = (is_numeric($cells[97][2])) ? @$prods['Total Value of Research Grants & Awards Held by Facility Staff and Faculty'] : $cells[97][2];
            $cells[98][2] = (is_numeric($cells[98][2])) ? @$prods['Total Number of Research Grants and Awards Held by Facility Staff and Faculty'] : $cells[98][2];
        }
        
        @$sheet->fromArray($cells, null, 'A1', false);
        return $obj;
    }
    
    static function getKPITemplate(){
        global $config;
        if(Cache::exists("KPI_Template")){
            return Cache::fetch("KPI_Template");
        }
        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_KPI_STRUCTURE');
        $summary = new Budget("XLS", $structure, file_get_contents("data/GIS KPIs.xlsx"), 1, "ProjectKPITab::optimizeFn");
        $summary->xls[69][1]->style .= "white-space: initial;";
        $summary->xls[71][1]->style .= "white-space: initial;";
        $summary->xls[97][1]->style .= "white-space: initial;";
        $summary->xls[98][1]->style .= "white-space: initial;";
        Cache::store("KPI_Template", $summary, 86400*7);
        return $summary;
    }
    
    static function getKPI($project, $id, $start_date, $end_date){
        global $config;
        if(Cache::exists("{$project->getId()}_{$id}")){
            return Cache::fetch("{$project->getId()}_{$id}");
        }
        $kpi = null;
        $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $project->getId());
        $addr = ReportBlob::create_address("RP_KPI", "KPI", $id, 0);
        $blb->load($addr, true);
        $xls = $blb->getData();
        $md5 = $blb->getMD5();
        if($xls == null){
            $xls = file_get_contents("data/KPI_Empty.xlsx");
        }

        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_KPI_STRUCTURE');
        $kpi = new Budget("XLS", $structure, $xls, 1, "ProjectKPITab::optimizeFn", array($project, $start_date, $end_date));
        $kpi->xls[69][1]->style .= "white-space: initial;";
        $kpi->xls[71][1]->style .= "white-space: initial;";
        $kpi->xls[97][1]->style .= "white-space: initial;";
        $kpi->xls[98][1]->style .= "white-space: initial;";
        foreach(array_merge($kpi->xls[0],$kpi->xls[1]) as $i => $cell){
            $cell->style .= "display:none;";
        }
        foreach($kpi->xls as $i => $row){
            $row[0]->style .= "display:none;";
        }
        if(!in_array($project->getName(), self::$autoProjects)){
            Cache::store("{$project->getId()}_{$id}", array($kpi, $md5), 86400*7);
        }
        return array($kpi, $md5);
    }
    
    static function addKPI($kpi1, $kpi2){
        foreach($kpi2->xls as $key => $row){
            if(@is_numeric($row[2]->value)){
                $kpi1->xls[$key][2]->value += $row[2]->value;
            }
        }
        // Handle Percents differently
        $kpi1->xls[37][2]->value = $kpi1->xls[36][2]->value/max(1, $kpi1->xls[35][2]->value);
        $kpi1->xls[56][2]->value = $kpi1->xls[55][2]->value/max(1, $kpi1->xls[54][2]->value);
        return $kpi1;
    }

    function showKPI(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $project = $this->project;
        
        if($me->isRoleAtLeast(STAFF)){
        //if($me->isMemberOf($this->project) || $this->visibility['isLead']){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#kpiAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            $this->html .= "<div id='kpiAccordion'>";
            $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Roll-over kpi in April
            
            $phaseDates = $config->getValue("projectPhaseDates");
            $startYear = date('Y', strtotime($project->getCreated()) - (3 * 30 * 24 * 60 * 60));
            
            for($i=$endYear; $i >= $startYear; $i--){
                foreach(self::$qMap as $q => $quarter){
                    switch($q){
                        case 1:
                            $date = "{$i}-04-01";
                            $enddate = "{$i}-07-01";
                            break;
                        case 2:
                            $date = "{$i}-07-01";
                            $enddate = "{$i}-10-01";
                            break;
                        case 3:
                            $date = "{$i}-10-01";
                            $enddate = ($i+1)."-01-01";
                            break;
                        case 4:
                            $date = ($i+1)."-01-01";
                            $enddate = ($i+1)."-04-01";
                            break;
                    }
                    if(date('Y-m-d') < $date || substr($project->getCreated(), 0, 10) > $enddate){
                        continue;
                    }
                    $this->html .= "<h3><a href='#'>".$i."/".substr($i+1,2,2)." Q{$q}</a></h3>";
                    $this->html .= "<div style='overflow: auto;'>";
                    
                    // KPI
                    list($kpi, $md5) = ProjectKPITab::getKPI($this->project, "KPI_{$i}_Q{$q}", $date, $enddate);
                    if($kpi != null){
                        $this->html .= "<div id='KPI_{$i}_Q{$q}'>{$kpi->render()}</div><br />";
                    }
                    if($md5 != null){
                        $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Q{$q}_KPI.xlsx'>Download KPI</a><br />";
                    }
                    else{
                        if(in_array($this->project->getName(), self::$autoProjects)){
                            $this->html .= "<a class='externalLink' style='cursor:pointer;' id='download_KPI_{$i}_Q{$q}'>Download KPI (Auto-Generated)</a>
                            <script type='text/javascript'>
                                $('#download_KPI_{$i}_Q{$q}').click(function(){
                                    window.open('data:application/vnd.ms-excel;base64,' + base64Conversion($('#KPI_{$i}_Q{$q} table')[0].outerHTML));
                                });
                            </script>";
                        }
                    }
                    $this->html .="</div>";
                }
            }
            $this->html .= "</div>";
        }
    }
}    
    
?>

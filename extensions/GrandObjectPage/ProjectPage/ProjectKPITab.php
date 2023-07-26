<?php

class ProjectKPITab extends AbstractEditableTab {

    static $qMap = array(1 => "Apr-Jun",
                         2 => "Jul-Sep",
                         3 => "Oct-Dec",
                         4 => "Jan-Mar");

    var $project;
    var $visibility;

    function ProjectKPITab($project, $visibility){
        parent::AbstractTab("KPI");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        global $config, $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_FILES)){
            foreach($_FILES as $key => $file){
                foreach($file['tmp_name'] as $year_q => $tmp){
                    if($tmp != ""){
                        $contents = file_get_contents($tmp);
                        $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                        $addr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$year_q}", 0);
                        $blb->store($contents, $addr);
                        Cache::delete("{$this->project->getId()}_KPI_{$year_q}");
                    }
                }
            }
        }
        redirect($this->project->getUrl()."?tab=kpi");
    }
    
    function canEdit(){
        return ($this->visibility['isLead']);
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
    
    function generateEditBody(){
        return $this->generateBody();
    }
    
    static function optimizeFn($obj, $project=null, $start_date="0000-00-00", $end_date="2100-01-01", $min=200){
        // Optimize formulas
        $sheets = $obj->getAllSheets();
        
        if(count($sheets) == 1){
            return $obj;
        }

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
            $nRows = min($min, $nRows);
            $max = max($max, $nRows);
            
            foreach($cells as $rowN => $row){
                foreach($row as $colN => $col){
                    if(strstr($cells[$rowN][$colN], "=") !== false && strstr($cells[$rowN][$colN], "200") !== false){
                        $cells[$rowN][$colN] = str_replace("200", $nRows, $cells[$rowN][$colN]);
                        if($min == 0){
                            $cells[$rowN][$colN] = 0;
                        }
                    }
                }
            }
            @$sheet->fromArray($cells, null, 'A1', true);
        }

        $obj->setActiveSheetIndex(1);
        $sheet = $obj->getActiveSheet();
        $cells = @$sheet->toArray(null, false, false);
        foreach($cells as $rowN => $row){
            foreach($row as $colN => $col){
                if(strstr($cells[$rowN][$colN], "=") !== false && strstr($cells[$rowN][$colN], "200") !== false){
                    $cells[$rowN][$colN] = str_replace("200", $max, $cells[$rowN][$colN]);
                    if($min == 0){
                        $cells[$rowN][$colN] = 0;
                    }
                }
            }
        }
        
        // Retrieving from LIMS
        if($project != null){
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
            $cells[5][2] += @$userTypes['On site'];
            $cells[6][2] += @$userTypes['Remote'];
            $cells[7][2] += @$userTypes['Data'];
            
            $cells[10][2] += @$userGeos['Alberta'];
            $cells[11][2] += @$userGeos['British Columbia'];
            $cells[12][2] += @$userGeos['Manitoba'];
            $cells[13][2] += @$userGeos['New Brunswick'];
            $cells[14][2] += @$userGeos['Newfoundland'];
            $cells[15][2] += @$userGeos['Nova Scotia'];
            $cells[16][2] += @$userGeos['Ontario'];
            $cells[17][2] += @$userGeos['Quebec'];
            $cells[18][2] += @$userGeos['Saskatchewan'];
            $cells[19][2] += @$userGeos['North West Territories'];
            $cells[20][2] += @$userGeos['Prince Edward Island'];
            $cells[21][2] += @$userGeos['Nunavut'];
            $cells[22][2] += @$userGeos['Yukon'];
            $cells[23][2] += @$userGeos['Outside Canada: United States'];
            $cells[24][2] += @$userGeos['Outside Canada: other than United States'];
            
            $cells[27][2] += @$userSectors['University, college, research hospital'];
            $cells[28][2] += @$userSectors['Other public'];
            $cells[29][2] += @$userSectors['Private'];
            $cells[30][2] += @$userSectors['Not-for-profit'];

            // Section 2
            $cells[35][2] += @$section2['requests'];
            $cells[36][2] += @$section2['accommodated'];
            
            // Section 5
            $cells[54][2] += @$section5['surveyed'];
            
            // Section 6
            $cells[60][2] += @$prods['Peer Reviewed Publications'];
            $cells[61][2] += @$prods['Other Publication (e.g. Trade Journal)'];
            $cells[62][2] += @$prods['Conference Presentations (Oral and Poster)'];
            $cells[63][2] += @$prods['Monographs, Books, Book Chapters'];
            
            // Section 7
            $cells[68][2] += @$prods['Courses, Workshops & Training Sessions'];
            $cells[69][2] += @$prods['Public Events Hosted by Facility (Symposia, Conferences, Open Houses, Tours)'];
            $cells[70][2] += @$prods['Media Interviews, Press Conferences & Broadcasts'];
            $cells[71][2] += @$prods['Stakeholder Events Attended by GIS Personnel Conferences, Tradeshows & Industry, Governments, Community Events'];
            
            // Section 8
            $cells[76][2] += @$section8['College Students'];
            $cells[77][2] += @$section8['University Undergrad Students'];
            $cells[78][2] += @$section8['M.Sc. Students'];
            $cells[79][2] += @$section8['Ph.D. Students'];
            $cells[80][2] += @$section8['PDFs'];
            $cells[81][2] += @$section8['Scientific & Technical Personnel (Outside GIS)'];
            
            // Section 9
            $cells[86][2] += @$prods['Technical & Consultancy Reports'];
            $cells[87][2] += @$prods['Provisional Patent Applications Filed'];
            $cells[88][2] += @$prods['PCT Application Filed & Patents Granted'];
            $cells[89][2] += @$prods['Outlicenses'];
            $cells[90][2] += @$prods['Spin-Off Companies Created'];
            
            // Section 10
            $cells[95][2] += @$prods['Collaboration with Industry Partners'];
            $cells[96][2] += @$prods['Collaborations with Scientific Institutions'];
            $cells[97][2] += @$prods['Total Value of Research Grants & Awards Held by Facility Staff and Faculty'];
            $cells[98][2] += @$prods['Total Number of Research Grants and Awards Held by Facility Staff and Faculty'];
        }
        
        @$sheet->fromArray($cells, null, 'A1', true);
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
            //return Cache::fetch("{$project->getId()}_{$id}");
        }
        $kpi = null;
        $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $project->getId());
        $addr = ReportBlob::create_address("RP_KPI", "KPI", $id, 0);
        $blb->load($addr, true);
        $xls = $blb->getData();
        $md5 = $blb->getMD5();
        $min = 200;
        if($xls == null){
            $xls = file_get_contents("data/GIS KPIs.xlsx");
            $min = 0;
        }

        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_KPI_STRUCTURE');
        $kpi = new Budget("XLS", $structure, $xls, 1, "ProjectKPITab::optimizeFn", array($project, $start_date, $end_date, $min));
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
        //Cache::store("{$project->getId()}_{$id}", array($kpi, $md5), 86400*7);

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
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if($me->isMemberOf($this->project) || $this->visibility['isLead']){
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
                foreach(array_reverse(self::$qMap, true) as $q => $quarter){
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
                    if($edit){
                        $lastblb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                        $lastaddr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$i}_Q".($q-1), 0);
                        $lastblb->load($lastaddr, true);
                        $lastmd5 = $lastblb->getMD5();
                        if($q > 1 && $lastmd5 != ""){
                            $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$lastmd5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Q".($q-1)."_KPI.xlsx'>Download Q".($q-1)." KPI</a>";
                        }
                        else{
                            $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/GIS KPIs.xlsx'>Download Template</a>";
                        }
                        $this->html .= "<h3 style='margin-top: 0;'>Upload KPI</h3>
                                        <input type='file' name='kpi[{$i}_Q{$q}]' accept='.xlsx' />";
                    }
                    
                    if(!$edit){
                        list($kpi, $md5) = ProjectKPITab::getKPI($this->project, "KPI_{$i}_Q{$q}", $date, $enddate);
                        if($kpi != null){
                            $this->html .= "<div id='KPI_{$i}_Q{$q}'>{$kpi->render()}</div><br />";
                        }
                        if($md5 != null){
                            $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Q{$q}_KPI.xlsx'>Download KPI</a><br />";
                        }
                        else{
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

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

    function showKPI(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_KPI_STRUCTURE');
        
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
                    $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                    $addr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$i}_Q{$q}", 0);
                    $result = $blb->load($addr, true);
                    $md5 = $blb->getMD5();
                    $xls = $blb->getData();
                    
                    if($edit){
                        $lastblb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                        $lastaddr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$i}_Q".($q-1), 0);
                        $lastblb->load($lastaddr, true);
                        $lastmd5 = $blb->getMD5();
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
                        //$xls = null;
                        if($xls != null){
                            $kpi = new Budget("XLS", $structure, $xls, 1);
                            $kpi->xls[69][1]->style .= "white-space: initial;";
                            $kpi->xls[71][1]->style .= "white-space: initial;";
                            $kpi->xls[97][1]->style .= "white-space: initial;";
                            $kpi->xls[98][1]->style .= "white-space: initial;";
                            $this->html .= $kpi->render()."<br />";
                            $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Q{$q}_KPI.xlsx'>Download KPI</a><br />";
                        }
                        else{
                            $this->html .= "No KPI uploaded";
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

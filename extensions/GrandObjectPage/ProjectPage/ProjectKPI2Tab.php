<?php

class ProjectKPI2Tab extends ProjectKPITab {

    function __construct($project, $visibility){
        AbstractTab::__construct("KPI Report");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        global $config, $wgMessage, $wgScriptPath, $wgAdditionalMailParams;
        $me = Person::newFromWgUser();

        if(isset($_POST['kpi_delete'])){
            foreach($_POST['kpi_delete'] as $year_q => $del){
                $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                $addr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$year_q}", 0);
                $blb->delete($addr);
                Cache::delete("{$this->project->getId()}_KPI_{$year_q}");
                $wgMessage->addSuccess(str_replace("_", " ", $year_q)." KPI Deleted");
            }
        }
        
        if(isset($_FILES)){
            $alreadySent = false;
            foreach($_FILES as $key => $file){
                foreach($file['tmp_name'] as $year_q => $tmp){
                    if($tmp != ""){
                        $contents = file_get_contents($tmp);
                        $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                        $addr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$year_q}", 0);
                        $blb->store($contents, $addr);
                        Cache::delete("{$this->project->getId()}_KPI_{$year_q}");
                        $wgMessage->addSuccess(str_replace("_", " ", $year_q)." KPI Uploaded");
                        if($wgScriptPath == "" && !$alreadySent){
                            $from = "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
                            $headers = "Content-type: text/html\r\n"; 
                            $headers .= $from;
                            mail("vsharko@glyconet.ca", "GIS Report Updated", "A KPI Report was updated: <a href='{$this->project->getUrl()}'>{$this->project->getName()}</a>", $headers, $wgAdditionalMailParams);
                            $alreadySent = true;
                        }
                    }
                }
            }
        }
        redirect($this->project->getUrl()."?tab=kpi-report");
    }
    
    function canEdit(){
        return ($this->visibility['isLead']);
    }
    
    function generatePDFBody(){
        // DO Nothing
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if($this->canEdit()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if(isset($_POST['save'])){
                $this->handleEdit();
            }
            $this->showKPI();
        }
        return $this->html;
    }

    function showKPI(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $project = $this->project;
        
        if($me->isMemberOf($this->project) || $this->visibility['isLead']){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#kpiEditAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            $this->html .= "<form action='{$this->project->getUrl()}' method='post' enctype='multipart/form-data'>";
            $this->html .= "<div id='kpiEditAccordion'>";
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
                    $lastblb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                    $lastaddr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$i}_Q{$q}", 0);
                    $lastblb->load($lastaddr, true);
                    $lastmd5 = $lastblb->getMD5();
                    $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/GIS KPIs.xlsx'>Download Template</a><br />";
                    
                    $this->html .= "<h3 style='margin-top: 0;'>Upload KPI Report</h3>";
                    $this->html .= "<input type='file' name='kpi[{$i}_Q{$q}]' accept='.xlsx' /><br />";
                    if($lastmd5 != ""){
                        $this->html .= "<p style='margin-bottom: 0.5em;'><b>Delete?</b> <input type='checkbox' name='kpi_delete[{$i}_Q{$q}]' /></p>";
                        if($me->isRoleAtLeast(STAFF)){
                            $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$lastmd5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Q{$q}_KPI.xlsx'>Download KPI Report</a><br />";
                        }
                    }
                    $this->html .= "<input type='submit' name='save' value='Save' style='margin-top:0.5em;' />";
                    $this->html .="</div>";
                }
            }
            $this->html .= "</div></form>";
        }
    }
}    
    
?>

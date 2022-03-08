<?php

class ProjectFESProjectionsTab extends ProjectFESReportTab {

    function ProjectFESProjectionsTab($person, $visibility){
        parent::AbstractEditableTab("Projections");
        $this->project = $person;
        $this->visibility = $visibility;
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function generatePDFBody(){
        $this->generateBody();
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        // Check that they are leader
        if($me->isRoleAtLeast(STAFF) ||
           $me->isRole(PL, $this->project) || 
           $me->isRole(PA, $this->project)){
            return true;
        }
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_PROJECTIONS) && parent::canEdit());
    }

    function generateBody(){
        global $wgOut, $config;
        if(!$this->userCanView()){
            return;
        }
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#projectionsAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='projectionsAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y');//, time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_GET['generatePDF'])){
            // Only show the last year in the PDF
            $today = date('Y') - 1;
            $year = $today;
        }
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        $structure = Product::structure();
        for($y=$today; $y >= $year; $y--){
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>Recruitment</h3>
                            <table class='wikitable' frame='box' rules='all'>
                                <tr><th>Role</th><th>Projected #</th></tr>
                                <tr><td>Undergrad</td><td align='right'>{$this->getBlobData("Undergrad", $y)}</td></tr>
                                <tr><td>MSc</td><td align='right'>{$this->getBlobData("MSc", $y)}</td></tr>
                                <tr><td>PhD</td><td align='right'>{$this->getBlobData("PhD", $y)}</td></tr>
                                <tr><td>PDF</td><td align='right'>{$this->getBlobData("PDF", $y)}</td></tr>
                                <tr><td>Research Associate</td><td align='right'>{$this->getBlobData("Research Associate", $y)}</td></tr>
                                <tr><td>Technician</td><td align='right'>{$this->getBlobData("Technician", $y)}</td></tr>
                                <tr><td>Other HQP ({$this->getBlobData("Other HQP Spec", $y)})</td><td align='right'>{$this->getBlobData("Other HQP", $y)}</td></tr>
                                <tr><td>Administrative Staff ({$this->getBlobData("Administrative Staff Spec", $y)})</td><td align='right'>{$this->getBlobData("Administrative Staff", $y)}</td></tr>
                                <tr><td>Other ({$this->getBlobData("Other Spec", $y)})</td><td align='right'>{$this->getBlobData("Other", $y)}</td></tr>
                            </table>
                            
                            <h3>".Inflect::pluralize($config->getValue('productsTerm'))."</h3>
                            <table class='wikitable' frame='box' rules='all'>
                                <tr><th>Type</th><th>Projected #</th></tr>";
            foreach($structure['categories'] as $cat => $category){
                $types = $category['types'];
                $this->html .= "<tr><th colspan='2'>{$cat}</th></tr>";
                foreach($types as $type => $data){
                    if($type == "Misc"){
                        continue;
                    }
                    $this->html .= "<tr><td>{$type}</td><td align='right'>{$this->getBlobData("$type", $y)}</td></tr>";
                }
                $this->html .= "<tr><td>Other ({$this->getBlobData("Other {$cat} Spec", $y)})</td><td align='right'>{$this->getBlobData("Other $cat", $y)}</td></tr>";
            }
            $this->html .= "</table>";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function generateEditBody(){
        global $wgOut, $config;
        if(!$this->canEdit()){
            return;
        }
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#projectionsAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='projectionsAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y');//, time() - (6 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        $structure = Product::structure();
        for($y=$today; $y >= $year; $y--){
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>Recruitment</h3>
                            <table class='wikitable' frame='box' rules='all'>
                                <tr><th>Role</th><th>Projected #</th></tr>
                                <tr><td>Undergrad</td><td><input type='number' name='report_undergrad[$y]' value='{$this->getBlobData("Undergrad", $y)}' /></td></tr>
                                <tr><td>MSc</td><td><input type='number' name='report_msc[$y]' value='{$this->getBlobData("MSc", $y)}' /></td></tr>
                                <tr><td>PhD</td><td><input type='number' name='report_phd[$y]' value='{$this->getBlobData("PhD", $y)}' /></td></tr>
                                <tr><td>PDF</td><td><input type='number' name='report_pdf[$y]' value='{$this->getBlobData("PDF", $y)}' /></td></tr>
                                <tr><td>Research Associate</td><td><input type='number' name='report_research_associate[$y]' value='{$this->getBlobData("Research Associate", $y)}' /></td></tr>
                                <tr><td>Technician</td><td><input type='number' name='report_technician[$y]' value='{$this->getBlobData("Technician", $y)}' /></td></tr>
                                <tr><td>Other HQP<br />(Specify: <input type='text' name='report_other_hqp_spec[$y]' value='{$this->getBlobData("Other HQP Spec", $y)}' />)</td><td><input type='number' name='report_other_hqp[$y]' value='{$this->getBlobData("Other HQP", $y)}' /></td></tr>
                                <tr><td>Administrative Staff<br />(Specify: <input type='text' name='report_administrative_staff_spec[$y]' value='{$this->getBlobData("Administrative Staff Spec", $y)}' />)</td><td><input type='number' name='report_administrative_staff[$y]' value='{$this->getBlobData("Administrative Staff", $y)}' /></td></tr>
                                <tr><td>Other<br />(Specify: <input type='text' name='report_other_spec[$y]' value='{$this->getBlobData("Other Spec", $y)}' />)</td><td><input type='number' name='report_other[$y]' value='{$this->getBlobData("Other", $y)}' /></td></tr>
                            </table>
                            
                            <h3>".Inflect::pluralize($config->getValue('productsTerm'))."</h3>
                            <table class='wikitable' frame='box' rules='all'>
                                <tr><th>Type</th><th>Projected #</th></tr>";
            foreach($structure['categories'] as $cat => $category){
                $types = $category['types'];
                $catMd5 = md5($cat);
                $this->html .= "<tr><th colspan='2'>{$cat}</th></tr>";
                foreach($types as $type => $data){
                    if($type == "Misc"){
                        continue;
                    }
                    $md5 = md5($type);
                    $this->html .= "<tr><td>{$type}</td><td><input type='number' name='report_{$md5}[$y]' value='{$this->getBlobData("$type", $y)}' /></td></tr>";
                }
                $this->html .= "<tr><td>Other<br />(Specify: <input type='text' name='report_other_{$catMd5}_spec[$y]' value='{$this->getBlobData("Other {$cat} Spec", $y)}' />)</td><td><input type='number' name='report_other_{$catMd5}[$y]' value='{$this->getBlobData("Other {$cat}", $y)}' /></td></tr>";
            }
            $this->html .= "</table>";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        $structure = Product::structure();
        if(isset($_POST['report_undergrad'])){
            foreach($_POST['report_undergrad'] as $year => $q){
                $this->saveBlobData("Undergrad", $year, $q);
            }
        }
        if(isset($_POST['report_msc'])){
            foreach($_POST['report_msc'] as $year => $q){
                $this->saveBlobData("MSc", $year, $q);
            }
        }
        if(isset($_POST['report_phd'])){
            foreach($_POST['report_phd'] as $year => $q){
                $this->saveBlobData("PhD", $year, $q);
            }
        }
        if(isset($_POST['report_pdf'])){
            foreach($_POST['report_pdf'] as $year => $q){
                $this->saveBlobData("PDF", $year, $q);
            }
        }
        if(isset($_POST['report_research_associate'])){
            foreach($_POST['report_research_associate'] as $year => $q){
                $this->saveBlobData("Research Associate", $year, $q);
            }
        }
        if(isset($_POST['report_technician'])){
            foreach($_POST['report_technician'] as $year => $q){
                $this->saveBlobData("Technician", $year, $q);
            }
        }
        if(isset($_POST['report_other_hqp_spec'])){
            foreach($_POST['report_other_hqp_spec'] as $year => $q){
                $this->saveBlobData("Other HQP Spec", $year, $q);
            }
        }
        if(isset($_POST['report_other_hqp'])){
            foreach($_POST['report_other_hqp'] as $year => $q){
                $this->saveBlobData("Other HQP", $year, $q);
            }
        }
        if(isset($_POST['report_administrative_staff_spec'])){
            foreach($_POST['report_administrative_staff_spec'] as $year => $q){
                $this->saveBlobData("Administrative Staff Spec", $year, $q);
            }
        }
        if(isset($_POST['report_administrative_staff'])){
            foreach($_POST['report_administrative_staff'] as $year => $q){
                $this->saveBlobData("Administrative Staff", $year, $q);
            }
        }
        if(isset($_POST['report_other_spec'])){
            foreach($_POST['report_other_spec'] as $year => $q){
                $this->saveBlobData("Other Spec", $year, $q);
            }
        }
        if(isset($_POST['report_other'])){
            foreach($_POST['report_other'] as $year => $q){
                $this->saveBlobData("Other", $year, $q);
            }
        }
        
        foreach($structure['categories'] as $cat => $category){
                $types = $category['types'];
                $catMd5 = md5($cat);
                $this->html .= "<tr><th colspan='2'>{$cat}</th></tr>";
                foreach($types as $type => $data){
                    if($type == "Misc"){
                        continue;
                    }
                    $md5 = md5($type);
                    if(isset($_POST["report_{$md5}"])){
                        foreach($_POST["report_{$md5}"] as $year => $q){
                            $this->saveBlobData($type, $year, $q);
                        }
                    }
                }
                if(isset($_POST["report_other_{$catMd5}_spec"])){
                    foreach($_POST["report_other_{$catMd5}_spec"] as $year => $q){
                        $this->saveBlobData("Other {$cat} Spec", $year, $q);
                    }
                }
                if(isset($_POST["report_other_{$catMd5}"])){
                    foreach($_POST["report_other_{$catMd5}"] as $year => $q){
                        $this->saveBlobData("Other {$cat}", $year, $q);
                    }
                }
            }
        Messages::addSuccess("'Projections' updated successfully.");
        redirect("{$this->project->getUrl()}?tab=projections");
    }
    
}
?>

<?php
    
    require_once('commandLine.inc');
    
    $wgUser = User::newFromId(1);
    
    $files = array_diff(scandir("proposals"), array('..', '.'));
    
    foreach($files as $file){
        $contents = file_get_contents("proposals/".$file);
        $project = Project::newFromName(str_replace(".zip", "", $file));
        if($project != null){
            $leader = array_values($project->getLeaders());
            if(isset($leader[0])){
                $leader = $leader[0];
                $sto = new ReportStorage($leader);
                $data = "";
                $html = "";
                $sto->store_report($data, $html, $contents, 0, 0, 'RPTP_PROJECT_PROPOSAL_ZIP', 2015);
                
                $ind = new ReportIndex($leader);
                $rid = $sto->metadata('report_id');
                $ind->insert_report($rid, $project);
            }
        }
    }

?>

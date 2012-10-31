<?php

class ReviewReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		$project = $this->getReport()->project;
		$projectGet = "";
		if($project != null){
		    $projectGet = "&project={$project->getName()}";
		}
		$year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
		$html = "<script type='text/javascript'>
		    function hideProgress(){
		        $('#loading').css('display', 'none');
		    }
		    
		    function alertsize(pixels){
		        $('#reportMain > div').stop();
                $('#previewFrame').height(pixels);
                $('#previewFrame').css('max-height', pixels);
            }
		</script>
		<span id='loading' style='position:absolute;top:50px;left:20px;'><img src='../skins/Throbber.gif' />&nbsp;Loading...</span><iframe id='previewFrame' frameborder='0' style='position:relative;left:0;width:100%;height:100%;border:0;border-width:0;' src='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$year}&generatePDF&preview&dpi=100'></iframe>";
		$wgOut->addHTML($this->processCData($html));
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

<?php

class ReviewReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		$project = $this->getReport()->project;
		$projectGet = "";
		$deptGet = "";
		if($project != null){
		    $projectGet = "&project={$project->getName()}";
		}
        if(isset($_GET['dept'])){
            $deptGet = "&dept={$_GET['dept']}";
        }
		$html = "<script type='text/javascript'>
		    function hideProgress(){
		        $('#loading').css('display', 'none');
		    }
		    
		    function alertsize(pixels){
		        $('#reportMain > div').stop();
                $('#previewFrame').height(pixels);
                $('#previewFrame').css('max-height', pixels);
                $('#reportMain > div').height(pixels);
            }
		</script>
		<span id='loading' style='float:left;'><img src='../skins/Throbber.gif' />&nbsp;Loading...</span><iframe id='previewFrame' frameborder='0' style='position:relative;left:0;width:100%;height:100%;border:0;border-width:0;' src='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$deptGet}&generatePDF&preview&dpi=96'></iframe>";
		$wgOut->addHTML($this->processCData($html));
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

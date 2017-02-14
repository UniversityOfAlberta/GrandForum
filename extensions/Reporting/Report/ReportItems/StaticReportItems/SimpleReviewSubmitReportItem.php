<?php

class SimpleReviewSubmitReportItem extends ReviewSubmitReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgImpersonating, $config;
		$reportname = $this->getReport()->name;
		$emails = $this->getAttr('emails', '');
		$text = $this->getAttr('text', 'By generating a PDF your application is automatically submitted');
		$person = Person::newFromId($wgUser->getId());
		$projectGet = "";
		if($this->getReport()->project != null){
		    if($this->getReport()->project instanceof Project){
		        if($this->getReport()->project->getName() == ""){
		            $projectGet = "&project={$this->getReport()->project->getId()}";
		        }
                else{
                    $projectGet = "&project={$this->getReport()->project->getName()}";
                }
            }
            else if($this->getReport()->project instanceof Theme){
                $projectGet = "&project={$this->getReport()->project->getAcronym()}";
            }
		}
		$year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
		if(!$wgImpersonating || checkSupervisesImpersonee()){
		    $pdfFiles = $this->getAttr('pdfFiles', '');
		    if($pdfFiles != ''){
		        $pdfFiles = "&pdfFiles=$pdfFiles";
		    }
		    $wgOut->addHTML("<script type='text/javascript'>
		        $(document).ready(function(){
		            $('#generateButton').click(function(){
		                $('#generateButton').prop('disabled', true);
		                $('#generate_success').html('');
                        $('#generate_success').css('display', 'none');
                        $('#generate_error').html('');
                        $('#generate_error').css('display', 'none');
                        $('#generate_throbber').css('display', 'inline-block');
		                $.ajax({
		                        url : '$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$year}&generatePDF{$pdfFiles}&emails={$emails}', 
		                        success : function(data){
		                                        //var data = jQuery.parseJSON(response);
		                                        for(index in data){
		                                            val = data[index];
		                                            if(typeof val.tok != 'undefined'){
		                                                index = index.replace('/', '');
		                                                var tok = val.tok;
		                                                var time = val.time;
		                                                var len = val.len;
		                                                var name = val.name;
		                                                
		                                                $('#ex_token_' + index).html(tok);
                                                        $('#ex_time_' + index).html(time);
                                                        $('#generate_button_' + index).attr('value', tok);
                                                        $('#download_button_' + index).removeAttr('disabled');
                                                        
                                                        $('#generate_success').html('PDF Generated/Submitted Successfully.');
                                                        $('#generate_success').css('display', 'block');
                                                        $('#download_button_' + index).attr('name', tok);
                                                        $('#download_button_' + index).text(name + ' PDF');
                                                    }
                                                    else{
                                                        $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                                        $('#generate_error').css('display', 'block');
                                                    }
                                                }
		                                        $('#generate_throbber').css('display', 'none');
		                                        $('#generateButton').removeAttr('disabled');
		                                  },
		                        error : function(response){
                                              // Error
                                              $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                              $('#generate_error').css('display', 'block');
		                                      $('#generateButton').removeAttr('disabled');
		                                      $('#generate_throbber').css('display', 'none');
		                                  }
		                });
		            });
		        });
		    </script>");
		}
		$wgOut->addHTML("<script type='text/javascript'>
		    function clickButton(button){
                $('#pdf_download_frame').attr('src', '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + button.name);
            }
		</script>");
		$disabled = "";
		if($wgImpersonating && !checkSupervisesImpersonee()){
		    $disabled = "disabled='true'";
		}
		$showWarning = (strtolower($this->getAttr('showWarning', 'true')) == 'true');
		if(!$this->getReport()->isComplete() && $showWarning){
		    $wgOut->addHTML("<div class='warning'>The report is not 100% complete.  Double check to make sure you did not miss any fields.</div>");
		}
		$wgOut->addHTML("<h3>Generate a new PDF</h3>");
		$wgOut->addHTML("<p><button id='generateButton' $disabled>Submit</button><img id='generate_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' /><br />
		                    {$text}<br />
		                    <div style='display:none;' class='error' id='generate_error'></div><div style='display:none;' class='success' id='generate_success'></div></p>");

		$wgOut->addHTML("<h3>Download the PDF</h3>");
		
		$gmt_date = date('P');
		$temp_html =<<<EOF
		<p><table cellspacing='0'>
EOF;

		$wgOut->addHTML($temp_html);
		$pdfcount = 1;
		$wgOut->addHTML("<iframe id='pdf_download_frame' style='position:absolute;top:-1000px;left:-1000px;width:1px;height:1px;'></iframe>");
		$pdfFiles = $this->getAttr('pdfFiles', '');
		if($pdfFiles != ''){
		    $pdfFiles = explode(',', $pdfFiles);
		}
		else{
		    $pdfFiles = $this->getReport()->pdfFiles;
		}
        foreach($pdfFiles as $file){
            $tok = false;
            $tst = '';
            $sto = new ReportStorage($person);
            $project = null;
            if($this->getReport()->project instanceof Project){
                $project = $this->getReport()->project;
            }
            else if($this->getReport()->project instanceof Theme){
                $project = Theme::newFromId($this->projectId);
            }
            if($file != $this->getReport()->xmlName){
                $report = new DummyReport($file, $person, $project);
            }
            else{
                $report = $this->getReport();
            }
        	$check = $report->getPDF();
        	if (count($check) > 0) {
        		$tok = $check[0]['token']; 	
        		$tst = $check[0]['timestamp'];
        	}
        	
        	// Present some data on available reports.
        	$style1 = "";
        	if ($tok === false) {
        		// No reports available.
        		$style1 = "disabled='disabled'";
        	}

		    if($tok === false){
		    	$show_pdf = "No PDF has been generated yet";
		    }else{
		    	$show_pdf = "<br />".$tst;
		    }

            $file = str_replace("/", "", $file);
		    $subm_table_row =<<<EOF
		    <tr>
            <td>
            	<button id='download_button_{$file}' name='{$tok}' onClick='clickButton(this)' {$style1}>{$report->name} PDF</button>
            </td>
EOF;

            $subm_table_row .= "</tr>";

            $wgOut->addHTML($subm_table_row);
            $pdfcount++;
        }
        
        $wgOut->addHTML("</table></p>");
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

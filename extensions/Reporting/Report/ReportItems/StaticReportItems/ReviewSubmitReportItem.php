<?php

class ReviewSubmitReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgImpersonating, $config;
		$reportname = $this->getReport()->name;
		$person = Person::newFromId($wgUser->getId());
		$projectGet = "";
		$deptGet = "";
		if($this->getReport()->project != null){
		    $projectGet = "&project={$this->getReport()->project->getName()}";
		}
        if(isset($_GET['dept'])){
            $deptGet = "&dept={$_GET['dept']}";
        }
		if(!$wgImpersonating || checkSupervisesImpersonee()){
		    $wgOut->addHTML("<script type='text/javascript'>
		        $(document).ready(function(){
		            var errors = $('#reportBody .inlineError').length + $('#reportBody .inlineWarning').length;
		            
		            if(errors > 0){
		                $('#reportBody').append(\"<div id='reportErrors' style='display:none;' title='Report Errors'>There were warnings and/or errors in your Report. Do you still want to submit it?</div>\");
		            }
		            
		            $('#submitCheck').change(function(){
		                if($('#submitCheck').is(':checked')){
		                    $('#submitButton').removeAttr('disabled');
		                }
		                else{
		                    $('#submitButton').prop('disabled', true);
		                }
		            });
		        
		            $('#generateButton').click(function(){
		                $('#submitButton').prop('disabled', true);
		                $('#submitCheck').prop('disabled', true);
		                $('#generateButton').prop('disabled', true);
		                $('#generate_success').html('');
                        $('#generate_success').css('display', 'none');
                        $('#generate_error').html('');
                        $('#generate_error').css('display', 'none');
                        $('#generate_throbber').css('display', 'inline-block');
		                $.ajax({
		                        url : '$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$deptGet}&generatePDF', 
		                        success : function(data){
                                        //var data = jQuery.parseJSON(response);
                                        for(index in data){
                                            var val = data[index];
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
                                                $('#report_submit_div').show();
                                                
                                                $('#generate_success').html('PDF Generated Successfully.');
                                                $('#generate_success').css('display', 'block');
                                                $('#download_button_' + index).attr('name', tok);
                                                $('#download_button_' + index).text(name + ' PDF');
                                            }
                                            else{
                                                $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                                $('#generate_error').css('display', 'block');
                                            }
                                        }
                                        $('#generate_throbber').css('display', 'none');
                                        $('#generateButton').removeAttr('disabled');
                                        $('#submitCheck').removeAttr('checked');
                                        $('#submitCheck').removeAttr('disabled');
                                        updateEvalReport();
                                  },
		                        error : function(response){
                                      // Error
                                      $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                      $('#generate_error').css('display', 'block');
                                      $('#generateButton').removeAttr('disabled');
                                      $('#generate_throbber').css('display', 'none');
                                      //$('#submitCheck').removeAttr('disabled');
                                }
		                });
		            });
		            
		            $('#submitButton').click(function(){
		                var that = this;
		                if(errors > 0){
		                    $('#reportErrors').dialog('destroy');
		                    $('#reportErrors').dialog({resizable: false,
		                                               modal: true,
		                                               buttons: {
                                                            'Submit': function() {
                                                                submitReport(that);
                                                                $( this ).dialog('close');
                                                            },
                                                            'Cancel': function() {
                                                                $( this ).dialog('close');
                                                            }
                                                       }});
                            $('.ui-dialog-buttonset button').removeClass('ui-widget').removeClass('ui-state-default').removeClass('ui-corner-all').removeClass('ui-button-text-only').removeClass('ui-state-hover');
		                }
		                else{
		                    submitReport(that);
		                }
		            });
		            
		            function submitReport(button){
		                $('#submitButton').prop('disabled', true);
		                $('#submit_throbber').css('display', 'inline-block');
		                $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$deptGet}&submitReport&tok=' + $(button).val() ,function(data){
		                    updateEvalReport();
		                    $('#submitButton').removeAttr('disabled');
		                    $('#submit_throbber').css('display', 'none');
		                });
		            }
		            
		            function updateEvalReport(){
		                $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$deptGet}&getPDF' ,function(data){
	                        if(data.length > 0){
	                            var val = data[0];
	                            if(typeof val != 'undefined'){
                                    var tok = val.token;
                                    var time = val.timestamp;
                                    var len = val.len;
                                    var name = val.name;
                                    var status = val.status;
                                    
                                    $('#download_submitted').attr('name', tok);
                                    $('#download_submitted').text(name + ' PDF');
                                    $('.submit_status_cell').html('<b>' + status + '</b>');
                                    $('#ex_time_submitted').html(time);
                                    $('#download_submitted').removeAttr('disabled');
                                }
	                        }
	                    });
		            }
		        });
		    </script>");
		}
		$wgOut->addHTML("<script type='text/javascript'>
		    function clickButton(button){
                $('#pdf_download_frame').attr('src',  '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + button.name + '&download');
            }
		</script>");
		$disabled = "";
		if($wgImpersonating && !checkSupervisesImpersonee()){
		    $disabled = "disabled='true'";
		}
		$wgOut->addHTML("<h3>1. Generate a new Report PDF for submission</h3>");
		$wgOut->addHTML("<p>Generate a Report PDF with the data submitted: <button id='generateButton' $disabled>Generate Report PDF</button><img id='generate_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' /><br />
		                    <small>Depending on the size of the report, this could take several moments.</small>
		                    <div style='display:none;' class='error' id='generate_error'></div><div style='display:none;' class='success' id='generate_success'></div></p>");

		$wgOut->addHTML("<h3>2. Review the most recently generated PDF</h3>");
		
		$gmt_date = date('P');
		$temp_html =<<<EOF
		<p><table cellpadding='5' rules='all' frame='box'>
        <tr>
        	<th align='left'>Generated (GMT {$gmt_date})</th><th>Download</th>
        </tr>
EOF;

		$wgOut->addHTML($temp_html);
		$pdfcount = 1;
		$wgOut->addHTML("<iframe id='pdf_download_frame' style='position:absolute;top:-1000px;left:-1000px;width:1px;height:1px;'></iframe>");
        foreach($this->getReport()->pdfFiles as $file){
            $tok = false;
            $tst = '';
            $sub = 0;
            $sto = new ReportStorage($person);
            $project = Project::newFromId($this->projectId);
            if($file != $this->getReport()->xmlName){
                $report = new DummyReport($file, $person, $project);
            }
            else{
                $report = $this->getReport();
            }
        	$check = $report->getLatestPDF();
        	if (count($check) > 0) {
        		$tok = $check[0]['token']; 	
        		$tst = $check[0]['timestamp'];
        		$sub = $check[0]['submitted'];
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
		    	$show_pdf = $tst;
		    }

            $file = str_replace("/", "", $file);
		    $subm_table_row =<<<EOF
		    <tr>
		    <td>
		    	<span id='ex_time_{$file}'>{$show_pdf}</span></td>
            <td>
            	<button id='download_button_{$file}' name='{$tok}' onClick='clickButton(this)' {$style1}>{$report->name} PDF</button>
            </td></tr>
EOF;

            $wgOut->addHTML($subm_table_row);
            $pdfcount++;
        }
        
        $wgOut->addHTML("</table></p>");
		$wgOut->addHTML("<h3>3. Submit the $reportname PDF</h3>");
		$wgOut->addHTML("<p>You can submit your most recently generated $reportname PDF for evaluation. Make sure you review it before submitting.<br />Please note:</p>
         <ul>
         <li>If you need to make a correction to your $reportname PDF that is already submitted, you can generate and submit again.</li>
         <li>If the status of the report is \"Not-Submitted\", a PDF document will be compiled with the current report data and forwarded for evaluation. 
         <li>If, on the other hand, the status is \"Submitted\", the last submitted PDF will be used for evaluation, even if subsequent edits have been made and newer PDF documents have been regenerated.
         <li>If you encounter any issues, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a></li>
         </ul></p>\n
         <div id='report_submit_div' style=''>
            <p>
            <table border='0' style='margin-top: 20px;' cellpadding='3'>
            <tr><td valign='top'>
            <input {$style1} id='submitCheck' type='checkbox' /> - I have reviewed my \"$reportname PDF\"
            </td></tr>
            <tr><td>
            <button id='submitButton' value='{$tok}' disabled>Submit $reportname PDF</button><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />
            </td></tr>
            </table></p>
         </div>");
         
        $wgOut->addHTML("<h3>4. Download the PDF which will be used for evaluation</h3>");
        $gmt_date = date('P');
		$temp_html =<<<EOF
		<p><table cellpadding='5' rules='all' frame='box'>
        <tr>
        	<th align='left'>Generated (GMT {$gmt_date})</th><th>Download</th><th>Status</th>
        </tr>
EOF;

		$wgOut->addHTML($temp_html);
		$pdfcount = 1;
        foreach($this->getReport()->pdfFiles as $file){
            $tok = false;
            $tst = '';
            $sub = 0;
            $sto = new ReportStorage($person);
            $project = Project::newFromId($this->projectId);
            if($file != $this->getReport()->xmlName){
                $report = new DummyReport($file, $person, $project);
            }
            else{
                $report = $this->getReport();
            }
        	$check = $report->getPDF();
        	$subm = "Not Generated/Not Submitted";
        	if (count($check) > 0) {
        		$tok = $check[0]['token']; 	
        		$tst = $check[0]['timestamp'];
        		$sub = $check[0]['submitted'];
        		$subm = $check[0]['status'];
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
		    	$show_pdf = $tst;
		    }

		    $subm_table_row =<<<EOF
		    <tr>
		    <td>
		    	<span id='ex_time_submitted'>{$show_pdf}</span></td>
            <td>
            	<button id='download_submitted' name='{$tok}' onClick='clickButton(this)' {$style1}>{$report->name} PDF</button>
            </td>
EOF;
			if($pdfcount == 1 ){
				$subm_table_row .=<<<EOF
            		<td align='center' class='submit_status_cell'>
            			<b>$subm</b>
            		</td>
EOF;
        	}
        	else{
        		$subm_table_row .= "<td></td>";
        	}

            $subm_table_row .= "</tr>";

            $wgOut->addHTML($subm_table_row);
            $pdfcount++;
            break; // Only the first PDF gets submitted
        }
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

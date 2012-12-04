<?php

class ReviewSubmitReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgImpersonating;
		$person = Person::newFromId($wgUser->getId());
		$projectGet = "";
		if($this->getReport()->project != null){
		    $projectGet = "&project={$this->getReport()->project->getName()}";
		}
		$year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
		if(!$wgImpersonating || checkSupervisesImpersonee()){
		    $wgOut->addHTML("<script type='text/javascript'>
		        $(document).ready(function(){
		            var errors = $('#reportBody .inlineError').length + $('#reportBody .inlineWarning').length;
		            
		            if(errors > 0){
		                $('#reportBody').append(\"<div id='reportErrors' style='display:none;' title='Report Errors'>There were warnings and/or errors in your Report. Do you still want to submit it?</div>\");
		            }
		            
		            $('#submitCheck').change(function(){
		                if($('#submitCheck').attr('checked') == 'checked'){
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
		                        url : '$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$year}&generatePDF', 
		                        success : function(data){
		                                        //var data = jQuery.parseJSON(response);
		                                        for(index in data){
		                                            val = data[index];
		                                            if(typeof val.tok != 'undefined'){
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
                                                        $('.submit_status_cell').css('background', 'red');
		                                                $('.submit_status_cell').html('<b>No</b>');
                                                    }
                                                    else{
                                                        $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:support@forum.grand-nce.ca\">support@forum.grand-nce.ca</a>');
                                                        $('#generate_error').css('display', 'block');
                                                    }
                                                }
		                                        $('#generate_throbber').css('display', 'none');
		                                        $('#generateButton').removeAttr('disabled');
		                                        $('#submitCheck').removeAttr('checked');
		                                        $('#submitCheck').removeAttr('disabled');
		                                  },
		                        error : function(response){
                                              // Error
                                              $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:support@forum.grand-nce.ca\">support@forum.grand-nce.ca</a>');
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
		                $.get('$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}{$year}&submitReport&tok=' + $(button).val() ,function(data){
		                    $('.submit_status_cell').css('background', '#008800');
		                    $('.submit_status_cell').html('<b>Yes</b>');
		                    $('#submitButton').removeAttr('disabled');
		                    $('#submit_throbber').css('display', 'none');
		                });
		            }
		        });
		    </script>");
		}
		$wgOut->addHTML("<script type='text/javascript'>
		    function clickButton(button){
                $('#pdf_download_frame').attr('src',  '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + button.name);
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

		$wgOut->addHTML("<h3>2. Download the Report PDF submitted for reviewing</h3>");
		
		$gmt_date = date('P');
		$temp_html =<<<EOF
		<p><table cellspacing='5'>
        <tr>
        	<th align='left'><span style="font-size:8pt; font-family:monospace;">Identifier</span><br />Generated (GMT {$gmt_date})</th>
        	<th>Download</th><th>Submitted?</th>
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
            $report = new DummyReport($file, $person, $project);
        	$check = $report->getPDF();
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
        	  
        	if ($sub == 1) {
			    $subm = "Yes";
			    $subm_style = "background-color:#008800;";
		    }
		    else {
			    $subm = "No";
			    $subm_style = "background-color:red;";
		    }

		    if($tok === false){
		    	$show_pdf = "No PDF has been generated yet";
		    }else{
		    	$show_pdf = "<br />".$tst;
		    }


		    $subm_table_row =<<<EOF
		    <tr>
		    <td>
		    	<span style="font-size:8pt; font-family: monospace;" id='ex_token_{$file}'>{$tok}</span>
		    	<span id='ex_time_{$file}'>{$show_pdf}</span></td>
            <td>
            	<button id='download_button_{$file}' name='{$tok}' onClick='clickButton(this)' {$style1}>{$report->name} PDF</button>
            </td>
EOF;
			if($pdfcount == 1 ){
				$subm_table_row .=<<<EOF
            		<td align='center' class='submit_status_cell' style='$subm_style'>
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
        }

        $reportname = $this->getReport()->name;
        $wgOut->addHTML("</table></p>");
		$wgOut->addHTML("<h3>3. Submit the $reportname PDF</h3>");
		$wgOut->addHTML("<p>You can submit your $reportname PDF for evaluation. Make sure you review it before submitting.<br />Please note:</p>
         <ul>
         <li>If you need to make a correction to your $reportname PDF that is already submitted, you can generate and submit again</li>
        <li>The most recently generated $reportname PDF is used for evaluation</li>
        <li>If you encounter any issues, please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a></li>
         </ul></p>\n
         <div id='report_submit_div' style=''>
            <p>
            <table border='0' style='margin-top: 20px;' cellpadding='3'>
            <tr><td valign='top'>
            <input {$style1} id='submitCheck' type='checkbox' /> - I have reviewed my \"$reportname PDF\"
            </td></tr>
            <tr><td>
            <button id='submitButton' value='{$tok}' disabled>Submit Final Report PDF</button><img id='submit_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' />
            </td></tr>
            </table></p>
         </div>");
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

<?php

class NotificationReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgImpersonating;
		$creator = Person::newFromId($wgUser->getId());
	    $user = Person::newFromId($this->personId);
		$title = $this->getAttr("title", "Send Notification");
		$width = $this->getAttr("width", 'auto');
		$url = $wgServer.$wgScriptPath."/index.php/".$this->getAttr("url", "Special:Report");
		$message = $this->getAttr("message", "{$creator->getNameForForms()} has requested that you complete your report.");
		if(isset($_GET['notify']) && $_GET['notify'] == $this->personId){
		    $this->processNotification();
		}
		if(!$wgImpersonating){
		    $button = "<a class='button' style='cursor:pointer;width:$width;' id='notification_{$this->id}_{$this->personId}'>$title</a>";
		}
		else{
		    $button = "<a class='disabledButton' style='width:$width;' id='notification_{$this->id}_{$this->personId}'>$title</a>";
		}
		$item = "<div style='white-space:nowrap;'>{$button}<img id='notification_{$this->id}_{$this->personId}_throbber' style='display:none;vertical-align:-20%;' src='../skins/Throbber.gif' /></div><div style='display:none;' id='dialog_{$this->id}_{$this->personId}' title='Notification Message'><p><b>Message:</b><br /><textarea id='message_{$this->id}_{$this->personId}' style='height:100px;width:455px;' name='message'>$message</textarea><br /><b>Also Send Email:</b> <input type='checkbox' id='email_{$this->id}_{$this->personId}' value='Yes' /></p>
</div>";
		$item = $this->processCData($item);
		$wgOut->addHTML($item);
		$projectGet = "";
		if($this->getReport()->project != null){
		    $projectGet = "&project={$this->getReport()->project->getName()}";
		}
		if(!$wgImpersonating){
		    $wgOut->addHTML("<script type='text/javascript'>
		
		        $('#notification_{$this->id}_{$this->personId}').click(function(){
		            $('#notification_{$this->id}_{$this->personId}').prop('disabled', true);
		            $('#dialog_{$this->id}_{$this->personId}').dialog('destroy');
                    $('#dialog_{$this->id}_{$this->personId}').dialog({
			            resizable: false,
			            draggable: false,
			            modal: true,
			            minWidth:500, 
			            buttons: {
				            'Send': function(){
				                $(this).dialog('close');
				                $('#notification_{$this->id}_{$this->personId}_throbber').css('display', 'inline-block');
				                var data = 'message=' + encodeURIComponent($('#message_{$this->id}_{$this->personId}').val());
				                data += '&email=' + encodeURIComponent($('#email_{$this->id}_{$this->personId}').is(':checked'));
				                $.ajax({
                                    type: 'POST',
                                    url: '$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}{$projectGet}&notify={$this->personId}',
                                    data: data,
                                    success: function (data) {
                                        $('#notification_{$this->id}_{$this->personId}').removeAttr('disabled');
		                                $('#notification_{$this->id}_{$this->personId}_throbber').css('display', 'none');
                                    }
                                });
				            },
				            'Cancel': function(){
					            $(this).dialog('close');
					            $('#resetBackup').click();
					            $('#notification_{$this->id}_{$this->personId}').removeAttr('disabled');
		                        $('#notification_{$this->id}_{$this->personId}_throbber').css('display', 'none');
				            }
			            }
		            });
		            $('.ui-dialog-buttonset button').removeClass('ui-widget').removeClass('ui-state-default').removeClass('ui-corner-all').removeClass('ui-button-text-only').removeClass('ui-state-hover');
		        });
		    </script>");
		}
	}
	
	function processNotification(){
	    global $wgServer, $wgScriptPath, $wgUser;
	    $creator = Person::newFromId($wgUser->getId());
	    $user = Person::newFromId($this->personId);
		$message = $this->getAttr("message", "{$creator->getNameForForms()} has requested that you complete your report.");
		if(isset($_POST['message'])){
		    $message = $_POST['message'];
		}
		$url = $wgServer.$wgScriptPath."/index.php/".$this->getAttr("url", "Special:Report");
		$email = ($_POST['email'] == "true") ? true : false;
		Notification::addNotification($creator, $user, "Report Notification", $message, $url, $email);
		close();
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>

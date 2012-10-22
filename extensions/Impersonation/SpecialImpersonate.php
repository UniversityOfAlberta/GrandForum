<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Impersonate'] = 'Impersonate'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Impersonate'] = $dir . 'SpecialImpersonate.i18n.php';
$wgSpecialPageGroups['Impersonate'] = 'grand-tools';

function runImpersonate($par) {
  Impersonate::run($par);
}

class Impersonate extends UserSearch{

	function Impersonate() {
	    global $wgOut, $wgServer, $wgScriptPath;
		wfLoadExtensionMessages('Impersonate');
	    SpecialPage::SpecialPage("Impersonate", MANAGER.'+', true, 'runImpersonate');
	    $wgOut->addScript("<script type='text/javascript'>
	        $(document).ready(function(){
	            $('#button').val('Impersonate');
	            $('#pageDescription').html('Select a user from the list below, and then click the \'Impersonate\' button.  You can filter out the selection box by searching a name, user role, or project below.');
	            $('#mainForm').attr('method', 'get');
	            $('#mainForm').attr('action', '$wgServer$wgScriptPath/index.php');
	            $('#button').click(function(){
                    var page = $('select option:selected').attr('name').split(':')[1];
                    if(typeof page != 'undefined'){
                        document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                    }
                });
                $('#search').keyup(function(event) {
                    if(event.keyCode == 13){
                        // Enter key was pressed
                        var page = $('select option:selected').attr('name').split(':')[1];
                        if(typeof page != 'undefined'){
                            document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                        }
                    }
                });
	        });
	    </script>");
	}
}

?>

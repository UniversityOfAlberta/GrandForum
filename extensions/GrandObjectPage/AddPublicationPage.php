<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['AddArtifactPage'] = 'AddArtifactPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddArtifactPage'] = $dir . 'AddArtifactPage.i18n.php';
$wgSpecialPageGroups['AddArtifactPage'] = 'grand-tools';

$wgSpecialPages['AddPublicationPage'] = 'AddPublicationPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPublicationPage'] = $dir . 'AddPublicationPage.i18n.php';
$wgSpecialPageGroups['AddPublicationPage'] = 'grand-tools';

$wgSpecialPages['AddActivityPage'] = 'AddActivityPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddActivityPage'] = $dir . 'AddActivityPage.i18n.php';
$wgSpecialPageGroups['AddActivityPage'] = 'grand-tools';

$wgSpecialPages['AddPressPage'] = 'AddPressPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPressPage'] = $dir . 'AddPressPage.i18n.php';
$wgSpecialPageGroups['AddPressPage'] = 'grand-tools';

$wgSpecialPages['AddAwardPage'] = 'AddAwardPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddAwardPage'] = $dir . 'AddAwardPage.i18n.php';
$wgSpecialPageGroups['AddAwardPage'] = 'grand-tools';

$wgSpecialPages['AddPresentationPage'] = 'AddPresentationPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPresentationPage'] = $dir . 'AddPresentationPage.i18n.php';
$wgSpecialPageGroups['AddPresentationPage'] = 'grand-tools';

$wgHooks['UnknownAction'][] = 'pubSearch';

function runAddPublicationPage($par){
    AddPublicationPage::run($par);
}

function runAddArtifactPage($par){
    AddArtifactPage::run($par);
}

function runAddActivityPage($par){
    AddActivityPage::run($par);
}

function runAddPressPage($par){
    AddPressPage::run($par);
}

function runAddAwardPage($par){
    AddAwardPage::run($par);
}

function runAddPresentationPage($par){
    AddPresentationPage::run($par);
}

function pubSearch($action, $request){
    if($action == "pubSearch"){
        header("Content-type: text/json");
        echo Paper::search($_GET['phrase'], $_GET['category']);
        exit;
    }
    return true;
}

function generateScript($category){
    global $wgServer, $wgScriptPath;
    $script = "<script type='text/javascript'>
                var lastCall;
                
                function search(phrase){
                    if(lastCall != null){
                        lastCall.abort();
                    }
                    if(phrase != ''){
                        phrase = phrase.replace(/'/g, ' ');
                        lastCall = $.get('$wgServer$wgScriptPath/index.php?action=pubSearch&phrase=' + phrase + '&category={$category}', function(data) {
                            $('#suggestions').html('');
                            var html = '';
                            if(data.length > 0){
                                $('#sug').css('display', 'inline');
                            }
                            else{
                                $('#sug').css('display', 'none');
                            }
                            html += '<table id=\'bigpubtable\' class=\'wikitable sortable\' width=\'100%\' cellspacing=\'1\' cellpadding=\'2\' rules=\'all\' frame=\'box\'><tr><th>Date</th><th>Project</th><th>Product</th></tr>';
                            $.each(data, function(index, value){
                                html += '<tr><td style=\'white-space:nowrap;\'>'+value['date']+'</td><td>'+value['projects']+ '</td><td><a href=\"$wgServer$wgScriptPath/index.php/{$category}:' + value['id'] + '?edit\">' + value['title'] + '</a></td></tr>';
                            });
                            html += '</table>';
                            $('#suggestions').html(html);
                            ts_makeSortable(document.getElementById('bigpubtable'));
                        });
                    }
                    else{
                        $('#sug').css('display', 'none');
                    }
                }
                
                function submitOnEnter(e){
                    var key;
                    if(window.event){
                        key = window.event.keyCode;     //IE
                    }
                    else{
                        key = e.which;     //firefox
                    }
                    if(key == 13){
                        changeLocation();
                    }
                }
                
                function changeLocation(){
                    var page = escape($('#title').attr('value').replace(/\‘/g, '\'').replace(/\’/g, '\''));
                    document.location = '$wgServer$wgScriptPath/index.php/{$category}:' + page + '?create';
                }
                
                $(document).ready(function(){
                    search($('#title').attr('value'));
                    $('#title').attr('autocomplete', 'off');
                });
           </script>";
    return $script;
}

class AddActivityPage extends SpecialPage{
    function AddActivityPage() {
		wfLoadExtensionMessages('AddActivityPage');
		SpecialPage::SpecialPage("AddActivityPage", HQP.'+', true, 'runAddActivityPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Activity"));
		$wgOut->addHTML("Enter in the title of the activity in the text field below.  If there is an already existing activity with the same or similar name, it will be listed below the text field.  If you see the activity in the list, then you can click on the title to edit its information, otherwise you can choose to create the activity with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be an activity with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

class AddArtifactPage extends SpecialPage{
    function AddArtifactPage() {
		wfLoadExtensionMessages('AddArtifactPage');
		SpecialPage::SpecialPage("AddArtifactPage", HQP.'+', true, 'runAddArtifactPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Artifact"));
		$wgOut->addHTML("Enter in the title of the artifact in the text field below.  If there is an already existing artifact with the same or similar name, it will be listed below the text field.  If you see the artifact in the list, then you can click on the title to edit its information, otherwise you can choose to create the artifact with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be an artifact with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

class AddPublicationPage extends SpecialPage{

	function AddPublicationPage() {
		wfLoadExtensionMessages('AddPublicationPage');
		SpecialPage::SpecialPage("AddPublicationPage", HQP.'+', true, 'runAddPublicationPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Publication"));
		$wgOut->addHTML("Enter in the title of the publication in the text field below.  If there is an already existing publication with the same or similar title, it will be listed below the text field.  If you see the publication in the list, then you can click on the title to edit its information, otherwise you can choose to create the publication with the title you have entered by clicking the 'Create' button. You can also add a new publication using the <a href='$wgServer$wgScriptPath/index.php/Special:ImportBibTex'>Import BibTeX</a> page.<br /><br />You can review the complete list of publications in the forum and search by title, author and project (if applicable) at <a href='http://forum.grand-nce.ca/index.php/GRAND:Publications'>GRAND Publications</a>.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a publication with a similar title to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

class AddPressPage extends SpecialPage{

	function AddPressPage() {
		wfLoadExtensionMessages('AddPressPage');
		SpecialPage::SpecialPage("AddPressPage", HQP.'+', true, 'runAddPressPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Press"));
		$wgOut->addHTML("Enter in the title of press item in the text field below.  If there is an already existing press item with the same or similar title, it will be listed below the text field.  If you see the press item in the list, then you can click on the title to edit its information, otherwise you can choose to create the press item with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a press item with a similar title to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

class AddAwardPage extends SpecialPage{

	function AddAwardPage() {
		wfLoadExtensionMessages('AddAwardPage');
		SpecialPage::SpecialPage("AddAwardPage", HQP.'+', true, 'runAddAwardPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Award"));
		$wgOut->addHTML("Enter in the title of the award in the text field below.  If there is an already existing award with the same or similar name, it will be listed below the text field.  If you see the award in the list, then you can click on the title to edit its information, otherwise you can choose to create the award with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a award with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

class AddPresentationPage extends SpecialPage{

	function AddPresentationPage() {
		wfLoadExtensionMessages('AddPresentationPage');
		SpecialPage::SpecialPage("AddPresentationPage", HQP.'+', true, 'runAddPresentationPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Presentation"));
		$wgOut->addHTML("Enter in the title of the presentation in the text field below.  If there is an already existing presentation with the same or similar name, it will be listed below the text field.  If you see the presentation in the list, then you can click on the title to edit its information, otherwise you can choose to create the presentation with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a award with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
}

?>

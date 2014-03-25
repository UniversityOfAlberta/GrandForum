<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['AddArtifactPage'] = 'AddArtifactPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddArtifactPage'] = $dir . 'AddArtifactPage.i18n.php';
$wgSpecialPageGroups['AddArtifactPage'] = 'network-tools';

$wgSpecialPages['AddPublicationPage'] = 'AddPublicationPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPublicationPage'] = $dir . 'AddPublicationPage.i18n.php';
$wgSpecialPageGroups['AddPublicationPage'] = 'network-tools';

$wgSpecialPages['AddActivityPage'] = 'AddActivityPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddActivityPage'] = $dir . 'AddActivityPage.i18n.php';
$wgSpecialPageGroups['AddActivityPage'] = 'network-tools';

$wgSpecialPages['AddPressPage'] = 'AddPressPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPressPage'] = $dir . 'AddPressPage.i18n.php';
$wgSpecialPageGroups['AddPressPage'] = 'network-tools';

$wgSpecialPages['AddAwardPage'] = 'AddAwardPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddAwardPage'] = $dir . 'AddAwardPage.i18n.php';
$wgSpecialPageGroups['AddAwardPage'] = 'network-tools';

$wgSpecialPages['AddPresentationPage'] = 'AddPresentationPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddPresentationPage'] = $dir . 'AddPresentationPage.i18n.php';
$wgSpecialPageGroups['AddPresentationPage'] = 'network-tools';

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

function getMyPapers($cat){
	global $wgUser, $wgServer, $wgScriptPath;
    $me = Person::newFromId($wgUser->getId());

	$html  = "<div id='my_papers'>";
	$html .= "<table id='mypubtable' class='wikitable sortable' width='100%' cellspacing='1' cellpadding='5' rules='all' frame='box'><tr><th align='center' style='white-space:nowrap;'>Publication Date</th><th>Project</th><th>Product</th></tr>";

	$myPapers = $me->getPapersAuthored($cat, YEAR, YEAR+1);
	   	
   	foreach($myPapers as $paper){
   		$id = $paper->getId();
   		$date = $paper->getDate();
   		$projects = $paper->getProjects();
   		$project_names = array();
   		foreach ($projects as $p){
   			$project_names[] = $p->getName();
   		}
   		$category = $paper->getCategory();
   		$citation = $paper->getProperCitation();

   		$title = $paper->getTitle();
   		$html .= "<tr><td style='white-space:nowrap;' align='center'>";
   		$html .= $date;
   		$html .= "</td><td>";
   		$html .= implode(", ", $project_names);
   		//$html .= "</td><td><a href='$wgServer$wgScriptPath/index.php/{$category}:{$id}?edit'>{$title}</a></td></tr>";
   		$html .= "</td><td>{$citation}</td></tr>";
   		
   	}
   	if(count($myPapers) == 0){
   		$html .= "<tr><td align='center' colspan='3'>You have no products in this category</td></tr>";
   	}

	$html .= "</table>";
	$html .= "</div>";
    
    return $html;
}

function generateScript($category){
    global $wgServer, $wgScriptPath;
	
    $script = "<script type='text/javascript'>
    			$(function() {
			        $( '#addpub_accordion' ).accordion({ heightStyle: 'fill' });
			    });

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
                    if($('#title').val() == ''){
                        clearError();
                        addError('The {$category} must not have an empty title');
                        return;
                    }
                    var page = escape($('#title').val().replace(/\‘/g, '\'').replace(/\’/g, '\'').replace(/\“/g, '\"').replace(/\”/g, '\"'));
                    document.location = '$wgServer$wgScriptPath/index.php/{$category}:New?name=' + page + '&create';
                }
                
                $(document).ready(function(){
                    search($('#title').val());
                    $('#title').attr('autocomplete', 'off');
                });
           </script>
	    	<style type='text/css'>
    			div#add-edit_publications{
    				height: auto !important;
    			}
    			span.ui-icon-triangle-1-s, span.ui-icon-triangle-1-e {
    				left: -3px !important;
    			}
    		</style>";
    return $script;
}

class AddActivityPage extends SpecialPage{
    function AddActivityPage() {
		wfLoadExtensionMessages('AddActivityPage');
		SpecialPage::SpecialPage("AddActivityPage", HQP.'During+', true, 'runAddActivityPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }

	    $wgOut->addScript(generateScript("Activity"));
		
		$wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='myactivities'>My ".YEAR." Activities</h3>");
	    
	   	$myPapers_html = getMyPapers("Activity");
	   	$wgOut->addHTML($myPapers_html);

	   	$wgOut->addHTML("<h3 id='addactivity'>Add/Edit Activity</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");
		$wgOut->addHTML("Enter in the title of the activity in the text field below.  If there is an already existing activity with the same or similar name, it will be listed below the text field.  If you see the activity in the list, then you can click on the title to edit its information, otherwise you can choose to create the activity with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be an activity with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

class AddArtifactPage extends SpecialPage{
    function AddArtifactPage() {
		wfLoadExtensionMessages('AddArtifactPage');
		SpecialPage::SpecialPage("AddArtifactPage", HQP.'During+', true, 'runAddArtifactPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Artifact"));
		
	    $wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='my_artifacts'>My ".YEAR." Artifacts</h3>");
	    
	   	$myPapers_html = getMyPapers("Artifact");
	   	$wgOut->addHTML($myPapers_html);

	   	$wgOut->addHTML("<h3 id='add_artifacts'>Add/Edit Artifact</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");
		$wgOut->addHTML("Enter in the title of the artifact in the text field below.  If there is an already existing artifact with the same or similar name, it will be listed below the text field.  If you see the artifact in the list, then you can click on the title to edit its information, otherwise you can choose to create the artifact with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be an artifact with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

class AddPublicationPage extends SpecialPage{

	function AddPublicationPage() {
		wfLoadExtensionMessages('AddPublicationPage');
		SpecialPage::SpecialPage("AddPublicationPage", HQP.'During+', true, 'runAddPublicationPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Publication"));

	    $wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='mypublications'>My ".YEAR." Publications</h3>");
	    
	   	$myPapers_html = getMyPapers("Publication");
	   	$wgOut->addHTML($myPapers_html);
		
		$wgOut->addHTML("<h3 id='addpublication'>Add/Edit Publication</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");
	    $wgOut->addHTML("Enter in the title of the publication in the text field below.  If there is an already existing publication with the same or similar title, it will be listed below the text field.  If you see the publication in the list, then you can click on the title to edit its information, otherwise you can choose to create the publication with the title you have entered by clicking the 'Create' button. You can also add a new publication using the <a href='$wgServer$wgScriptPath/index.php/Special:ImportBibTex'>Import BibTeX</a> page.<br /><br />You can review the complete list of publications in the forum and search by title, author and project (if applicable) at <a href='$wgServer$wgScriptPath/index.php/Special:Products#/Publication'>GRAND Publications</a>.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a publication with a similar title to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

class AddPressPage extends SpecialPage{

	function AddPressPage() {
		wfLoadExtensionMessages('AddPressPage');
		SpecialPage::SpecialPage("AddPressPage", HQP.'During+', true, 'runAddPressPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Press"));

	    $wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='mypress'>My ".YEAR." Press</h3>");
	    
	   	$myPapers_html = getMyPapers("Press");
	   	$wgOut->addHTML($myPapers_html);

	   	$wgOut->addHTML("<h3 id='addpress'>Add/Edit Press</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");
		$wgOut->addHTML("Enter in the title of press item in the text field below.  If there is an already existing press item with the same or similar title, it will be listed below the text field.  If you see the press item in the list, then you can click on the title to edit its information, otherwise you can choose to create the press item with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a press item with a similar title to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

class AddAwardPage extends SpecialPage{

	function AddAwardPage() {
		wfLoadExtensionMessages('AddAwardPage');
		SpecialPage::SpecialPage("AddAwardPage", HQP.'During+', true, 'runAddAwardPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Award"));

	    $wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='myawards'>My ".YEAR." Awards</h3>");
	    
	   	$myPapers_html = getMyPapers("Award");
	   	$wgOut->addHTML($myPapers_html);

	   	$wgOut->addHTML("<h3 id='addaward'>Add/Edit Award</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");

		$wgOut->addHTML("Enter in the title of the award in the text field below.  If there is an already existing award with the same or similar name, it will be listed below the text field.  If you see the award in the list, then you can click on the title to edit its information, otherwise you can choose to create the award with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a award with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

class AddPresentationPage extends SpecialPage{

	function AddPresentationPage() {
		wfLoadExtensionMessages('AddPresentationPage');
		SpecialPage::SpecialPage("AddPresentationPage", HQP.'During+', true, 'runAddPresentationPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    if(isset($_GET['pubSearch'])){
	        header("Content-type: text/json");
	        echo Paper::search($_GET['phrase']);
	        exit;
	    }
	    $wgOut->addScript(generateScript("Presentation"));

	    $wgOut->addHTML("<div id='addpub_accordion'>");
	    $wgOut->addHTML("<h3 id='mypresentations'>My ".YEAR." Presentations</h3>");
	    
	   	$myPapers_html = getMyPapers("Presentation");
	   	$wgOut->addHTML($myPapers_html);

	   	$wgOut->addHTML("<h3 id='addpresentation'>Add/Edit Presentation</h3>");
		$wgOut->addHTML("<div id='add-edit_publications'>");

		$wgOut->addHTML("Enter in the title of the presentation in the text field below.  If there is an already existing presentation with the same or similar name, it will be listed below the text field.  If you see the presentation in the list, then you can click on the title to edit its information, otherwise you can choose to create the presentation with the title you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Title:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a award with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");

		$wgOut->addHTML("</div>");
		$wgOut->addHTML("</div>");
	}
}

?>

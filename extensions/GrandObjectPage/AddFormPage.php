<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['AddFormPage'] = 'AddFormPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddFormPage'] = $dir . 'AddFormPage.i18n.php';
$wgSpecialPageGroups['AddFormPage'] = 'grand-tools';

$wgHooks['UnknownAction'][] = 'formSearch';

function runAddFormPage($par){
    AddFormPage::run($par);
}

function formSearch($action, $request){
    global $wgUser;
    $me = Person::newFromId($wgUser->getId());
    if($me->getName() == "Adrian.Sheppard" || $me->getName() == "Admin"){
        if($action == "formSearch"){
            header("Content-type: text/json");
            echo Form::search($_GET['phrase']);
            exit;
        }
    }
    return true;
}

function generateFormScript($category){
    global $wgServer, $wgScriptPath;
    $script = "<script type='text/javascript'>
		                            var lastCall;
		                            
		                            function search(phrase){
		                                if(lastCall != null){
		                                    lastCall.abort();
		                                }
		                                if(phrase != ''){
		                                    phrase = phrase.replace(/'/g, ' ');
		                                    lastCall = $.get('$wgServer$wgScriptPath/index.php?action=formSearch&phrase=' + phrase + '&category={$category}', function(data) {
		                                        $('#suggestions').html('');
		                                        var html = '';
		                                        if(data.length > 0){
		                                            $('#sug').css('display', 'inline');
		                                        }
		                                        else{
		                                            $('#sug').css('display', 'none');
		                                        }
		                                        html += '<ul>';
                                                $.each(data, function(index, value){
                                                    title = value[1];
                                                    id = value[0];
                                                    html += '<li><a href=\"$wgServer$wgScriptPath/index.php/{$category}:' + id + '?edit\">' + title + '</a></li>';
                                                });
                                                html += '</ul>';
                                                $('#suggestions').html(html);
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
		                                document.location = '$wgServer$wgScriptPath/index.php/Form:' + page + '?create';
		                            }
		                            
		                            $(document).ready(function(){
		                                search($('#title').attr('value'));
		                                $('#title').attr('autocomplete', 'off');
		                            });
		                       </script>";
    return $script;
}

class AddFormPage extends SpecialPage{

	function AddFormPage() {
		wfLoadExtensionMessages('AddFormPage');
		SpecialPage::SpecialPage("AddFormPage", STAFF.'+', true, 'runAddFormPage');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$me = Person::newFromId($wgUser->getId());
		if($me->getName() == "Adrian.Sheppard" || $me->getName() == "Admin"){
	        if(isset($_GET['pubSearch'])){
	            header("Content-type: text/json");
	            echo Paper::search($_GET['phrase']);
	            exit;
	        }
	        $wgOut->addScript(generateFormScript("Form"));
		    $wgOut->addHTML("Enter a short title for the Form in the text field below. If there is an already existing Form with the same or similar title, it will be listed below the text field. If you see the Form in the list, then you can click on the title to edit its information, otherwise you can choose to create the Form with the name you have entered by clicking the 'Create' button.<br /><br />
		                     <b>Name:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                     <fieldset id='sug' style='display:none;'>
		                        <legend>Suggestions</legend>
		                        It looks like there might be a Form with a similar name to the one entered.<br /><br />
		                        <div id='suggestions'></div>
		                     </fieldset>");
	    }
	    else{
	        $wgOut->addHTML("Only Adrian is allowed to view this page");
	    }
	}
}

?>

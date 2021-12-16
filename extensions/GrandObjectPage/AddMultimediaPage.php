<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['AddMultimediaPage'] = 'AddMultimediaPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddMultimediaPage'] = $dir . 'AddMultimediaPage.i18n.php';
$wgSpecialPageGroups['AddMultimediaPage'] = 'network-tools';

UnknownAction::createAction('MultimediaSearch');
$wgHooks['ToolboxLinks'][] = 'AddMultimediaPage::createToolboxLinks';

function runAddMultimediaPage($par){
    AddMultimediaPage::execute($par);
}

function MultimediaSearch($action, $request){
    if($action == "MaterialSearch"){
        header("Content-type: text/json");
        echo Material::search($_GET['phrase']);
        exit;
    }
    return true;
}

function generateMultimediaScript($category){
    global $wgServer, $wgScriptPath;
    $script = "<script type='text/javascript'>
		                            var lastCall;
		                            
		                            function search(phrase){
		                                if(lastCall != null){
		                                    lastCall.abort();
		                                }
		                                if(phrase != ''){
		                                    phrase = phrase.replace(/'/g, ' ');
		                                    lastCall = $.get('$wgServer$wgScriptPath/index.php?action=MaterialSearch&phrase=' + phrase + '&category={$category}', function(data) {
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
		                                if($('#title').val() == ''){
                                            clearError();
                                            addError('The Multimedia must not have an empty title');
                                            return;
                                        }
		                                var page = escape($('#title').val().replace(/\‘/g, '\'').replace(/\’/g, '\''));
		                                document.location = '$wgServer$wgScriptPath/index.php/Multimedia:New?name=' + page + '&create';
		                            }
		                            
		                            $(document).ready(function(){
		                                search($('#title').val());
		                                $('#title').attr('autocomplete', 'off');
		                            });
		                       </script>";
    return $script;
}

class AddMultimediaPage extends SpecialPage{

	function AddMultimediaPage() {
		SpecialPage::__construct("AddMultimediaPage", HQP.'During+', true, 'runAddMultimediaPage');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
	    $wgOut->addScript(generateMultimediaScript("Multimedia"));
		$wgOut->addHTML("<i>Multimedia</i> ".Inflect::pluralize($config->getValue('productsTerm'))." are any media form which has been produced as a result of {$config->getValue('networkName')} participation.<br /><br />Enter a short title for the  in the text field below. If there is an already existing with the same or similar title, it will be listed below the text field. If you see the in the list, then you can click on the title to edit its information, otherwise you can choose to create the with the name you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Name:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a Multimedia with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
	
	static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(HQP)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Add/Edit Multimedia", "$wgServer$wgScriptPath/index.php/Special:AddMultimediaPage");
	    }
	    return true;
	}
}

?>

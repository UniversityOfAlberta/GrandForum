<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['AddContributionPage'] = 'AddContributionPage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddContributionPage'] = $dir . 'AddContributionPage.i18n.php';
$wgSpecialPageGroups['AddContributionPage'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'AddContributionPage::createToolboxLinks';

UnknownAction::createAction('contributionSearch');

function runAddContributionPage($par){
    AddContributionPage::execute($par);
}

function contributionSearch($action, $request){
    if($action == "contributionSearch"){
        header("Content-type: text/json");
        echo Contribution::search($_GET['phrase'], $_GET['category']);
        exit;
    }
    return true;
}

function generateContributionScript($category){
    global $wgServer, $wgScriptPath;
    $script = "<script type='text/javascript'>
		                            var lastCall;
		                            
		                            function search(phrase){
		                                if(lastCall != null){
		                                    lastCall.abort();
		                                }
		                                if(phrase != ''){
		                                    phrase = phrase.replace(/'/g, ' ');
		                                    lastCall = $.get('$wgServer$wgScriptPath/index.php?action=contributionSearch&phrase=' + phrase + '&category={$category}', function(data) {
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
                                                    html += '<li><a href=\"$wgServer$wgScriptPath/index.php/{$category}:' + value.id + '?edit\">' + value.name + '</a></li>';
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
                                            addError('The Contribution must not have an empty title');
                                            return;
                                        }
		                                var page = escape($('#title').val().replace(/\‘/g, '\'').replace(/\’/g, '\''));
		                                document.location = '$wgServer$wgScriptPath/index.php/{$category}:New?name=' + page + '&create';
		                            }
		                            
		                            $(document).ready(function(){
		                                search($('#title').val());
		                                $('#title').attr('autocomplete', 'off');
		                            });
		                       </script>";
    return $script;
}

class AddContributionPage extends SpecialPage{

	function AddContributionPage() {
		SpecialPage::__construct("AddContributionPage", HQP.'+', true, 'runAddContributionPage');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $wgOut->addScript(generateContributionScript("Contribution"));
		$wgOut->addHTML("Enter a short title for the contribution in the text field below. If there is an already existing contribution with the same or similar title, it will be listed below the text field. If you see the contribution in the list, then you can click on the title to edit its information, otherwise you can choose to create the contribution with the name you have entered by clicking the 'Create' button.<br /><br />
		                 <b>Name:</b> <input onKeyPress='submitOnEnter(event)' type='text' id='title' name='title' size='50' onKeyUp='search(this.value);' /> <input type='button' onClick='changeLocation();' name='submit' value='Create' /><br />
		                 <fieldset id='sug' style='display:none;'>
		                    <legend>Suggestions</legend>
		                    It looks like there might be a contribution with a similar name to the one entered.<br /><br />
		                    <div id='suggestions'></div>
		                 </fieldset>");
	}
	
	static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(HQP)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Add/Edit Contribution", "$wgServer$wgScriptPath/index.php/Special:AddContributionPage");
	    }
	    return true;
	}
}

?>

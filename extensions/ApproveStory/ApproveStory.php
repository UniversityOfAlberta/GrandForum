<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApproveStory'] = 'ApproveStory'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApproveStory'] = $dir . 'ApproveStory.i18n.php';

require_once("ApproveStoryAdmin.php");

function runApproveStory($par) {
  ApproveStory::execute($par);
}

class ApproveStory extends SpecialPage{

    function __construct() {
            parent::__construct("ApproveStory", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $user = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "view" && $user->isRoleAtLeast(STAFF)){
            if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
                $result = APIRequest::doAction('ApproveStory', false);
            }
            ApproveStory::generateViewHTML($wgOut);
        }
        else{
	    permissionError();
        }
    }
    
    function generateViewHTML($wgOut){
        global $wgScriptPath, $wgServer, $config, $wgEnableEmail;
        $wgOut->addHTML("<table id='requests' style='background:#ffffff;width:100%;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
                        <thead><tr bgcolor='#F2F2F2'>
                            <th>Requesting User</th>
                            <th>Timestamp</th>
                            <th>Story Title</th>
                            <th>Action</th>
                        </tr></thead><tbody>\n");
   //for loop adding here 
        $requests = Story::getAllUnapprovedStories();
        $forms = array();
        foreach($requests as $request){
            $title = str_replace("<", "&lt;", str_replace(">", "&gt;", $request->getTitle()));
            $req_user = $request->getUser();
            
            $forms[] = "<form action='$wgServer$wgScriptPath/index.php/Special:ApproveStory?action=view' method='post'>
                <input type='hidden' name='id' value='{$request->getId()}' />
                <input id='{$request->getId()}_accept' type='submit' name='submit' value='Accept' />
            </form>"; 
            
            $wgOut->addHTML("<tr>
                        <td align='left'>
                            <a target='_blank' href='{$req_user->getUrl()}'><b>{$req_user->getName()}</b></a>
                        </td>");
            $wgOut->addHTML("<td>{$request->getDateSubmitted()}</td>");
            $wgOut->addHTML("<td align='left'><a target='_blank' href='{$request->getUrl()}'>{$title}</a></td>");
            $wgOut->addHTML("<td><input type='button' value='Accept' onclick='$(\"#{$request->getId()}_accept\").click();' /></td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table><div style='display:none;'>".implode("", $forms)."</div>
        <script type='text/javascript'>
                                            $('#requests').dataTable({'autoWidth': true, 'scrollX': true}).fnSort([[2,'desc']]);
                                            $('#requests').show();
                                         </script>");
    }

}
?>

<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApprovePage'] = 'ApprovePage'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApprovePage'] = $dir . 'ApprovePage.i18n.php';

require_once("ApprovePageAdmin.php");


class ApprovePage extends SpecialPage{

    function __construct() {
            parent::__construct("ApprovePage", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $user = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "view" && $user->isRoleAtLeast(STAFF)){
            if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
                $result = APIRequest::doAction('ApprovePage', false);
            }
            ApprovePage::generateViewHTML($wgOut);
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
                            <th>Page</th>
                            <th>Action</th>
                        </tr></thead><tbody>\n");
   //for loop adding here 
        $requests = Wiki::getAllUnapprovedPages();
        $forms = array();
        foreach($requests as $request){
            if($request->getArticle() == null){
                continue;
            }
            $date = wfTimestamp(TS_DB, $request->getArticle()->getTimestamp());
            $title = str_replace("<", "&lt;", str_replace(">", "&gt;", $request->getTitle()));
            $req_user = $request->getNewestAuthor();
            $forms[] = "<form action='$wgServer$wgScriptPath/index.php/Special:ApprovePage?action=view' method='post'>
                <input type='hidden' name='id' value='{$request->getId()}' />
                <input id='{$request->getId()}_accept' type='submit' name='submit' value='Accept' />
            </form>";
            $wgOut->addHTML("<tr>
                        <td align='left'>
                            <a target='_blank' href='{$req_user->getUrl()}'><b>{$req_user->getName()}</b></a>
                        </td>");
            $wgOut->addHTML("<td>{$date}</td>");
            $wgOut->addHTML("<td align='left'><a target='_blank' href='{$request->getUrl()}'>{$title}</a></td>");
            $wgOut->addHTML("<td><input type='button' name='submit' value='Accept' onclick='$(\"#{$request->getId()}_accept\").click()' /></td>");
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

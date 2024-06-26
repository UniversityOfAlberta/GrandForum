<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Newsletter'] = 'Newsletter'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Newsletter'] = $dir . 'Newsletter.i18n.php';
$wgSpecialPageGroups['Newsletter'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'Newsletter::createToolboxLinks';

function runNewsletter($par) {
    Newsletter::execute($par);
}

class Newsletter extends SpecialPage{

    function Newsletter() {
        SpecialPage::__construct("Newsletter", null, false, 'runNewsletter');
    }
    
    function userCanExecute($user){
        return true;
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        exec('grep -r -e "No.*images?" -e "You.*are.*receiving.*this.*email.*because.*you.*are.*subscribed.*to.*the.*mailing.*list" /var/lib/mailman/archives/private/director/attachments/ | grep "attachment.html" | grep -v "5eaf95e9" | grep -v "3399ada1" | grep -v "d77a4937" | grep -v "f03e87ef"', $output);
        //exec('grep -r "HQP.*Monthly.*Newsletter" /var/lib/mailman/archives/private/hqp/attachments/ | grep "attachment.html"', $output);
        $wgOut->addHTML("<div id='accordion'>");
        sort($output);
        $output = array_reverse($output);
        $alreadyDone = array();
        foreach($output as $news){
            $exploded = explode(":", $news);
            $news = $exploded[0];
            
            $exploded = explode("/", $news);
            $date = $exploded[8];
            if($date == "20200901"){
                $date = "20200815";
            }
            $datestr = date("F j, Y", strtotime($date));
            $id = $exploded[9];
            if(!isset($alreadyDone[$id])){
                $alreadyDone[$id] = true;
                @mkdir("extensions/MailingList/Newsletter/cache/");
                if(!file_exists("extensions/MailingList/Newsletter/cache/{$id}.html")){
                    $contents = file_get_contents($news);
                    $contents = str_replace("<br>", "", $contents);
                    $contents = str_replace("&nbsp;", " ", $contents);
                    $contents = str_replace("&nbsp", " ", $contents);
                    $contents = str_replace("<tt>", "", $contents);
                    $contents = str_replace("</tt>", "", $contents);
                    $contents = html_entity_decode($contents);
                    $contents = "<style> body { margin: 0 }; </style>\n{$contents}";
                    file_put_contents("extensions/MailingList/Newsletter/cache/{$id}.html", $contents);
                }
                $wgOut->addHTML("<h3><a href='#'>{$datestr}</a></h3>
                                 <div style='padding:0;margin:0;overflow-y:hidden;'><iframe style='width:100%;height:500px;border:none;' src='{$wgServer}{$wgScriptPath}/extensions/MailingList/Newsletter/cache/{$id}.html'></iframe></div>");
            }
        }
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            $('#accordion').accordion();
        </script>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("Newsletter", "$wgServer$wgScriptPath/index.php/Special:Newsletter");
        }
        return true;
    }

}

?>

<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MailingListMapper'] = 'MailingListMapper'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MailingListMapper'] = $dir . 'MailingListMapper.i18n.php';
$wgSpecialPageGroups['MailingListMapper'] = 'other-tools';

function runMailingListMapper($par) {
  MailingListMapper::execute($par);
}

class MailingListMapper extends SpecialPage{

    function MailingListMapper() {
        SpecialPage::__construct("MailingListMapper", MANAGER, true, 'runMailingListMapper');
    }

    function execute($par){
        global $wgOut;
        $this->getOutput()->setPageTitle("Mailing List Mapper");
        $lists = MailingList::listLists();
        $wgOut->addHTML("<form>");
        $wgOut->addHTML("<h2>Select a List<h2><div>");
        $wgOut->addHTML("<select id='listSelect' name='list'>");
        foreach($lists as $list){
            $wgOut->addHTML("<option value='$list'>$list</option>");
        }
        $wgOut->addHTML("</select></div>");
        $wgOut->addHTML("<h2>List Rules</h2><div id='listRules'></div>");
        
        $wgOut->addHTML("<input type='submit' value='Save' />");
        $wgOut->addHTML("</form>");
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('#listSelect').change(function(){
                var list = $('#listSelect').val();
                
            });
        </script>");
    }
}
?>

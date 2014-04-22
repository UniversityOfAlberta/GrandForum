<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MailingListMapper'] = 'MailingListMapper'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MailingListMapper'] = $dir . 'MailingListMapper.i18n.php';
$wgSpecialPageGroups['MailingListMapper'] = 'other-tools';

//$wgHooks['TopLevelTabs'][] = 'MailingListMapper::createTab';

function runMailingListMapper($par) {
  MailingListMapper::run($par);
}

class MailingListMapper extends SpecialPage{

    function MailingListMapper() {
        wfLoadExtensionMessages('MailingListMapper');
        SpecialPage::SpecialPage("MailingListMapper", MANAGER, true, 'runMailingListMapper');
    }

    function run($par){
        
    }
}
?>

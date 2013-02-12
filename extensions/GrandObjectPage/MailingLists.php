<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['MailingLists'] = 'MailingLists'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MailingLists'] = $dir . 'MailingLists.i18n.php';
$wgSpecialPageGroups['MailingLists'] = 'grand-tools';

function runMailingLists($par){
    MailingLists::run($par);
}

class MailingLists extends SpecialPage{

	function MailingLists() {
		wfLoadExtensionMessages('MailingLists');
		SpecialPage::SpecialPage("MailingLists", HQP.'+', true, 'runMailingLists');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$location = array();
		foreach(MailingList::getLocationBasedLists() as $list){
		    $location[] = "<a href='mailto:$list@forum.grand-nce.ca'>$list@forum.grand-nce.ca</a>";
		}
		$wgOut->addHTML('The following are mailing lists for GRAND.  Each project has a project list where each project member is subscribed from.  Special mailing lists are also included on this page, like separate role based lists, support email, and university lists.<br />
		                <h2>Project Lists</h2>
		                <a href="mailto:aesthvis@forum.grand-nce.ca">aesthvis@forum.grand-nce.ca</a><br />
                        <a href="mailto:afeval@forum.grand-nce.ca">afeval@forum.grand-nce.ca</a><br />
                        <a href="mailto:ambaid@forum.grand-nce.ca">ambaid@forum.grand-nce.ca</a><br />
                        <a href="mailto:believe@forum.grand-nce.ca">believe@forum.grand-nce.ca</a><br />
                        <a href="mailto:capsim@forum.grand-nce.ca">capsim@forum.grand-nce.ca</a><br />
                        <a href="mailto:cprm@forum.grand-nce.ca">cprm@forum.grand-nce.ca</a><br />
                        <a href="mailto:digilab@forum.grand-nce.ca">digilab@forum.grand-nce.ca</a><br />
                        <a href="mailto:diglt@forum.grand-nce.ca">diglt@forum.grand-nce.ca</a><br />
                        <a href="mailto:dins@forum.grand-nce.ca">dins@forum.grand-nce.ca</a><br />
                        <a href="mailto:encad@forum.grand-nce.ca">encad@forum.grand-nce.ca</a><br />
                        <a href="mailto:eovw@forum.grand-nce.ca">eovw@forum.grand-nce.ca</a><br />
                        <a href="mailto:gamfit@forum.grand-nce.ca">gamfit@forum.grand-nce.ca</a><br />
                        <a href="mailto:grncty@forum.grand-nce.ca">grncty@forum.grand-nce.ca</a><br />
                        <a href="mailto:hctsl@forum.grand-nce.ca">hctsl@forum.grand-nce.ca</a><br />
                        <a href="mailto:hdvid@forum.grand-nce.ca">hdvid@forum.grand-nce.ca</a><br />
                        <a href="mailto:hlthsim@forum.grand-nce.ca">hlthsim@forum.grand-nce.ca</a><br />
                        <a href="mailto:hsceg@forum.grand-nce.ca">hsceg@forum.grand-nce.ca</a><br />
                        <a href="mailto:include@forum.grand-nce.ca">include@forum.grand-nce.ca</a><br />
                        <a href="mailto:mcsig@forum.grand-nce.ca">mcsig@forum.grand-nce.ca</a><br />
                        <a href="mailto:meow@forum.grand-nce.ca">meow@forum.grand-nce.ca</a><br />
                        <a href="mailto:motion@forum.grand-nce.ca">motion@forum.grand-nce.ca</a><br />
                        <a href="mailto:navel@forum.grand-nce.ca">navel@forum.grand-nce.ca</a><br />
                        <a href="mailto:neurogam@forum.grand-nce.ca">neurogam@forum.grand-nce.ca</a><br />
                        <a href="mailto:news@forum.grand-nce.ca">news@forum.grand-nce.ca</a><br />
                        <a href="mailto:ngaia@forum.grand-nce.ca">ngaia@forum.grand-nce.ca</a><br />
                        <a href="mailto:perui@forum.grand-nce.ca">perui@forum.grand-nce.ca</a><br />
                        <a href="mailto:platform@forum.grand-nce.ca">platform@forum.grand-nce.ca</a><br />
                        <a href="mailto:playpr@forum.grand-nce.ca">playpr@forum.grand-nce.ca</a><br />
                        <a href="mailto:privnm@forum.grand-nce.ca">privnm@forum.grand-nce.ca</a><br />
                        <a href="mailto:promo@forum.grand-nce.ca">promo@forum.grand-nce.ca</a><br />
                        <a href="mailto:shrdsp@forum.grand-nce.ca">shrdsp@forum.grand-nce.ca</a><br />
                        <a href="mailto:simul@forum.grand-nce.ca">simul@forum.grand-nce.ca</a><br />
                        <a href="mailto:sketch@forum.grand-nce.ca">sketch@forum.grand-nce.ca</a><br />
                        <a href="mailto:virtpres@forum.grand-nce.ca">virtpres@forum.grand-nce.ca</a><br />
                        <h2>Role Lists</h2>
                        <a href="mailto:grand-forum-hqps@forum.grand-nce.ca">grand-forum-hqps@forum.grand-nce.ca</a><br />
                        <a href="mailto:grand-forum-researchers@forum.grand-nce.ca">grand-forum-researchers@forum.grand-nce.ca</a><br />
                        <a href="mailto:grand-forum-project-leaders@forum.grand-nce.ca">grand-forum-project-leaders@forum.grand-nce.ca</a><br />
                        <a href="mailto:grand-support@forum.grand-nce.ca">grand-support@forum.grand-nce.ca</a><br />
                        <h2>Location Based Lists</h2>');
        $wgOut->addHTML(implode("<br />\n", $location));
	}
}

?>

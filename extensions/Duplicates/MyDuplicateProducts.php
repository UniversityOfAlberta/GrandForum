<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['MyDuplicateProducts'] = 'MyDuplicateProducts'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyDuplicateProducts'] = $dir . 'MyDuplicateProducts.i18n.php';
$wgSpecialPageGroups['MyDuplicateProducts'] = 'other-tools';

function runMyDuplicateProducts($par){
    MyDuplicateProducts::run($par);
}

class MyDuplicateProducts extends SpecialPage{

	function MyDuplicateProducts() {
		wfLoadExtensionMessages('MyDuplicateProducts');
		SpecialPage::SpecialPage("MyDuplicateProducts", HQP.'+', true, 'runMyDuplicateProducts');
	}

	function run($par){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser;
	    $me = Person::newFromId($wgUser->getId());
        $myPapers = $me->getPapers();
        $allPapers = Paper::getAllPapers('all', 'all', 'both');
        $duplicates = array();
        if(count($myPapers) > 0){
            foreach($myPapers as $paper1){
                foreach($allPapers as $paper2){
                    if($paper1->getId() < $paper2->getId()){
                        similar_text($paper1->getTitle(), $paper2->getTitle(), $percent);
                        $percent = round($percent);
                        if($percent >= 85){
                            $duplicates[] = array('paper1' => $paper1, 'paper2' => $paper2);
                        }
                    }
                }
            }
        }
        if(count($duplicates) > 0){
            $wgOut->addHTML("The following items were identified as being possible duplicate products.  After going to both product pages, you can decide which one should be deleted and then press the delete button on the product page.  If it is not actually a duplicate, then you can simply disregard the pair.<br /><br />
            <table rules='all' frame='box' style='padding:3px;' padding='3'>");
            foreach($duplicates as $duplicate){
                $paper1 = $duplicate['paper1'];
                $paper2 = $duplicate['paper2'];
                $wgOut->addHTML("<tr>
                                     <td style='padding:3px;'><a href='{$paper1->getURL()}' target='_blank'>{$paper1->getTitle()}</a></td>
                                     <td style='padding:3px;'><a href='{$paper2->getURL()}' target='_blank'>{$paper2->getTitle()}</a></td>
                                 </tr>");
            }
            $wgOut->addHTML("</table>");
        }
        else{
            $wgOut->addHTML("No duplicate products were detected.");
        }
	}
}

?>
